<?php
require_once '../../includes/autoload.php';
require_once '../../includes/auth_check.php';

header('Content-Type: application/json');

try {
    $siteId = $_GET['site_id'] ?? 0;
    
    if (!$siteId) {
        throw new Exception('Site ID gerekli.');
    }

    $siteService = new WordPressSiteService($_SESSION['user_id']);
    $site = $siteService->getSite($siteId);

    if (!$site['success']) {
        throw new Exception('Site bulunamadı.');
    }

    $client = new WordPressApiClient(
        $site['data']['api_url'],
        $site['data']['consumer_key'],
        $site['data']['consumer_secret']
    );

    $categories = $client->getCategories();

    echo json_encode([
        'success' => true,
        'categories' => $categories['categories']
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}