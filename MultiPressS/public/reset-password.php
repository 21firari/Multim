<?php
require_once '../includes/autoload.php';

$token = $_GET['token'] ?? '';
$resetService = new PasswordResetService();

// Token geçerliliğini kontrol et
if (!$resetService->validateResetToken($token)) {
    $_SESSION['error'] = 'Geçersiz veya süresi dolmuş şifre sıfırlama bağlantısı.';
    header('Location: forgot-password.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    if ($password !== $passwordConfirm) {
        $_SESSION['error'] = 'Şifreler eşleşmiyor.';
    } elseif (strlen($password) < 8) {
        $_SESSION['error'] = 'Şifre en az 8 karakter olmalıdır.';
    } else {
        $result = $resetService->resetPassword($token, $password);
        
        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
            header('Location: login.php');
            exit;
        } else {
            $_SESSION['error'] = $result['message'];
        }
    }
}

$pageTitle = "Şifre Sıfırlama - MultiPress Hub";
require_once '../templates/header.php';
?>

<div class="reset-password-container py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body p-5">
                        <h2 class="text-center mb-4">Yeni Şifre Belirleme</h2>

                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger">
                                <?php 
                                echo $_SESSION['error'];
                                unset($_SESSION['error']);
                                ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="password" class="form-label">Yeni Şifre</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <div class="form-text">En az 8 karakter olmalıdır.</div>
                            </div>

                            <div class="mb-4">
                                <label for="password_confirm" class="form-label">Yeni Şifre (Tekrar)</label>
                                <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    Şifremi Güncelle
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../templates/footer.php'; ?>