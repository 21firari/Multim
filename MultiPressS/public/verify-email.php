<?php
require_once '../includes/autoload.php';

$token = $_GET['token'] ?? '';
$verificationService = new EmailVerificationService();
$result = $verificationService->verifyEmail($token);

$pageTitle = "E-posta Doğrulama - MultiPress Hub";
require_once '../templates/header.php';
?>

<div class="verification-container py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <div class="card shadow-sm">
                    <div class="card-body p-5">
                        <?php if ($result['success']): ?>
                            <div class="verification-success mb-4">
                                <i class="fas fa-check-circle text-success fa-5x"></i>
                            </div>
                            <h2 class="mb-4">E-posta Adresiniz Doğrulandı!</h2>
                            <p class="lead mb-4">
                                Hesabınız başarıyla aktifleştirildi. Artık MultiPress Hub'ı kullanmaya başlayabilirsiniz.
                            </p>
                            <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                                <a href="login.php" class="btn btn-primary btn-lg px-4 gap-3">
                                    Giriş Yap
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="verification-error mb-4">
                                <i class="fas fa-times-circle text-danger fa-5x"></i>
                            </div>
                            <h2 class="mb-4">Doğrulama Başarısız</h2>
                            <p class="lead mb-4">
                                <?php echo htmlspecialchars($result['message']); ?>
                            </p>
                            <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                                <a href="resend-verification.php" class="btn btn-primary btn-lg px-4 gap-3">
                                    Yeni Doğrulama E-postası Gönder
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../templates/footer.php'; ?>