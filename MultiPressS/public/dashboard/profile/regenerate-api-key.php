<?php
// public/dashboard/profile/regenerate-api-key.php
require_once '../../../includes/autoload.php';
require_once '../../../includes/auth_check.php';

$userService = new UserService();
$userId = $_SESSION['user_id'];

// Yeni API anahtarı oluştur
$newApiKey = bin2hex(random_bytes(32)); // 64 karakterlik güvenli bir anahtar

if ($userService->updateApiKey($userId, $newApiKey)) {
    $_SESSION['message'] = "API anahtarınız başarıyla yenilendi.";
    $_SESSION['message_type'] = "success";
} else {
    $_SESSION['message'] = "API anahtarı yenilenirken bir hata oluştu.";
    $_SESSION['message_type'] = "danger";
}

header("Location: index.php");
exit;