<?php
require_once '../../includes/autoload.php';
require_once '../../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$siteId = $_POST['site_id'] ?? 0;
$siteService = new WordPressSiteService($_SESSION['user_id']);
$result = $siteService->deleteSite($siteId);

if ($result['success']) {
    $_SESSION['success'] = $result['message'];
} else {
    $_SESSION['error'] = $result['message'];
}

header('Location: index.php');
exit;