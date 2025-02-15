<?php
// publish.php
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

// İçeriği yayınla
$result = $postService->publishPost($postId);

if ($result['success']) {
    $_SESSION['success'] = 'İçerik başarıyla yayınlandı.';
    
    // Hedef sitelere göre başarı/hata durumlarını kontrol et
    if (!empty($result['site_results'])) {
        $successCount = 0;
        $errorMessages = [];
        
        foreach ($result['site_results'] as $siteResult) {
            if ($siteResult['success']) {
                $successCount++;
            } else {
                $errorMessages[] = $siteResult['site_name'] . ': ' . $siteResult['message'];
            }
        }
        
        if ($errorMessages) {
            $_SESSION['warning'] = 'Bazı sitelerde yayınlama hatası oluştu:<br>' . 
                                 implode('<br>', $errorMessages);
        }
    }
    
    header('Location: edit.php?id=' . $postId);
} else {
    $_SESSION['error'] = 'İçerik yayınlanırken hata oluştu: ' . $result['message'];
    header('Location: edit.php?id=' . $postId);
}
exit;