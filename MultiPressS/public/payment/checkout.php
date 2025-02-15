<?php
require_once '../../includes/autoload.php';
require_once '../../includes/auth_check.php';

$packageId = $_GET['package'] ?? null;
if (!$packageId) {
    header('Location: ../packages.php');
    exit;
}

$packages = require '../../config/packages.php';
$package = $packages[$packageId] ?? null;

if (!$package) {
    header('Location: ../packages.php');
    exit;
}

$pageTitle = "Ödeme - MultiPress Hub";
require_once '../../templates/header.php';
?>

<div class="checkout-container py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title mb-4">Ödeme Bilgileri</h3>
                        
                        <!-- Fatura Bilgileri Formu -->
                        <form id="checkoutForm" method="POST" action="process.php">
                            <input type="hidden" name="package_id" value="<?php echo htmlspecialchars($packageId); ?>">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ad Soyad</label>
                                    <input type="text" class="form-control" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">E-posta</label>
                                    <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Telefon</label>
                                    <input type="tel" class="form-control" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Şehir</label>
                                    <input type="text" class="form-control" name="city" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Adres</label>
                                <textarea class="form-control" name="address" rows="3" required></textarea>
                            </div>

                            <!-- Ödeme Yöntemi Seçimi -->
                            <div class="payment-methods mb-4">
                                <h4 class="mb-3">Ödeme Yöntemi</h4>
                                
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="payment_method" id="credit_card" value="credit_card" checked>
                                    <label class="form-check-label" for="credit_card">
                                        Kredi Kartı
                                    </label>
                                </div>
                                
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="bank_transfer" value="bank_transfer">
                                    <label class="form-check-label" for="bank_transfer">
                                        Banka Havalesi
                                    </label>
                                </div>
                            </div>

                            <!-- Sözleşmeler -->
                            <div class="agreements mb-4">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                    <label class="form-check-label" for="terms">
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Kullanım Şartları</a>'nı okudum ve kabul ediyorum
                                    </label>
                                </div>
                                
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="privacy" name="privacy" required>
                                    <label class="form-check-label" for="privacy">
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#privacyModal">Gizlilik Politikası</a>'nı okudum ve kabul ediyorum
                                    </label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100">Ödemeyi Tamamla</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sipariş Özeti -->
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title mb-4">Sipariş Özeti</h3>
                        
                        <div class="package-details mb-4">
                            <h5><?php echo htmlspecialchars($package['name']); ?></h5>
                            <p class="text-muted"><?php echo $package['duration']; ?> gün</p>
                            
                            <ul class="list-unstyled">
                                <?php foreach ($package['features'] as $feature): ?>
                                    <li><i class="fas fa-check text-success me-2"></i><?php echo htmlspecialchars($feature); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <div class="price-details">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Paket Ücreti</span>
                                <span>₺<?php echo number_format($package['price'], 2); ?></span>
                            </div>
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span>KDV (%18)</span>
                                <span>₺<?php echo number_format($package['price'] * 0.18, 2); ?></span>
                            </div>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between mb-2">
                                <strong>Toplam</strong>
                                <strong>₺<?php echo number_format($package['price'] * 1.18, 2); ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Terms Modal -->
<div class="modal fade" id="termsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Kullanım Şartları</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Kullanım şartları içeriği -->
            </div>
        </div>
    </div>
</div>

<!-- Privacy Modal -->
<div class="modal fade" id="privacyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Gizlilik Politikası</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Gizlilik politikası içeriği -->
            </div>
        </div>
    </div>
</div>

<?php require_once '../../templates/footer.php'; ?>