<?php
require_once __DIR__ . '/../includes/autoload.php';
$pageTitle = "Kayıt Ol - MultiPress Hub";
require_once __DIR__ . '/../templates/header.php';

// Seçilen paketi al
$selectedPackage = isset($_GET['package']) ? $_GET['package'] : 'free';
$packages = require __DIR__ . '/../config/packages.php';
$package = isset($packages[$selectedPackage]) ? $packages[$selectedPackage] : $packages['free'];
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body">
                    <h2 class="text-center mb-4">MultiPress Hub'a Kayıt Ol</h2>
                    <div class="selected-package mb-4">
                        <h4>Seçilen Paket: <?php echo $package['name']; ?></h4>
                        <p class="price">₺<?php echo number_format($package['price'], 2); ?> /aylık</p>
                    </div>
                    <form id="registerForm" action="/public/process-register.php" method="POST">
                        <input type="hidden" name="package" value="<?php echo $selectedPackage; ?>">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="firstName" class="form-label">Ad</label>
                                <input type="text" class="form-control" id="firstName" name="firstName" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="lastName" class="form-label">Soyad</label>
                                <input type="text" class="form-control" id="lastName" name="lastName" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">E-posta Adresi</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Şifre</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <div class="mb-3">
                            <label for="passwordConfirm" class="form-label">Şifre Tekrar</label>
                            <input type="password" class="form-control" id="passwordConfirm" name="passwordConfirm" required>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                            <label class="form-check-label" for="terms">
                                <a href="/terms" target="_blank">Kullanım Şartları</a>'nı okudum ve kabul ediyorum
                            </label>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Kayıt Ol</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('registerForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const passwordConfirm = document.getElementById('passwordConfirm').value;

    if (password !== passwordConfirm) {
        e.preventDefault();
        alert('Şifreler eşleşmiyor!');
    }
});
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>