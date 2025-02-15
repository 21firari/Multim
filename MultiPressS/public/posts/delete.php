<?php
// delete.php
require_once '../../includes/autoload.php';
require_once '../../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Geçersiz istek yöntemi.';
    header('Location: index.php');
    exit;
}

$postId = $_POST['post_id'] ?? 0;
if (!$postId) {
    $_SESSION['error'] = 'Geçersiz içerik ID\'si.';
    header('Location: index.php');
    exit;
}

$postService = new PostService($_SESSION['user_id']);

// Önce içeriğin kullanıcıya ait olduğunu kontrol et
$post = $postService->getPost($postId);
if (!$post['success']) {
    $_SESSION['error'] = 'İçerik bulunamadı.';
    header('Location: index.php');
    exit;
}

// İçeriği sil
$result = $postService->deletePost($postId);

if ($result['success']) {
    $_SESSION['success'] = 'İçerik başarıyla silindi.';
    header('Location: index.php');
} else {
    $_SESSION['error'] = 'İçerik silinirken hata oluştu: ' . $result['message'];
    header('Location: edit.php?id=' . $postId);
}
exit;