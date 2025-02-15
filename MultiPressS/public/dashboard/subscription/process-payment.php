<?php
// public/dashboard/subscription/process-payment.php
require_once '../../../includes/autoload.php';
require_once '../../../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

// Gerekli servisleri başlat
$subscriptionService = new SubscriptionService();
$paymentService = new PaymentService();
$userService = new UserService();

$userId = $_SESSION['user_id'];
$packageId = (int)$_POST['package_id'];

// Form verilerini al
$paymentData = [
    'card_holder' => trim($_POST['card_holder']),
    'card_number' => preg_replace('/\s+/', '', $_POST['card_number']),
    'expiry' => trim($_POST['expiry']),
    'cvv' => trim($_POST['cvv']),
    'billing_name' => trim($_POST['billing_name']),
    'tax_number' => trim($_POST['tax_number']),
    'billing_address' => trim($_POST['billing_address'])
];

// Paket bilgilerini al
$package = $subscriptionService->getPackageById($packageId);
if (!$package) {
    $_SESSION['message'] = "Geçersiz paket seçimi.";
    $_SESSION['message_type'] = "danger";
    header("Location: upgrade.php");
    exit;
}

try {
    // PayTR için ödeme verilerini hazırla
    $paymentRequest = [
        'merchant_id' => PAYTR_MERCHANT_ID,
        'user_ip' => $_SERVER['REMOTE_ADDR'],
        'merchant_oid' => time() . rand(1000, 9999), // Benzersiz sipariş numarası
        'email' => $userService->getUserById($userId)['email'],
        'payment_amount' => $package['price'] * 100, // Kuruş cinsinden
        'currency' => 'TL',
        'test_mode' => PAYTR_TEST_MODE,
        'non_3d' => 0, // 3D Secure kullan
        'merchant_ok_url' => SITE_URL . '/public/dashboard/subscription/payment-success.php',
        'merchant_fail_url' => SITE_URL . '/public/dashboard/subscription/payment-cancel.php',
        'user_name' => $paymentData['billing_name'],
        'user_address' => $paymentData['billing_address'],
        'user_phone' => '', // Telefon numarası eklenebilir
        'user_basket' => json_encode([
            [$package['name'], $package['price'], 1]
        ]),
        'debug_on' => 1,
        'timeout_limit' => 30,
        'no_installment' => 0,
        'max_installment' => 0
    ];

    // PayTR token oluştur
    $hashStr = implode('', $paymentRequest) . PAYTR_MERCHANT_SALT;
    $paymentRequest['paytr_token'] = base64_encode(hash_hmac('sha256', $hashStr, PAYTR_MERCHANT_KEY, true));

    // Ödeme kaydını oluştur
    $paymentId = $paymentService->createPayment([
        'user_id' => $userId,
        'package_id' => $packageId,
        'amount' => $package['price'],
        'merchant_oid' => $paymentRequest['merchant_oid'],
        'status' => 'pending',
        'billing_name' => $paymentData['billing_name'],
        'tax_number' => $paymentData['tax_number'],
        'billing_address' => $paymentData['billing_address']
    ]);

    // PayTR iframe URL'sini al
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://www.paytr.com/odeme/api/get-token");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $paymentRequest);
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);

    $result = @curl_exec($ch);

    if (curl_errno($ch)) {
        throw new Exception("PAYTR IFRAME connection error. err:" . curl_error($ch));
    }

    curl_close($ch);

    $result = json_decode($result, true);

    if ($result['status'] == 'success') {
        // Ödeme sayfasına yönlendir
        $_SESSION['payment_token'] = $result['token'];
        $_SESSION['payment_id'] = $paymentId;
        header("Location: payment.php");
        exit;
    } else {
        throw new Exception($result['reason']);
    }

} catch (Exception $e) {
    error_log("Payment Error: " . $e->getMessage());
    $_SESSION['message'] = "Ödeme işlemi başlatılırken bir hata oluştu: " . $e->getMessage();
    $_SESSION['message_type'] = "danger";
    header("Location: upgrade.php?package_id=" . $packageId);
    exit;
}