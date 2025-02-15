<?php
// admin/users/update.php
require_once '../../includes/autoload.php';
require_once '../../includes/admin_auth_check.php';

$userService = new UserService();

try {
    if (!isset($_POST['id'])) {
        throw new Exception('Kullanıcı ID\'si belirtilmedi.');
    }

    $userId = $_POST['id'];
    $userData = [
        'name' => $_POST['name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'package_id' => $_POST['package_id'] ?? '',
        'status' => $_POST['status'] ?? '',
        'address' => $_POST['address'] ?? '',
        'admin_notes' => $_POST['admin_notes'] ?? ''
    ];

    // Şifre değişikliği kontrolü
    if (!empty($_POST['password'])) {
        $userData['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }

    // Profil fotoğrafı işlemleri
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../public/uploads/avatars/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = time() . '_' . basename($_FILES['avatar']['name']);
        $uploadPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadPath)) {
            $userData['avatar'] = '/uploads/avatars/' . $fileName;
            
            // Eski avatarı sil
            $oldUser = $userService->getUserById($userId);
            if (!empty($oldUser['avatar'])) {
                $oldAvatarPath = '../../public' . $oldUser['avatar'];
                if (file_exists($oldAvatarPath)) {
                    unlink($oldAvatarPath);
                }
            }
        }
    }

    // Avatar kaldırma kontrolü
    if (isset($_POST['remove_avatar']) && $_POST['remove_avatar'] === 'on') {
        $oldUser = $userService->getUserById($userId);
        if (!empty($oldUser['avatar'])) {
            $oldAvatarPath = '../../public' . $oldUser['avatar'];
            if (file_exists($oldAvatarPath)) {
                unlink($oldAvatarPath);
            }
        }
        $userData['avatar'] = null;
    }

    // Kullanıcıyı güncelle
    $userService->updateUser($userId, $userData);

    $_SESSION['message'] = 'Kullanıcı başarıyla güncellendi.';
    $_SESSION['message_type'] = 'success';

} catch (Exception $e) {
    $_SESSION['message'] = 'Hata: ' . $e->getMessage();
    $_SESSION['message_type'] = 'danger';
}

header('Location: edit.php?id=' . $userId);
exit;