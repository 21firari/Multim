<?php
require_once '../../includes/autoload.php';
require_once '../../includes/auth_check.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Geçersiz istek.');
    }

    // Form verilerini al
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $excerpt = $_POST['excerpt'] ?? '';
    $status = $_POST['status'] ?? 'draft';
    $scheduledTime = $_POST['scheduled_time'] ?? null;
    $siteTargets = $_POST['site_targets'] ?? [];
    $siteCategories = $_POST['site_category'] ?? [];
    $siteTags = $_POST['site_tags'] ?? [];

    if (empty($title) || empty($content)) {
        throw new Exception('Başlık ve içerik alanları zorunludur.');
    }

    // Öne çıkan görsel işleme
    $featuredImage = null;
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../uploads/' . $_SESSION['user_id'] . '/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9.]/', '', $_FILES['featured_image']['name']);
        $filePath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $filePath)) {
            $featuredImage = str_replace('../../', '/', $filePath);
        }
    }

    // Hedef siteleri hazırla
    $targets = [];
    foreach ($siteTargets as $siteId) {
        $targets[] = [
            'site_id' => $siteId,
            'category_id' => $siteCategories[$siteId] ?? null,
            'tags' => !empty($siteTags[$siteId]) ? explode(',', $siteTags[$siteId]) : []
        ];
    }

    // İçeriği kaydet
    $postService = new PostService($_SESSION['user_id']);
    $result = $postService->createPost([
        'title' => $title,
        'content' => $content,
        'excerpt' => $excerpt,
        'featured_image' => $featuredImage,
        'status' => $status,
        'scheduled_time' => $scheduledTime,
        'site_targets' => $targets
    ]);

    if (!$result['success']) {
        throw new Exception($result['message']);
    }

    // Başarılı sonuç
    $_SESSION['success'] = 'İçerik başarıyla kaydedildi.';
    
    if ($status === 'publish') {
        header('Location: publish.php?id=' . $result['post_id']);
    } else {
        header('Location: index.php');
    }
    exit;

} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: create.php');
    exit;
}