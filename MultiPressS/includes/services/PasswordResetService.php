<?php
class PasswordResetService {
    private $db;
    private $mailer;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->mailer = new Mailer();
    }

    public function initiateReset($email) {
        try {
            // Kullanıcıyı kontrol et
            $user = $this->getUserByEmail($email);
            if (!$user) {
                throw new Exception('Bu e-posta adresiyle kayıtlı kullanıcı bulunamadı.');
            }

            // Son 30 dakika içinde gönderilen reset isteğini kontrol et
            if ($this->hasRecentResetRequest($user['id'])) {
                throw new Exception('Lütfen yeni bir şifre sıfırlama isteği için 30 dakika bekleyin.');
            }

            // Reset token oluştur
            $token = $this->generateResetToken();
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Token'ı kaydet
            $this->saveResetToken($user['id'], $token, $expiry);

            // Reset e-postası gönder
            $resetLink = SITE_URL . "/reset-password.php?token=" . $token;
            $this->sendResetEmail($user, $resetLink);

            return [
                'success' => true,
                'message' => 'Şifre sıfırlama bağlantısı e-posta adresinize gönderildi.'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function validateResetToken($token) {
        $stmt = $this->db->prepare("
            SELECT user_id, expiry 
            FROM mph_password_resets 
            WHERE token = ? AND used = 0
        ");
        
        $stmt->execute([$token]);
        $reset = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$reset) {
            return false;
        }

        if (strtotime($reset['expiry']) < time()) {
            return false;
        }

        return $reset['user_id'];
    }

    public function resetPassword($token, $newPassword) {
        try {
            $userId = $this->validateResetToken($token);
            if (!$userId) {
                throw new Exception('Geçersiz veya süresi dolmuş şifre sıfırlama bağlantısı.');
            }

            // Şifreyi güncelle
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $stmt = $this->db->prepare("
                UPDATE mph_users 
                SET password = ? 
                WHERE id = ?
            ");
            
            $stmt->execute([$hashedPassword, $userId]);

            // Token'ı kullanıldı olarak işaretle
            $this->markTokenAsUsed($token);

            return [
                'success' => true,
                'message' => 'Şifreniz başarıyla güncellendi.'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function getUserByEmail($email) {
        $stmt = $this->db->prepare("
            SELECT id, email, full_name 
            FROM mph_users 
            WHERE email = ? AND status != 'suspended'
        ");
        
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function hasRecentResetRequest($userId) {
        $stmt = $this->db->prepare("
            SELECT id 
            FROM mph_password_resets 
            WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 30 MINUTE)
        ");
        
        $stmt->execute([$userId]);
        return $stmt->rowCount() > 0;
    }

    private function generateResetToken() {
        return bin2hex(random_bytes(32));
    }

    private function saveResetToken($userId, $token, $expiry) {
        $stmt = $this->db->prepare("
            INSERT INTO mph_password_resets 
            (user_id, token, expiry) 
            VALUES (?, ?, ?)
        ");
        
        $stmt->execute([$userId, $token, $expiry]);
    }

    private function markTokenAsUsed($token) {
        $stmt = $this->db->prepare("
            UPDATE mph_password_resets 
            SET used = 1 
            WHERE token = ?
        ");
        
        $stmt->execute([$token]);
    }

    private function sendResetEmail($user, $resetLink) {
        $subject = "Şifre Sıfırlama - MultiPress Hub";
        $message = $this->getResetEmailTemplate($user['full_name'], $resetLink);
        
        $this->mailer->send($user['email'], $subject, $message);
    }

    private function getResetEmailTemplate($name, $resetLink) {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Şifre Sıfırlama</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
            <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                <div style="text-align: center; margin-bottom: 30px;">
                    <img src="' . SITE_URL . '/assets/images/logo.png" alt="MultiPress Hub" style="max-width: 200px;">
                </div>
                
                <h2 style="color: #2C3E50; margin-bottom: 20px;">Merhaba ' . htmlspecialchars($name) . ',</h2>
                
                <p>MultiPress Hub hesabınız için şifre sıfırlama talebinde bulundunuz. Şifrenizi sıfırlamak için 
                aşağıdaki bağlantıya tıklayın:</p>
                
                <div style="text-align: center; margin: 30px 0;">
                    <a href="' . $resetLink . '" style="background-color: #4A90E2; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Şifremi Sıfırla</a>
                </div>
                
                <p>Veya aşağıdaki bağlantıyı tarayıcınıza kopyalayabilirsiniz:</p>
                <p style="word-break: break-all; color: #4A90E2;">' . $resetLink . '</p>
                
                <p>Bu bağlantı 1 saat süreyle geçerlidir.</p>
                
                <hr style="border: none; border-top: 1px solid #eee; margin: 30px 0;">
                
                <p style="color: #666; font-size: 14px;">
                    Eğer bu şifre sıfırlama talebini siz yapmadıysanız, lütfen bu e-postayı dikkate almayın ve 
                    hesabınızın güvenliği için şifrenizi değiştirin.
                </p>
            </div>
        </body>
        </html>';
    }
}