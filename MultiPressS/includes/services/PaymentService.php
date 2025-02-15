<?php
class PaymentService {
    private $merchantId;
    private $merchantKey;
    private $merchantSalt;
    private $db;
    private $testMode;

    public function __construct() {
        $config = require __DIR__ . '/../../config/payment.php';
        $this->merchantId = $config['paytr']['merchant_id'];
        $this->merchantKey = $config['paytr']['merchant_key'];
        $this->merchantSalt = $config['paytr']['merchant_salt'];
        $this->testMode = $config['paytr']['test_mode'];
        $this->db = Database::getInstance()->getConnection();
    }

    public function createPayment($userId, $packageId, $amount) {
        try {
            // Ödeme kaydı oluştur
            $stmt = $this->db->prepare("
                INSERT INTO mph_payments 
                (user_id, package_id, amount, payment_status) 
                VALUES (?, ?, ?, 'pending')
            ");
            
            $stmt->execute([$userId, $packageId, $amount]);
            $paymentId = $this->db->lastInsertId();

            // PayTR için ödeme formu oluştur
            return $this->createPaymentForm($paymentId, $userId, $amount);
        } catch (Exception $e) {
            throw new Exception('Ödeme başlatılırken bir hata oluştu: ' . $e->getMessage());
        }
    }

    private function createPaymentForm($paymentId, $userId, $amount) {
        // Kullanıcı bilgilerini al
        $user = $this->getUserInfo($userId);

        // Merchant bilgileri
        $merchant = [
            'id' => $this->merchantId,
            'key' => $this->merchantKey,
            'salt' => $this->merchantSalt
        ];

        // Sipariş bilgileri
        $orderInfo = [
            'payment_id' => $paymentId,
            'total_amount' => $amount * 100, // Kuruş cinsinden
            'currency' => 'TL',
            'user_ip' => $_SERVER['REMOTE_ADDR']
        ];

        // Müşteri bilgileri
        $customer = [
            'email' => $user['email'],
            'name' => $user['full_name'],
            'phone' => $user['phone']
        ];

        // Adres bilgileri
        $address = [
            'address' => $user['address'] ?? 'Belirtilmedi',
            'city' => $user['city'] ?? 'Belirtilmedi',
            'country' => 'Turkey'
        ];

        // Ödeme formu parametreleri
        $params = [
            'merchant_id' => $merchant['id'],
            'user_ip' => $orderInfo['user_ip'],
            'merchant_oid' => $paymentId,
            'email' => $customer['email'],
            'payment_amount' => $orderInfo['total_amount'],
            'currency' => $orderInfo['currency'],
            'test_mode' => $this->testMode ? 1 : 0,
            'no_installment' => 0,
            'max_installment' => 12,
            'user_name' => $customer['name'],
            'user_phone' => $customer['phone'],
            'merchant_ok_url' => SITE_URL . '/payment/success.php',
            'merchant_fail_url' => SITE_URL . '/payment/cancel.php',
            'user_basket' => base64_encode(json_encode([
                ['MultiPress Hub Package', $amount, 1]
            ])),
            'debug_on' => 1,
            'timeout_limit' => 30,
            'lang' => 'tr'
        ];

        // Hash oluştur
        $hashStr = $params['merchant_id'] . $orderInfo['user_ip'] . $paymentId . 
                  $customer['email'] . $orderInfo['total_amount'] . 
                  base64_encode(json_encode([['MultiPress Hub Package', $amount, 1]])) . 
                  $params['no_installment'] . $params['max_installment'] . 
                  $params['currency'] . $params['test_mode'];
        
        $params['paytr_token'] = base64_encode(hash_hmac('sha256', $hashStr . $merchant['salt'], $merchant['key'], true));

        return [
            'success' => true,
            'form_data' => $params
        ];
    }

    public function handleCallback($data) {
        try {
            // Hash doğrulama
            $hash = base64_encode(hash_hmac('sha256', $data['merchant_oid'] . $this->merchantSalt . 
                                $data['status'] . $data['total_amount'], $this->merchantKey, true));

            if ($hash != $data['hash']) {
                throw new Exception('Hash doğrulama başarısız.');
            }

            // Ödeme durumunu güncelle
            $status = $data['status'] === 'success' ? 'completed' : 'failed';
            
            $stmt = $this->db->prepare("
                UPDATE mph_payments 
                SET payment_status = ?, 
                    transaction_id = ?,
                    payment_details = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $status,
                $data['merchant_oid'],
                json_encode($data),
                $data['merchant_oid']
            ]);

            // Ödeme başarılıysa aboneliği aktifleştir
            if ($status === 'completed') {
                $this->activateSubscription($data['merchant_oid']);
            }

            return ['success' => true];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function activateSubscription($paymentId) {
        $stmt = $this->db->prepare("
            SELECT user_id, package_id 
            FROM mph_payments 
            WHERE id = ?
        ");
        $stmt->execute([$paymentId]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($payment) {
            $packages = require __DIR__ . '/../../config/packages.php';
            $package = $packages[$payment['package_id']];

            $stmt = $this->db->prepare("
                INSERT INTO mph_subscriptions 
                (user_id, package_id, start_date, end_date, status) 
                VALUES (?, ?, NOW(), DATE_ADD(NOW(), INTERVAL ? DAY), 'active')
            ");

            $stmt->execute([
                $payment['user_id'],
                $payment['package_id'],
                $package['duration']
            ]);
        }
    }

    private function getUserInfo($userId) {
        $stmt = $this->db->prepare("
            SELECT email, full_name, phone, address, city 
            FROM mph_users 
            WHERE id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}