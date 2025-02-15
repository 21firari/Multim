<?php
// public/dashboard/subscription/upgrade.php
require_once '../../../includes/autoload.php';
require_once '../../../includes/auth_check.php';

$subscriptionService = new SubscriptionService();
$userId = $_SESSION['user_id'];

// Seçilen paket ID'sini al
$packageId = isset($_GET['package_id']) ? (int)$_GET['package_id'] : null;

// Paket bilgilerini al
$package = $packageId ? $subscriptionService->getPackageById($packageId) : null;
if (!$package) {
    $_SESSION['message'] = "Geçersiz paket seçimi.";
    $_SESSION['message_type'] = "danger";
    header("Location: index.php");
    exit;
}

$pageTitle = "Paket Yükseltme";
require_once '../../../templates/dashboard/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once '../../../templates/dashboard/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Paket Yükseltme</h1>
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

            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Ödeme Bilgileri</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6>Seçilen Paket</h6>
                                    <p class="mb-1"><?php echo htmlspecialchars($package['name']); ?></p>
                                    <p class="mb-0 text-muted">Aylık <?php echo number_format($package['price'], 2); ?> ₺</p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Paket Özellikleri</h6>
                                    <ul class="list-unstyled mb-0">
                                        <li><i class="bi bi-check-circle-fill text-success"></i> <?php echo $package['site_limit']; ?> WordPress Site</li>
                                        <li><i class="bi bi-check-circle-fill text-success"></i> <?php echo $package['post_limit']; ?> İçerik/Ay</li>
                                        <li><i class="bi bi-check-circle-fill text-success"></i> <?php echo $package['media_limit']; ?>MB Medya Depolama</li>
                                    </ul>
                                </div>
                            </div>

                            <form id="payment-form" action="process-payment.php" method="POST">
                                <input type="hidden" name="package_id" value="<?php echo $packageId; ?>">
                                
                                <!-- Kart Bilgileri -->
                                <div class="mb-3">
                                    <label for="card_holder" class="form-label">Kart Üzerindeki İsim</label>
                                    <input type="text" class="form-control" id="card_holder" name="card_holder" required>
                                </div>

                                <div class="mb-3">
                                    <label for="card_number" class="form-label">Kart Numarası</label>
                                    <input type="text" class="form-control" id="card_number" name="card_number" required>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="expiry" class="form-label">Son Kullanma Tarihi</label>
                                        <input type="text" class="form-control" id="expiry" name="expiry" placeholder="AA/YY" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="cvv" class="form-label">CVV</label>
                                        <input type="text" class="form-control" id="cvv" name="cvv" required>
                                    </div>
                                </div>

                                <!-- Fatura Bilgileri -->
                                <h5 class="mb-3 mt-4">Fatura Bilgileri</h5>
                                
                                <div class="mb-3">
                                    <label for="billing_name" class="form-label">Ad Soyad / Firma Adı</label>
                                    <input type="text" class="form-control" id="billing_name" name="billing_name" required>
                                </div>

                                <div class="mb-3">
                                    <label for="tax_number" class="form-label">TC/Vergi No</label>
                                    <input type="text" class="form-control" id="tax_number" name="tax_number" required>
                                </div>

                                <div class="mb-3">
                                    <label for="billing_address" class="form-label">Fatura Adresi</label>
                                    <textarea class="form-control" id="billing_address" name="billing_address" rows="3" required></textarea>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                    <label class="form-check-label" for="terms">
                                        <a href="#" target="_blank">Mesafeli satış sözleşmesini</a> okudum ve kabul ediyorum.
                                    </label>
                                </div>

                                <button type="submit" class="btn btn-primary">Ödemeyi Tamamla (<?php echo number_format($package['price'], 2); ?> ₺)</button>
                                <a href="index.php" class="btn btn-secondary">İptal</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
// Kart numarası formatlama
document.getElementById('card_number').addEventListener('input', function (e) {
    let value = e.target.value.replace(/\D/g, '');
    value = value.replace(/(.{4})/g, '$1 ').trim();
    e.target.value = value;
});

// Son kullanma tarihi formatlama
document.getElementById('expiry').addEventListener('input', function (e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length >= 2) {
        value = value.slice(0,2) + '/' + value.slice(2,4);
    }
    e.target.value = value;
});

// CVV sınırlama
document.getElementById('cvv').addEventListener('input', function (e) {
    let value = e.target.value.replace(/\D/g, '');
    e.target.value = value.slice(0,3);
});
</script>

<?php require_once '../../../templates/dashboard/footer.php'; ?>