<?php
require_once '../../includes/autoload.php';
require_once '../../includes/auth_check.php';

header('Content-Type: application/json');

try {
    if (!isset($_FILES['file'])) {
        throw new Exception('Dosya yüklenemedi.');
    }

    $file = $_FILES['file'];
    $uploadDir = '../../uploads/' . $_SESSION['user_id'] . '/';
    
    // Klasör kontrolü
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Dosya türü kontrolü
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Desteklenmeyen dosya türü.');
    }

    // Dosya boyutu kontrolü (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception('Dosya boyutu çok büyük (max 5MB).');
    }

    // Güvenli dosya adı oluştur
    $fileName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9.]/', '', $file['name']);
    $filePath = $uploadDir . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        throw new Exception('Dosya yüklenirken hata oluştu.');
    }

    // Veritabanına kaydet
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("
        INSERT INTO mph_media (
            user_id, 
            file_name, 
            file_path, 
            file_type, 
            file_size
        ) VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $_SESSION['user_id'],
        $fileName,
        str_replace('../../', '/', $filePath),
        $file['type'],
        $file['size']
    ]);

    echo json_encode([
        'success' => true,
        'url' => str_replace('../../', '/', $filePath),
        'id' => $db->lastInsertId()
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}