<?php
require_once '../../includes/autoload.php';
require_once '../../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../packages.php');
    exit;
}

try {
    // Form verilerini doğrula
    $validator = new Validator($_POST);
    $validator->required(['package_id', 'full_name', 'phone', 'city', 'address', 'terms', 'privacy'])
              ->email('email')
              ->phone('phone');

    if (!$validator->isValid()) {
        throw new Exception('Lütfen tüm alanları doğru şekilde doldurun.');
    }

    // Paket bilgilerini al
    $packages = require '../../config/packages.php';
    $package = $packages[$_POST['package_id']] ?? null;

    if (!$package) {
        throw new Exception('Geçersiz paket seçimi.');
    }

    // Kullanıcı bilgilerini güncelle
    $userService = new UserService();
    $userService->updateBillingInfo($_SESSION['user_id'], [
        'full_name' => $_POST['full_name'],
        'phone' => $_POST['phone'],
        'city' => $_POST['city'],
        'address' => $_POST['address']
    ]);

    // Ödeme işlemini başlat
    $paymentService = new PaymentService();
    $result = $paymentService->createPayment(
        $_SESSION['user_id'],
        $_POST['package_id'],
        $package['price']
    );

    if (!$result['success']) {
        throw new Exception('Ödeme başlatılırken bir hata oluştu.');
    }

    // PayTR iframe formunu oluştur
    $iframeUrl = 'https://www.paytr.com/odeme/guvenli/' . $result['token'];
    ?>
    <!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Ödeme - MultiPress Hub</title>
        <style>
            body { margin: 0; padding: 0; }
            .payment-iframe {
                width: 100%;
                height: 100vh;
                border: none;
            }
        </style>
    </head>
    <body>
        <iframe src="<?php echo $iframeUrl; ?>" class="payment-iframe" scrolling="no"></iframe>
    </body>
    </html>
    <?php
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: checkout.php?package=' . $_POST['package_id']);
    exit;
}