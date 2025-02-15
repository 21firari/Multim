<?php
require_once '../../includes/autoload.php';
require_once '../../includes/auth_check.php';

$paymentId = $_GET['payment_id'] ?? null;
if (!$paymentId) {
    header('Location: ../dashboard.php');
    exit;
}

// Ödeme durumunu kontrol et
$paymentService = new PaymentService();
$payment = $paymentService->getPayment($paymentId);

if (!$payment || $payment['user_id'] !== $_SESSION['user_id']) {
    header('Location: ../dashboard.php');
    exit;
}

$pageTitle = "Ödeme Başarılı - MultiPress Hub";
require_once '../../templates/header.php';
?>

<div class="payment-success-container py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <div class="card shadow-sm">
                    <div class="card-body p-5">
                        <div class="success-icon mb-4">
                            <i class="fas fa-check-circle text-success fa-5x"></i>
                        </div>
                        
                        <h2 class="mb-4">Ödemeniz Başarıyla Tamamlandı!</h2>
                        
                        <p class="lead mb-4">
                            Ödeme işleminiz başarıyla gerçekleştirildi. Paketiniz aktif edildi.
                        </p>
                        
                        <div class="payment-details mb-4">
                            <p><strong>Ödeme Numarası:</strong> #<?php echo $payment['id']; ?></p>
                            <p><strong>Tutar:</strong> ₺<?php echo number_format($payment['amount'], 2); ?></p>
                            <p><strong>Tarih:</strong> <?php echo date('d.m.Y H:i', strtotime($payment['created_at'])); ?></p>
                        </div>
                        
                        <div class="next-steps">
                            <p class="mb-4">Şimdi WordPress sitelerinizi yönetmeye başlayabilirsiniz!</p>
                            
                            <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                                <a href="../dashboard.php" class="btn btn-primary btn-lg px-4 gap-3">
                                    Dashboard'a Git
                                </a>
                                <a href="../sites/add.php" class="btn btn-outline-primary btn-lg px-4">
                                    Site Ekle
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../templates/footer.php'; ?>