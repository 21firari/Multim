<?php
require_once '../includes/autoload.php';
require_once '../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $verificationService = new EmailVerificationService();
    $result = $verificationService->sendVerificationEmail($_SESSION['user_id']);
    
    if ($result['success']) {
        $_SESSION['success'] = 'Doğrulama e-postası gönderildi. Lütfen e-posta kutunuzu kontrol edin.';
    } else {
        $_SESSION['error'] = $result['message'];
    }
    
    header('Location: resend-verification.php');
    exit;
}

$pageTitle = "Doğrulama E-postası Gönder - MultiPress Hub";
require_once '../templates/header.php';
?>

<div class="resend-verification-container py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <div class="card shadow-sm">
                    <div class="card-body p-5">
                        <h2 class="mb-4">Doğrulama E-postası Gönder</h2>
                        
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success">
                                <?php 
                                echo $_SESSION['success'];
                                unset($_SESSION['success']);
                                ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger">
                                <?php 
                                echo $_SESSION['error'];
                                unset($_SESSION['error']);
                                ?>
                            </div>
                        <?php endif; ?>

                        <p class="lead mb-4">
                            E-posta doğrulama bağlantısını tekrar göndermek için aşağıdaki butona tıklayın.
                        </p>

                        <form method="POST" action="">
                            <button type="submit" class="btn btn-primary btn-lg">
                                Doğrulama E-postası Gönder
                            </button>
                        </form>

                        <div class="mt-4">
                            <p class="text-muted">
                                Not: Yeni bir doğrulama e-postası göndermek için 5 dakika beklemeniz gerekebilir.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../templates/footer.php'; ?>