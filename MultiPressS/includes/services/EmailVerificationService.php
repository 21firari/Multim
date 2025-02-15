<?php
class EmailVerificationService {
    private $db;
    private $mailer;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->mailer = new Mailer();
    }

    public function sendVerificationEmail($userId) {
        try {
            // Kullanıcı bilgilerini al
            $user = $this->getUserInfo($userId);
            if (!$user) {
                throw new Exception('Kullanıcı bulunamadı.');
            }

            // Yeni doğrulama tokeni oluştur
            $token = $this->generateVerificationToken();
            
            // Tokeni veritabanına kaydet
            $this->updateVerificationToken($userId, $token);

            // Doğrulama e-postası gönder
            $verificationLink = SITE_URL . "/verify-email.php?token=" . $token;
            
            $subject = "E-posta Adresinizi Doğrulayın - MultiPress Hub";
            $message = $this->getVerificationEmailTemplate($user['full_name'], $verificationLink);

            $this->mailer->send($user['email'], $subject, $message);

            return [
                'success' => true,
                'message' => 'Doğrulama e-postası gönderildi.'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function verifyEmail($token) {
        try {
            // Token'ı kontrol et
            $stmt = $this->db->prepare("
                SELECT id, email_verified 
                FROM mph_users 
                WHERE verification_token = ? 
                AND status != 'suspended'
            ");
            
            $stmt->execute([$token]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                throw new Exception('Geçersiz veya süresi dolmuş doğrulama bağlantısı.');
            }

            if ($user['email_verified']) {
                throw new Exception('E-posta adresi zaten doğrulanmış.');
            }

            // E-posta doğrulandı olarak işaretle
            $stmt = $this->db->prepare("
                UPDATE mph_users 
                SET email_verified = 1, 
                    verification_token = NULL,
                    status = CASE 
                        WHEN status = 'inactive' THEN 'active'
                        ELSE status 
                    END
                WHERE id = ?
            ");
            
            $stmt->execute([$user['id']]);

            // Hoş geldin e-postası gönder
            $this->sendWelcomeEmail($user['id']);

            return [
                'success' => true,
                'message' => 'E-posta adresiniz başarıyla doğrulandı.'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function generateVerificationToken() {
        return bin2hex(random_bytes(32));
    }

    private function updateVerificationToken($userId, $token) {
        $stmt = $this->db->prepare("
            UPDATE mph_users 
            SET verification_token = ? 
            WHERE id = ?
        ");
        
        $stmt->execute([$token, $userId]);
    }

    private function getUserInfo($userId) {
        $stmt = $this->db->prepare("
            SELECT id, email, full_name, email_verified 
            FROM mph_users 
            WHERE id = ?
        ");
        
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getVerificationEmailTemplate($name, $link) {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>E-posta Doğrulama</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
            <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                <div style="text-align: center; margin-bottom: 30px;">
                    <img src="' . SITE_URL . '/assets/images/logo.png" alt="MultiPress Hub" style="max-width: 200px;">
                </div>
                
                <h2 style="color: #2C3E50; margin-bottom: 20px;">Merhaba ' . htmlspecialchars($name) . ',</h2>
                
                <p>MultiPress Hub hesabınızı oluşturduğunuz için teşekkür ederiz. Hesabınızı aktifleştirmek için lütfen e-posta adresinizi doğrulayın.</p>
                
                <div style="text-align: center; margin: 30px 0;">
                    <a href="' . $link . '" style="background-color: #4A90E2; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">E-posta Adresimi Doğrula</a>
                </div>
                
                <p>Veya aşağıdaki bağlantıyı tarayıcınıza kopyalayabilirsiniz:</p>
                <p style="word-break: break-all; color: #4A90E2;">' . $link . '</p>
                
                <hr style="border: none; border-top: 1px solid #eee; margin: 30px 0;">
                
                <p style="color: #666; font-size: 14px;">
                    Bu e-posta MultiPress Hub hesabınız için gönderilmiştir. Eğer bu hesabı siz oluşturmadıysanız, 
                    lütfen bu e-postayı dikkate almayın.
                </p>
            </div>
        </body>
        </html>';
    }

    private function sendWelcomeEmail($userId) {
        $user = $this->getUserInfo($userId);
        
        $subject = "Hoş Geldiniz - MultiPress Hub";
        $message = $this->getWelcomeEmailTemplate($user['full_name']);
        
        $this->mailer->send($user['email'], $subject, $message);
    }

    private function getWelcomeEmailTemplate($name) {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Hoş Geldiniz</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
            <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                <div style="text-align: center; margin-bottom: 30px;">
                    <img src="' . SITE_URL . '/assets/images/logo.png" alt="MultiPress Hub" style="max-width: 200px;">
                </div>
                
                <h2 style="color: #2C3E50; margin-bottom: 20px;">Hoş Geldiniz ' . htmlspecialchars($name) . '!</h2>
                
                <p>MultiPress Hub ailesine katıldığınız için teşekkür ederiz. Artık WordPress sitelerinizi tek bir panelden 
                yönetmeye başlayabilirsiniz.</p>
                
                <h3 style="color: #2C3E50; margin: 30px 0 20px;">Başlangıç Adımları</h3>
                
                <ol style="padding-left: 20px;">
                    <li style="margin-bottom: 10px;">Dashboard\'a giriş yapın</li>
                    <li style="margin-bottom: 10px;">İlk WordPress sitenizi ekleyin</li>
                    <li style="margin-bottom: 10px;">İçerik paylaşmaya başlayın</li>
                </ol>
                
                <div style="text-align: center; margin: 30px 0;">
                    <a href="' . SITE_URL . '/dashboard.php" style="background-color: #4A90E2; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Dashboard\'a Git</a>
                </div>
                
                <hr style="border: none; border-top: 1px solid #eee; margin: 30px 0;">
                
                <p style="color: #666; font-size: 14px;">
                    Herhangi bir sorunuz olursa, destek ekibimiz size yardımcı olmaktan mutluluk duyacaktır.<br>
                    <a href="mailto:support@multipress-hub.com" style="color: #4A90E2;">support@multipress-hub.com</a>
                </p>
            </div>
        </body>
        </html>';
    }
}