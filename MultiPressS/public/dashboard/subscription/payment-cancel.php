<?php
// public/dashboard/subscription/payment-cancel.php
require_once '../../../includes/autoload.php';
require_once '../../../includes/auth_check.php';

// Servisleri başlat
$paymentService = new PaymentService();

// PayTR'den gelen parametreleri al
$merchantOid = $_POST['merchant_oid'] ?? null;

try {
    if ($merchantOid) {
        // Ödeme kaydını bul ve güncelle
        $payment = $paymentService->getPaymentByMerchantOid($merchantOid);
        if ($payment) {
            $paymentService->updatePaymentStatus($payment['id'], 'cancelled');
        }
    }

    $_SESSION['message'] = "Ödeme işlemi iptal edildi.";
    $_SESSION['message_type'] = "warning";
    header("Location: index.php");
    exit;

} catch (Exception $e) {
    error_log("Payment cancellation error: " . $e->getMessage());
    $_SESSION['message'] = "Ödeme iptali sırasında bir hata oluştu.";
    $_SESSION['message_type'] = "danger";
    header("Location: index.php");
    exit;
}