<?php
// public/dashboard/subscription/success-page.php
require_once '../../../includes/autoload.php';
require_once '../../../includes/auth_check.php';

// Servisleri başlat
$subscriptionService = new SubscriptionService();
$userService = new UserService();

$userId = $_SESSION['user_id'];
$subscription = $subscriptionService->getCurrentSubscription($userId);
$package = $subscriptionService->getPackageById($subscription['package_id']);

$pageTitle = "Ödeme Başarılı";
require_once '../../../templates/dashboard/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once '../../../templates/dashboard/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Ödeme Başarılı</h1>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="mb-4">
                                <i class="fas fa-check-circle text-success" style="font-size: 64px;"></i>
                            </div>
                            
                            <h3 class="card-title mb-4">Tebrikler! Ödemeniz Başarıyla Tamamlandı</h3>
                            
                            <div class="alert alert-success">
                                <h5 class="mb-3">Abonelik Detayları</h5>
                                <p class="mb-2"><strong>Paket:</strong> <?php echo htmlspecialchars($package['name']); ?></p>
                                <p class="mb-2"><strong>Başlangıç Tarihi:</strong> <?php echo date('d.m.Y', strtotime($subscription['start_date'])); ?></p>
                                <p class="mb-2"><strong>Bitiş Tarihi:</strong> <?php echo date('d.m.Y', strtotime($subscription['end_date'])); ?></p>
                            </div>

                            <div class="mt-4">
                                <p class="text-muted mb-4">
                                    Aboneliğiniz başarıyla aktifleştirildi. Artık tüm özelliklere erişebilirsiniz.
                                    Abonelik detaylarınız e-posta adresinize gönderildi.
                                </p>

                                <div class="d-grid gap-2 d-md-block">
                                    <a href="../dashboard/index.php" class="btn btn-primary">
                                        <i class="fas fa-home me-2"></i>Ana Sayfaya Dön
                                    </a>
                                    <a href="../profile/index.php" class="btn btn-outline-primary">
                                        <i class="fas fa-user me-2"></i>Profili Görüntüle
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Başlayabileceğiniz İşlemler</h5>
                            <div class="list-group">
                                <a href="../sites/add.php" class="list-group-item list-group-item-action">
                                    <i class="fas fa-plus-circle me-2"></i>WordPress Sitenizi Ekleyin
                                </a>
                                <a href="../posts/create.php" class="list-group-item list-group-item-action">
                                    <i class="fas fa-edit me-2"></i>İlk İçeriğinizi Oluşturun
                                </a>
                                <a href="../help/index.php" class="list-group-item list-group-item-action">
                                    <i class="fas fa-question-circle me-2"></i>Yardım Dökümanlarını İnceleyin
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once '../../../templates/dashboard/footer.php'; ?>