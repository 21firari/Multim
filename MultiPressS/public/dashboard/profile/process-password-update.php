<?php
// public/dashboard/profile/process-password-update.php
require_once '../../../includes/autoload.php';
require_once '../../../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userService = new UserService();
    $userId = $_SESSION['user_id'];
    
    // Form verilerini al
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Validasyon
    $errors = [];
    
    // Mevcut şifre kontrolü
    if (!$userService->verifyPassword($userId, $currentPassword)) {
        $errors[] = "Mevcut şifreniz yanlış.";
    }
    
    // Yeni şifre kontrolü
    if (strlen($newPassword) < 8) {
        $errors[] = "Yeni şifre en az 8 karakter uzunluğunda olmalıdır.";
    }
    
    if (!preg_match("/[A-Z]/", $newPassword)) {
        $errors[] = "Yeni şifre en az bir büyük harf içermelidir.";
    }
    
    if (!preg_match("/[a-z]/", $newPassword)) {
        $errors[] = "Yeni şifre en az bir küçük harf içermelidir.";
    }
    
    if (!preg_match("/[0-9]/", $newPassword)) {
        $errors[] = "Yeni şifre en az bir rakam içermelidir.";
    }
    
    // Şifre eşleşme kontrolü
    if ($newPassword !== $confirmPassword) {
        $errors[] = "Yeni şifreler eşleşmiyor.";
    }
    
    if (empty($errors)) {
        if ($userService->updatePassword($userId, $newPassword)) {
            $_SESSION['message'] = "Şifreniz başarıyla güncellendi.";
            $_SESSION['message_type'] = "success";
            
            // Güvenlik için kullanıcıyı çıkış yaptır
            session_destroy();
            header("Location: ../../../public/login.php");
            exit;
        } else {
            $_SESSION['message'] = "Şifre güncellenirken bir hata oluştu.";
            $_SESSION['message_type'] = "danger";
        }
    } else {
        $_SESSION['message'] = implode("<br>", $errors);
        $_SESSION['message_type'] = "danger";
    }
    
    header("Location: update-password.php");
    exit;
}