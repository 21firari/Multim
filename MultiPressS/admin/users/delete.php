<?php
// admin/users/delete.php
require_once '../../includes/autoload.php';
require_once '../../includes/admin_auth_check.php';

if (!isset($_GET['id'])) {
    $_SESSION['message'] = 'Kullanıcı ID\'si belirtilmedi.';
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php');
    exit;
}

$userService = new UserService();

try {
    $userId = $_GET['id'];
    
    // Kullanıcının avatar'ını sil
    $user = $userService->getUserById($userId);
    if ($user && !empty($user['avatar'])) {
        $avatarPath = '../../public' . $user['avatar'];
        if (file_exists($avatarPath)) {
            unlink($avatarPath);
        }
    }
    
    // Kullanıcıyı sil
    $userService->deleteUser($userId);

    $_SESSION['message'] = 'Kullanıcı başarıyla silindi.';
    $_SESSION['message_type'] = 'success';

} catch (Exception $e) {
    $_SESSION['message'] = 'Hata: ' . $e->getMessage();
    $_SESSION['message_type'] = 'danger';
}

header('Location: index.php');
exit;