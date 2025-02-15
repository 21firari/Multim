<?php
// update.php
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

// Form verilerini hazırla
$postData = [
    'title' => $_POST['title'] ?? '',
    'content' => $_POST['content'] ?? '',
    'excerpt' => $_POST['excerpt'] ?? '',
    'status' => $_POST['status'] ?? 'draft',
    'scheduled_time' => null
];

// Planlanan yayın tarihi varsa ekle
if ($postData['status'] === 'scheduled' && !empty($_POST['scheduled_time'])) {
    $postData['scheduled_time'] = $_POST['scheduled_time'];
}

// Öne çıkan görsel işleme
if (!empty($_FILES['featured_image']['name'])) {
    $uploadService = new UploadService();
    $featuredImage = $uploadService->uploadImage($_FILES['featured_image']);
    
    if ($featuredImage['success']) {
        $postData['featured_image'] = $featuredImage['path'];
    } else {
        $_SESSION['error'] = 'Öne çıkan görsel yüklenirken hata oluştu: ' . $featuredImage['message'];
        header('Location: edit.php?id=' . $postId);
        exit;
    }
}

// Hedef siteleri hazırla
$targets = [];
if (!empty($_POST['site_targets'])) {
    foreach ($_POST['site_targets'] as $siteId) {
        $targets[] = [
            'site_id' => $siteId,
            'category_id' => $_POST['site_category'][$siteId] ?? null,
            'tags' => !empty($_POST['site_tags'][$siteId]) ? 
                     array_map('trim', explode(',', $_POST['site_tags'][$siteId])) : 
                     []
        ];
    }
}
$postData['targets'] = $targets;

// Medya dosyalarını hazırla
$postData['media_files'] = $_POST['media_files'] ?? [];

// İçeriği güncelle
$result = $postService->updatePost($postId, $postData);

if ($result['success']) {
    $_SESSION['success'] = 'İçerik başarıyla güncellendi.';
    
    // Eğer durum "publish" ise ve önceki durum "publish" değilse, yayınlama işlemini başlat
    if ($postData['status'] === 'publish' && $result['previous_status'] !== 'publish') {
        $publishResult = $postService->publishPost($postId);
        if (!$publishResult['success']) {
            $_SESSION['warning'] = 'İçerik güncellendi ancak yayınlama sırasında bazı hatalar oluştu: ' . 
                                 $publishResult['message'];
        }
    }
    
    header('Location: edit.php?id=' . $postId);
} else {
    $_SESSION['error'] = 'İçerik güncellenirken hata oluştu: ' . $result['message'];
    header('Location: edit.php?id=' . $postId);
}
exit;