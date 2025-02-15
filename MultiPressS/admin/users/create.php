<?php
// admin/users/create.php
require_once '../../includes/autoload.php';
require_once '../../includes/admin_auth_check.php';

$packageService = new PackageService();
$packages = $packageService->getAllPackages();

$pageTitle = "Yeni Kullanıcı Oluştur";
require_once '../../templates/admin/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once '../../templates/admin/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Yeni Kullanıcı Oluştur</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Geri Dön
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="store.php" method="POST" enctype="multipart/form-data">
                        <div class="row g-3">
                            <!-- Kişisel Bilgiler -->
                            <div class="col-md-6">
                                <label class="form-label">Ad Soyad</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">E-posta</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Şifre</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Şifre Tekrar</label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Telefon</label>
                                <input type="tel" name="phone" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Profil Fotoğrafı</label>
                                <input type="file" name="avatar" class="form-control" accept="image/*">
                            </div>

                            <!-- Paket ve Durum -->
                            <div class="col-md-6">
                                <label class="form-label">Paket</label>
                                <select name="package_id" class="form-select" required>
                                    <option value="">Paket Seçin</option>
                                    <?php foreach ($packages as $package): ?>
                                        <option value="<?php echo $package['id']; ?>">
                                            <?php echo htmlspecialchars($package['name']); ?> 
                                            (<?php echo number_format($package['price'], 2); ?> TL)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Durum</label>
                                <select name="status" class="form-select" required>
                                    <option value="active">Aktif</option>
                                    <option value="pending">Onay Bekliyor</option>
                                    <option value="suspended">Askıya Alınmış</option>
                                </select>
                            </div>

                            <!-- Adres Bilgileri -->
                            <div class="col-md-12">
                                <label class="form-label">Adres</label>
                                <textarea name="address" class="form-control" rows="3"></textarea>
                            </div>

                            <!-- Notlar -->
                            <div class="col-md-12">
                                <label class="form-label">Admin Notları</label>
                                <textarea name="admin_notes" class="form-control" rows="3"></textarea>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Kullanıcı Oluştur
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const password = form.querySelector('input[name="password"]').value;
        const passwordConfirm = form.querySelector('input[name="password_confirmation"]').value;
        
        if (password !== passwordConfirm) {
            e.preventDefault();
            alert('Şifreler eşleşmiyor!');
        }
    });
});
</script>

<?php require_once '../../templates/admin/footer.php'; ?>