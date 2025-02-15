<?php
require_once '../../includes/autoload.php';
require_once '../../includes/auth_check.php';

$siteId = $_GET['id'] ?? 0;
$siteService = new WordPressSiteService($_SESSION['user_id']);
$site = $siteService->getSite($siteId);

if (!$site['success']) {
    $_SESSION['error'] = $site['message'];
    header('Location: index.php');
    exit;
}

$testResult = $siteService->testApiConnection($site['data']);

header('Content-Type: application/json');
echo json_encode($testResult);
exit;