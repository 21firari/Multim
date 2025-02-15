<?php
// public/dashboard/subscription/payment-success.php
require_once '../../../includes/autoload.php';
require_once '../../../includes/auth_check.php';

// Servisleri başlat
$paymentService = new PaymentService();
$subscriptionService = new SubscriptionService();
$userService = new UserService();

// PayTR'den gelen parametreleri al
$merchantOid = $_POST['merchant_oid'] ?? null;
$status = $_POST['status'] ?? null;
$totalAmount = $_POST['total_amount'] ?? null;
$hash = $_POST['hash'] ?? null;

// Hash doğrulama
$hashStr = implode('', [
    $merchantOid,
    PAYTR_MERCHANT_SALT,
    $status,
    $totalAmount
]);
$expectedHash = base64_encode(hash_hmac('sha256', $hashStr, PAYTR_MERCHANT_KEY, true));

if ($hash !== $expectedHash) {
    error_log("Payment hash verification failed for merchant_oid: " . $merchantOid);
    http_response_code(400);
    exit;
}

try {
    // Ödeme kaydını bul
    $payment = $paymentService->getPaymentByMerchantOid($merchantOid);
    if (!$payment) {
        throw new Exception("Payment record not found");
    }

    if ($status === 'success') {
        // Ödeme durumunu güncelle
        $paymentService->updatePaymentStatus($payment['id'], 'completed');

        // Kullanıcının aboneliğini güncelle
        $subscriptionService->createOrUpdateSubscription([
            'user_id' => $payment['user_id'],
            'package_id' => $payment['package_id'],
            'payment_id' => $payment['id'],
            'start_date' => date('Y-m-d H:i:s'),
            'end_date' => date('Y-m-d H:i:s', strtotime('+1 month')),
            'status' => 'active'
        ]);

        // Kullanıcıya e-posta bildirim gönder
        $user = $userService->getUserById($payment['user_id']);
        $package = $subscriptionService->getPackageById($payment['package_id']);
        
        $emailService = new EmailService();
        $emailService->sendSubscriptionConfirmation($user['email'], [
            'name' => $user['name'],
            'package_name' => $package['name'],
            'amount' => $payment['amount'],
            'start_date' => date('d.m.Y'),
            'end_date' => date('d.m.Y', strtotime('+1 month'))
        ]);

        // Başarılı sayfasına yönlendir
        $_SESSION['message'] = "Ödeme işleminiz başarıyla tamamlandı.";
        $_SESSION['message_type'] = "success";
        header("Location: success-page.php");
        exit;
    } else {
        // Ödeme başarısız
        $paymentService->updatePaymentStatus($payment['id'], 'failed');
        throw new Exception("Payment failed");
    }

} catch (Exception $e) {
    error_log("Payment processing error: " . $e->getMessage());
    $_SESSION['message'] = "Ödeme işlemi sırasında bir hata oluştu.";
    $_SESSION['message_type'] = "danger";
    header("Location: index.php");
    exit;
}