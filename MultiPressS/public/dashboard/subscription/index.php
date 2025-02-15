<?php
// public/dashboard/subscription/index.php
require_once '../../../includes/autoload.php';
require_once '../../../includes/auth_check.php';

$subscriptionService = new SubscriptionService();
$userService = new UserService();

$userId = $_SESSION['user_id'];
$currentSubscription = $subscriptionService->getCurrentSubscription($userId);
$packages = $subscriptionService->getAllPackages();
$paymentHistory = $subscriptionService->getPaymentHistory($userId);

$pageTitle = "Abonelik Yönetimi";
require_once '../../../templates/dashboard/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once '../../../templates/dashboard/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Abonelik Yönetimi</h1>
            </div>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show">
                    <?php 
                    echo $_SESSION['message'];
                    unset($_SESSION['message']);
                    unset($_SESSION['message_type']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Mevcut Abonelik Bilgileri -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Mevcut Abonelik</h5>
                </div>
                <div class="card-body">
                    <?php if ($currentSubscription): ?>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Paket:</strong> <?php echo htmlspecialchars($currentSubscription['package_name']); ?></p>
                                <p><strong>Durum:</strong> 
                                    <span class="badge bg-<?php echo $currentSubscription['status'] == 'active' ? 'success' : 'danger'; ?>">
                                        <?php echo $currentSubscription['status'] == 'active' ? 'Aktif' : 'Pasif'; ?>
                                    </span>
                                </p>
                                <p><strong>Başlangıç:</strong> <?php echo date('d.m.Y', strtotime($currentSubscription['start_date'])); ?></p>
                                <p><strong>Bitiş:</strong> <?php echo date('d.m.Y', strtotime($currentSubscription['end_date'])); ?></p>
                            </div>
                            <div class="col-md-6 text-end">
                                <?php if ($currentSubscription['status'] == 'active'): ?>
                                    <a href="cancel.php" class="btn btn-danger" onclick="return confirm('Aboneliğinizi iptal etmek istediğinizden emin misiniz?')">Aboneliği İptal Et</a>
                                <?php endif; ?>
                                <a href="upgrade.php" class="btn btn-primary">Paketi Yükselt</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-center mb-0">Aktif bir aboneliğiniz bulunmamaktadır.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Paket Seçenekleri -->
            <div class="row mb-4">
                <?php foreach ($packages as $package): ?>
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-header text-center">
                            <h5 class="mb-0"><?php echo htmlspecialchars($package['name']); ?></h5>
                        </div>
                        <div class="card-body">
                            <h3 class="text-center mb-4"><?php echo number_format($package['price'], 2); ?> ₺<small class="text-muted">/ay</small></h3>
                            <ul class="list-unstyled">
                                <li><i class="bi bi-check-circle-fill text-success"></i> <?php echo $package['site_limit']; ?> WordPress Site</li>
                                <li><i class="bi bi-check-circle-fill text-success"></i> <?php echo $package['post_limit']; ?> İçerik/Ay</li>
                                <li><i class="bi bi-check-circle-fill text-success"></i> <?php echo $package['media_limit']; ?>MB Medya Depolama</li>
                                <?php foreach (json_decode($package['features'], true) as $feature): ?>
                                    <li><i class="bi bi-check-circle-fill text-success"></i> <?php echo htmlspecialchars($feature); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="card-footer text-center">
                            <?php if ($currentSubscription && $currentSubscription['package_id'] == $package['id']): ?>
                                <button class="btn btn-success" disabled>Mevcut Paket</button>
                            <?php else: ?>
                                <a href="upgrade.php?package_id=<?php echo $package['id']; ?>" class="btn btn-primary">Paketi Seç</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Ödeme Geçmişi -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Ödeme Geçmişi</h5>
                    <a href="history.php" class="btn btn-sm btn-outline-primary">Tümünü Gör</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Tarih</th>
                                    <th>Paket</th>
                                    <th>Tutar</th>
                                    <th>Durum</th>
                                    <th>İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($paymentHistory, 0, 5) as $payment): ?>
                                <tr>
                                    <td><?php echo date('d.m.Y H:i', strtotime($payment['created_at'])); ?></td>
                                    <td><?php echo htmlspecialchars($payment['package_name']); ?></td>
                                    <td><?php echo number_format($payment['amount'], 2); ?> ₺</td>
                                    <td>
                                        <span class="badge bg-<?php echo $payment['status'] == 'success' ? 'success' : 'danger'; ?>">
                                            <?php echo $payment['status'] == 'success' ? 'Başarılı' : 'Başarısız'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($payment['status'] == 'success'): ?>
                                            <a href="invoice.php?id=<?php echo $payment['id']; ?>" class="btn btn-sm btn-outline-secondary">Fatura</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once '../../../templates/dashboard/footer.php'; ?>