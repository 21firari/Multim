<?php
require_once '../../includes/autoload.php';

// Log oluştur
$logger = new Logger('payment');
$logger->log('PayTR webhook tetiklendi: ' . json_encode($_POST));

try {
    // POST verilerini doğrula
    if (empty($_POST)) {
        throw new Exception('POST verisi bulunamadı');
    }

    // Gerekli alanları kontrol et
    $requiredFields = ['merchant_oid', 'status', 'total_amount', 'hash'];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field])) {
            throw new Exception("Gerekli alan eksik: {$field}");
        }
    }

    // Ödeme servisini başlat
    $paymentService = new PaymentService();
    
    // Callback'i işle
    $result = $paymentService->handleCallback($_POST);

    if (!$result['success']) {
        throw new Exception($result['message']);
    }

    // Başarılı yanıt
    echo "OK";
    $logger->log('Ödeme başarıyla işlendi: ' . $_POST['merchant_oid']);

} catch (Exception $e) {
    // Hata logla
    $logger->error('Webhook hatası: ' . $e->getMessage());
    
    // Hata yanıtı
    echo "FAIL";
    http_response_code(400);
}