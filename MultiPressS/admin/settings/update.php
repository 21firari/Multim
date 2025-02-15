<?php
// admin/settings/update.php
require_once '../../includes/autoload.php';
require_once '../../includes/admin_auth_check.php';

$settingsService = new SettingsService();

try {
    // Form verilerini al
    $settings = [
        // Site Ayarları
        'site_title' => $_POST['site_title'] ?? '',
        'site_description' => $_POST['site_description'] ?? '',
        
        // SMTP Ayarları
        'smtp_host' => $_POST['smtp_host'] ?? '',
        'smtp_port' => $_POST['smtp_port'] ?? '',
        'smtp_username' => $_POST['smtp_username'] ?? '',
        'smtp_password' => $_POST['smtp_password'] ?? '',
        
        // PayTR Ayarları
        'paytr_merchant_id' => $_POST['paytr_merchant_id'] ?? '',
        'paytr_merchant_key' => $_POST['paytr_merchant_key'] ?? '',
        'paytr_merchant_salt' => $_POST['paytr_merchant_salt'] ?? '',
        
        // Sosyal Medya Ayarları
        'social_facebook' => $_POST['social_facebook'] ?? '',
        'social_twitter' => $_POST['social_twitter'] ?? '',
        'social_instagram' => $_POST['social_instagram'] ?? '',
        'social_linkedin' => $_POST['social_linkedin'] ?? ''
    ];

    // Logo yükleme işlemi
    if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../public/uploads/';
        $fileExtension = strtolower(pathinfo($_FILES['site_logo']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new Exception('Geçersiz dosya formatı. Sadece JPG, PNG ve GIF dosyaları yüklenebilir.');
        }
        
        $logoFileName = 'site_logo_' . time() . '.' . $fileExtension;
        $logoPath = $uploadDir . $logoFileName;
        
        if (move_uploaded_file($_FILES['site_logo']['tmp_name'], $logoPath)) {
            $settings['site_logo'] = '/uploads/' . $logoFileName;
        }
    }

    // Favicon yükleme işlemi
    if (isset($_FILES['site_favicon']) && $_FILES['site_favicon']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../public/uploads/';
        $fileExtension = strtolower(pathinfo($_FILES['site_favicon']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['ico', 'png'];
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new Exception('Geçersiz favicon formatı. Sadece ICO ve PNG dosyaları yüklenebilir.');
        }
        
        $faviconFileName = 'favicon_' . time() . '.' . $fileExtension;
        $faviconPath = $uploadDir . $faviconFileName;
        
        if (move_uploaded_file($_FILES['site_favicon']['tmp_name'], $faviconPath)) {
            $settings['site_favicon'] = '/uploads/' . $faviconFileName;
        }
    }

    // Ayarları güncelle
    $settingsService->updateSettings($settings);

    // Başarılı mesajı
    $_SESSION['message'] = "Sistem ayarları başarıyla güncellendi.";
    $_SESSION['message_type'] = "success";

} catch (Exception $e) {
    // Hata mesajı
    $_SESSION['message'] = "Hata: " . $e->getMessage();
    $_SESSION['message_type'] = "danger";
}

// Ayarlar sayfasına yönlendir
header("Location: index.php");
exit;