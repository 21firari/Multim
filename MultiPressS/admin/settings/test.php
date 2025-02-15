<?php
// admin/settings/test.php
require_once '../../includes/autoload.php';
require_once '../../includes/admin_auth_check.php';

$settingsService = new SettingsService();
$response = ['success' => false, 'message' => ''];

try {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'test_smtp':
            $testEmail = $_POST['test_email'] ?? '';
            if (empty($testEmail)) {
                throw new Exception('Test e-postası adresi gereklidir.');
            }
            
            if ($settingsService->testEmailSettings($testEmail)) {
                $response = [
                    'success' => true,
                    'message' => 'SMTP ayarları başarıyla test edildi. Test e-postası gönderildi.'
                ];
            }
            break;

        case 'test_paytr':
            $settings = $settingsService->getAllSettings();
            
            // PayTR test isteği
            $merchant_id = $settings['paytr_merchant_id'];
            $merchant_key = $settings['paytr_merchant_key'];
            $merchant_salt = $settings['paytr_merchant_salt'];
            
            // Test işlemi için token oluştur
            $hash_str = $merchant_id . $merchant_salt;
            $paytr_token = base64_encode(hash_hmac('sha256', $hash_str, $merchant_key, true));
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://www.paytr.com/odeme/api/test');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, [
                'merchant_id' => $merchant_id,
                'merchant_token' => $paytr_token
            ]);
            
            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                throw new Exception('PayTR bağlantı hatası: ' . curl_error($ch));
            }
            curl_close($ch);
            
            $result = json_decode($result, true);
            if ($result['status'] === 'success') {
                $response = [
                    'success' => true,
                    'message' => 'PayTR bağlantısı başarıyla test edildi.'
                ];
            } else {
                throw new Exception('PayTR test hatası: ' . ($result['message'] ?? 'Bilinmeyen hata'));
            }
            break;

        case 'test_cache':
            $testKey = 'test_' . time();
            $testValue = 'Test Value';
            
            $cache = new Cache();
            $cache->set($testKey, $testValue, 60);
            $cachedValue = $cache->get($testKey);
            
            if ($cachedValue === $testValue) {
                $response = [
                    'success' => true,
                    'message' => 'Cache sistemi başarıyla test edildi.'
                ];
            } else {
                throw new Exception('Cache sistemi test hatası: Değer kaydedilemedi veya okunamadı.');
            }
            break;

        case 'test_upload':
            if (!isset($_FILES['test_file'])) {
                throw new Exception('Test dosyası yüklenmedi.');
            }
            
            $file = $_FILES['test_file'];
            $uploadDir = '../../public/uploads/test/';
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = 'test_' . time() . '_' . basename($file['name']);
            $uploadPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                // Test dosyasını sil
                unlink($uploadPath);
                
                $response = [
                    'success' => true,
                    'message' => 'Dosya yükleme sistemi başarıyla test edildi.'
                ];
            } else {
                throw new Exception('Dosya yükleme hatası.');
            }
            break;

        default:
            throw new Exception('Geçersiz test işlemi.');
    }
} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
}

header('Content-Type: application/json');
echo json_encode($response);