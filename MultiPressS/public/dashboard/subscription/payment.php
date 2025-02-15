<?php
// public/dashboard/subscription/payment.php
require_once '../../../includes/autoload.php';
require_once '../../../includes/auth_check.php';

// Ödeme token kontrolü
if (!isset($_SESSION['payment_token']) || !isset($_SESSION['payment_id'])) {
    $_SESSION['message'] = "Geçersiz ödeme oturumu.";
    $_SESSION['message_type'] = "danger";
    header("Location: index.php");
    exit;
}

$paymentToken = $_SESSION['payment_token'];
$paymentId = $_SESSION['payment_id'];

$pageTitle = "Ödeme";
require_once '../../../templates/dashboard/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once '../../../templates/dashboard/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Ödeme İşlemi</h1>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-center mb-4">
                                <h5>Güvenli Ödeme Sayfası</h5>
                                <p class="text-muted">Lütfen ödeme işlemini tamamlamak için bekleyin...</p>
                            </div>

                            <!-- PayTR iframe -->
                            <iframe src="https://www.paytr.com/odeme/guvenli/<?php echo $paymentToken; ?>" 
                                    id="paytriframe" 
                                    frameborder="0" 
                                    scrolling="no" 
                                    style="width: 100%; height: 600px;">
                            </iframe>

                            <div class="text-center mt-4">
                                <a href="cancel.php?payment_id=<?php echo $paymentId; ?>" class="btn btn-secondary">İptal Et</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
iFrameResize({}, '#paytriframe');
</script>

<?php require_once '../../../templates/dashboard/footer.php'; ?>