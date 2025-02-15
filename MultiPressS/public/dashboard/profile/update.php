<?php
// public/dashboard/profile/update.php
require_once '../../../includes/autoload.php';
require_once '../../../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userService = new UserService();
    $userId = $_SESSION['user_id'];
    
    // Form verilerini al
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    
    // Validasyon
    $errors = [];
    if (empty($name)) {
        $errors[] = "Ad Soyad alanı boş bırakılamaz.";
    }
    if (empty($email)) {
        $errors[] = "E-posta alanı boş bırakılamaz.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Geçerli bir e-posta adresi giriniz.";
    }
    
    // E-posta benzersizlik kontrolü
    if ($userService->isEmailTaken($email, $userId)) {
        $errors[] = "Bu e-posta adresi başka bir kullanıcı tarafından kullanılıyor.";
    }
    
    // Profil fotoğrafı yükleme işlemi
    $profile_image = null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($_FILES['profile_image']['type'], $allowed_types)) {
            $errors[] = "Sadece JPG, PNG ve GIF formatında dosyalar yüklenebilir.";
        }
        if ($_FILES['profile_image']['size'] > $max_size) {
            $errors[] = "Dosya boyutu 5MB'dan büyük olamaz.";
        }
        
        if (empty($errors)) {
            $upload_dir = '../../../public/uploads/profile_images/';
            $file_extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('profile_') . '.' . $file_extension;
            
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_dir . $filename)) {
                // Eski profil fotoğrafını sil
                $old_image = $userService->getUserById($userId)['profile_image'];
                if ($old_image && file_exists($upload_dir . $old_image)) {
                    unlink($upload_dir . $old_image);
                }
                $profile_image = $filename;
            } else {
                $errors[] = "Dosya yükleme işlemi başarısız oldu.";
            }
        }
    }
    
    if (empty($errors)) {
        $updateData = [
            'name' => $name,
            'email' => $email
        ];
        
        if ($profile_image) {
            $updateData['profile_image'] = $profile_image;
        }
        
        if ($userService->updateUser($userId, $updateData)) {
            $_SESSION['message'] = "Profil bilgileriniz başarıyla güncellendi.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Profil güncellenirken bir hata oluştu.";
            $_SESSION['message_type'] = "danger";
        }
    } else {
        $_SESSION['message'] = implode("<br>", $errors);
        $_SESSION['message_type'] = "danger";
    }
    
    header("Location: index.php");
    exit;
}