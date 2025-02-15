<?php
// admin/users/store.php
require_once '../../includes/autoload.php';
require_once '../../includes/admin_auth_check.php';

$userService = new UserService();

try {
    // Form verilerini al
    $userData = [
        'name' => $_POST['name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'password' => $_POST['password'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'package_id' => $_POST['package_id'] ?? '',
        'status' => $_POST['status'] ?? '',
        'address' => $_POST['address'] ?? '',
        'admin_notes' => $_POST['admin_notes'] ?? ''
    ];

    // Profil fotoğrafı yükleme
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../public/uploads/avatars/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = time() . '_' . basename($_FILES['avatar']['name']);
        $uploadPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadPath)) {
            $userData['avatar'] = '/uploads/avatars/' . $fileName;
        }
    }

    // Kullanıcıyı oluştur
    $userId = $userService->createUser($userData);

    $_SESSION['message'] = 'Kullanıcı başarıyla oluşturuldu.';
    $_SESSION['message_type'] = 'success';
    
    // Yeni kullanıcıya hoş geldin e-postası gönder
    $emailService = new EmailService();
    $emailService->sendWelcomeEmail($userData['email'], $userData['name'], $userData['password']);

} catch (Exception $e) {
    $_SESSION['message'] = 'Hata: ' . $e->getMessage();
    $_SESSION['message_type'] = 'danger';
}

header('Location: index.php');
exit;