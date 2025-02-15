<?php
spl_autoload_register(function ($class) {
    $directories = [
        __DIR__ . '/',
        __DIR__ . '/../classes/',
        __DIR__ . '/../services/',
    ];

    foreach ($directories as $directory) {
        $file = $directory . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Gerekli dosyaları yükle
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Auth.php';

// Hata raporlama
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}