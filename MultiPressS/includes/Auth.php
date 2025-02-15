<?php
class Auth {
    private $db;
    private $mailer;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->mailer = new Mailer();
    }

    public function register($data) {
        try {
            // Veri doğrulama
            $this->validateRegistrationData($data);

            // Şifre hashleme
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

            // Doğrulama tokeni oluşturma
            $verificationToken = bin2hex(random_bytes(32));

            // Kullanıcıyı veritabanına kaydetme
            $stmt = $this->db->prepare("
                INSERT INTO mph_users 
                (username, email, password, full_name, phone, verification_token) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $username = strtolower(str_replace(' ', '', $data['firstName'])) . rand(100, 999);
            $fullName = $data['firstName'] . ' ' . $data['lastName'];

            $stmt->execute([
                $username,
                $data['email'],
                $hashedPassword,
                $fullName,
                $data['phone'],
                $verificationToken
            ]);

            $userId = $this->db->lastInsertId();

            // Paketi kaydetme
            $this->createSubscription($userId, $data['package_id']);

            // Doğrulama e-postası gönderme
            $this->sendVerificationEmail($data['email'], $verificationToken);

            return [
                'success' => true,
                'message' => 'Kayıt başarılı. Lütfen e-posta adresinizi doğrulayın.'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function login($email, $password, $remember = false) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, email, password, status, email_verified 
                FROM mph_users 
                WHERE email = ?
            ");
            
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !password_verify($password, $user['password'])) {
                throw new Exception('Geçersiz e-posta veya şifre.');
            }

            if ($user['status'] !== 'active') {
                throw new Exception('Hesabınız aktif değil.');
            }

            if (!$user['email_verified']) {
                throw new Exception('Lütfen e-posta adresinizi doğrulayın.');
            }

            // Oturum başlatma
            $_SESSION['user_id'] = $user['id'];
            
            // Remember me
            if ($remember) {
                $this->setRememberMeToken($user['id']);
            }

            // Son giriş tarihini güncelle
            $this->updateLastLogin($user['id']);

            return [
                'success' => true,
                'message' => 'Giriş başarılı.'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function validateRegistrationData($data) {
        // E-posta kontrolü
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Geçersiz e-posta adresi.');
        }

        // E-posta benzersizlik kontrolü
        $stmt = $this->db->prepare("SELECT id FROM mph_users WHERE email = ?");
        $stmt->execute([$data['email']]);
        if ($stmt->rowCount() > 0) {
            throw new Exception('Bu e-posta adresi zaten kullanılıyor.');
        }

        // Şifre kontrolü
        if (strlen($data['password']) < 8) {
            throw new Exception('Şifre en az 8 karakter olmalıdır.');
        }

        if ($data['password'] !== $data['passwordConfirm']) {
            throw new Exception('Şifreler eşleşmiyor.');
        }

        // Telefon numarası kontrolü
        if (!preg_match("/^[0-9]{10}$/", preg_replace("/[^0-9]/", "", $data['phone']))) {
            throw new Exception('Geçersiz telefon numarası.');
        }
    }

    private function createSubscription($userId, $packageId) {
        $packages = require __DIR__ . '/../config/packages.php';
        $package = $packages[$packageId];

        $stmt = $this->db->prepare("
            INSERT INTO mph_subscriptions 
            (user_id, package_id, start_date, end_date) 
            VALUES (?, ?, NOW(), DATE_ADD(NOW(), INTERVAL ? DAY))
        ");

        $stmt->execute([
            $userId,
            $packageId,
            $package['duration']
        ]);
    }

    private function sendVerificationEmail($email, $token) {
        $verificationLink = SITE_URL . "/verify-email.php?token=" . $token;
        
        $subject = "E-posta Adresinizi Doğrulayın - MultiPress Hub";
        $message = "Merhaba,\n\n";
        $message .= "MultiPress Hub hesabınızı doğrulamak için aşağıdaki bağlantıya tıklayın:\n\n";
        $message .= $verificationLink . "\n\n";
        $message .= "Saygılarımızla,\nMultiPress Hub Ekibi";

        $this->mailer->send($email, $subject, $message);
    }
}