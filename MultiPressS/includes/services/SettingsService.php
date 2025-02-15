<?php
// includes/services/SettingsService.php

class SettingsService {
    private $db;
    private $cache;
    private $settings = null;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->cache = new Cache();
    }

    /**
     * Tüm ayarları getirir
     */
    public function getAllSettings() {
        if ($this->settings === null) {
            // Önce cache'den kontrol et
            $this->settings = $this->cache->get('system_settings');

            if ($this->settings === false) {
                // Cache'de yoksa veritabanından al
                $query = "SELECT * FROM settings";
                $stmt = $this->db->prepare($query);
                $stmt->execute();
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $this->settings = [];
                foreach ($results as $row) {
                    $this->settings[$row['key']] = $row['value'];
                }

                // Cache'e kaydet (1 saat geçerli)
                $this->cache->set('system_settings', $this->settings, 3600);
            }
        }

        return $this->settings;
    }

    /**
     * Belirli bir ayarı getirir
     */
    public function getSetting($key, $default = null) {
        $settings = $this->getAllSettings();
        return $settings[$key] ?? $default;
    }

    /**
     * Ayarları günceller
     */
    public function updateSettings($settings) {
        try {
            $this->db->beginTransaction();

            foreach ($settings as $key => $value) {
                $query = "INSERT INTO settings (`key`, `value`) 
                         VALUES (:key, :value) 
                         ON DUPLICATE KEY UPDATE `value` = :value";
                
                $stmt = $this->db->prepare($query);
                $stmt->execute([
                    ':key' => $key,
                    ':value' => $value
                ]);
            }

            $this->db->commit();

            // Cache'i temizle
            $this->cache->delete('system_settings');
            $this->settings = null;

            // Log tut
            $this->logSettingsUpdate($settings);

            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Settings update error: " . $e->getMessage());
            throw new Exception("Ayarlar güncellenirken bir hata oluştu.");
        }
    }

    /**
     * Ayar güncellemelerini loglar
     */
    private function logSettingsUpdate($settings) {
        $adminId = $_SESSION['admin_id'] ?? 0;
        $logData = [
            'admin_id' => $adminId,
            'action' => 'settings_update',
            'details' => json_encode($settings),
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'created_at' => date('Y-m-d H:i:s')
        ];

        $query = "INSERT INTO admin_logs 
                 (admin_id, action, details, ip_address, user_agent, created_at) 
                 VALUES 
                 (:admin_id, :action, :details, :ip_address, :user_agent, :created_at)";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($logData);
    }

    /**
     * E-posta ayarlarını test eder
     */
    public function testEmailSettings($testEmail) {
        try {
            $settings = $this->getAllSettings();
            
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $settings['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $settings['smtp_username'];
            $mail->Password = $settings['smtp_password'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $settings['smtp_port'];

            $mail->setFrom($settings['smtp_username'], $settings['site_title']);
            $mail->addAddress($testEmail);
            $mail->Subject = 'SMTP Test E-postası';
            $mail->Body = 'Bu bir test e-postasıdır. SMTP ayarlarınız başarıyla çalışıyor.';

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("SMTP test error: " . $e->getMessage());
            throw new Exception("E-posta gönderimi başarısız: " . $e->getMessage());
        }
    }

    /**
     * Sistem bakımı için gerekli ayarları yapar
     */
    public function setMaintenanceMode($enabled = true) {
        try {
            $maintenanceFile = ROOTPATH . '/maintenance.php';
            
            if ($enabled) {
                // Bakım modu sayfasını oluştur
                if (!file_exists($maintenanceFile)) {
                    $content = file_get_contents(ROOTPATH . '/templates/maintenance.php');
                    file_put_contents($maintenanceFile, $content);
                }
                
                // .htaccess'i güncelle
                $htaccess = file_get_contents(ROOTPATH . '/.htaccess');
                $htaccess .= "\nErrorDocument 503 /maintenance.php\n";
                $htaccess .= "RewriteEngine On\n";
                $htaccess .= "RewriteCond %{REQUEST_URI} !/maintenance.php$\n";
                $htaccess .= "RewriteCond %{REQUEST_URI} !/assets/.*$\n";
                $htaccess .= "RewriteRule .* /maintenance.php [R=503,L]\n";
                file_put_contents(ROOTPATH . '/.htaccess', $htaccess);
            } else {
                // Bakım modunu kaldır
                if (file_exists($maintenanceFile)) {
                    unlink($maintenanceFile);
                }
                
                // .htaccess'i temizle
                $htaccess = file_get_contents(ROOTPATH . '/.htaccess');
                $htaccess = preg_replace('/\nErrorDocument 503.*\n.*\n.*\n.*\n.*/', '', $htaccess);
                file_put_contents(ROOTPATH . '/.htaccess', $htaccess);
            }

            $this->updateSettings(['maintenance_mode' => $enabled ? '1' : '0']);
            return true;
        } catch (Exception $e) {
            error_log("Maintenance mode error: " . $e->getMessage());
            throw new Exception("Bakım modu ayarlanırken bir hata oluştu.");
        }
    }
}