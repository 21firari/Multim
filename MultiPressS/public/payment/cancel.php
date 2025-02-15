<?php
require_once '../../includes/autoload.php';
require_once '../../includes/auth_check.php';

$paymentId = $_GET['payment_id'] ?? null;
if (!$paymentId) {
    header('Location: ../dashboard.php');
    exit;
}

$pageTitle = "Ödeme İptal - MultiPress Hub";
require_once '../../templates/header.php';
?>

<div class="payment-cancel-container py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <div class="card shadow-sm">
                    <div class="card-body p-5">
                        <div class="cancel-icon mb-4">
                            <i class="fas fa-times-circle text-danger fa-5x"></i>
                        </div>
                        
                        <h2 class="mb-4">Ödeme İşlemi İptal Edildi</h2>
                        
                        <p class="lead mb-4">
                            Ödeme işleminiz tamamlanamadı veya iptal edildi.
                        </p>
                        
                        <div class="next-steps">
                            <p class="mb-4">Aşağıdaki seçeneklerden birini tercih edebilirsiniz:</p>
                            
                            <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                                <a href="../checkout.php?package=<?php echo $_GET['package_id'] ?? ''; ?>" class="btn btn-primary btn-lg px-4 gap-3">
                                    Tekrar Dene
                                </a>
                                <a href="../packages.php" class="btn btn-outline-secondary btn-lg px-4">
                                    Paketlere Geri Dön
                                </a>
                            </div>
                        </div>
                        
                        <div class="support-info mt-5">
                            <p class="mb-2">Sorun yaşıyorsanız bizimle iletişime geçebilirsiniz:</p>
                            <p class="mb-0">
                                <i class="fas fa-envelope me-2"></i>
                                <a href="mailto:support@multipress-hub.com">support@multipress-hub.com</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../templates/footer.php'; ?>