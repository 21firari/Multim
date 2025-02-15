<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/Auth.php';

// URL yönlendirmesi için basit bir router
$request = $_SERVER['REQUEST_URI'];
$basePath = '/'; // Eğer alt dizinde çalışıyorsa bunu ayarlayın

// URL'den base path'i çıkar
$request = str_replace($basePath, '', $request);
$request = strtok($request, '?'); // Query string'i kaldır

// Routing işlemleri
switch ($request) {
    case '':
    case '/':
        require __DIR__ . '/public/home.php';
        break;
    
    case 'login':
        require __DIR__ . '/public/login.php';
        break;
        
    case 'register':
        require __DIR__ . '/public/register.php';
        break;
        
    case 'dashboard':
        // Oturum kontrolü
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }
        require __DIR__ . '/public/dashboard.php';
        break;
        
    case 'forgot-password':
        require __DIR__ . '/public/forgot-password.php';
        break;
        
    case 'reset-password':
        require __DIR__ . '/public/reset-password.php';
        break;
        
    case 'verify-email':
        require __DIR__ . '/public/verify-email.php';
        break;
        
    case 'resend-verification':
        require __DIR__ . '/public/resend-verification.php';
        break;
        
    default:
        http_response_code(404);
        require __DIR__ . '/public/404.php';
        break;
}