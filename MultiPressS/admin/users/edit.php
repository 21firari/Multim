<?php
// admin/users/edit.php
require_once '../../includes/autoload.php';
require_once '../../includes/admin_auth_check.php';

if (!isset($_GET['id'])) {
    $_SESSION['message'] = 'Kullanıcı ID\'si belirtilmedi.';
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php');
    exit;
}

$userService = new UserService();
$packageService = new PackageService();

$user = $userService->getUserById($_GET['id']);
if (!$user) {
    $_SESSION['message'] = 'Kullanıcı bulunamadı.';
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php');
    exit;
}

$packages = $packageService->getAllPackages();

$pageTitle = "Kullanıcı Düzenle: " . htmlspecialchars($user['name']);
require_once '../../templates/admin/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once '../../templates/admin/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Kullanıcı Düzenle</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="index.php" class="btn btn-secondary me-2">
                        <i class="fas fa-arrow-left me-2"></i>Geri Dön
                    </a>
                    <a href="view.php?id=<?php echo $user['id']; ?>" class="btn btn-info">
                        <i class="fas fa-eye me-2"></i>Kullanıcıyı Görüntüle
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="update.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                        
                        <div class="row g-3">
                            <!-- Kişisel Bilgiler -->
                            <div class="col-md-6">
                                <label class="form-label">Ad Soyad</label>
                                <input type="text" name="name" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">E-posta</label>
                                <input type="email" name="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Yeni Şifre</label>
                                <input type="password" name="password" class="form-control" 
                                       placeholder="Değiştirmek için yeni şifre girin">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Yeni Şifre Tekrar</label>
                                <input type="password" name="password_confirmation" class="form-control" 
                                       placeholder="Yeni şifreyi tekrar girin">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Telefon</label>
                                <input type="tel" name="phone" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Profil Fotoğrafı</label>
                                <input type="file" name="avatar" class="form-control" accept="image/*">
                                <?php if (!empty($user['avatar'])): ?>
                                    <div class="mt-2">
                                        <img src="<?php echo htmlspecialchars($user['avatar']); ?>" 
                                             alt="Mevcut Avatar" class="img-thumbnail" style="max-height: 100px;">
                                        <div class="form-check mt-1">
                                            <input type="checkbox" name="remove_avatar" class="form-check-input" id="removeAvatar">
                                            <label class="form-check-label" for="removeAvatar">Fotoğrafı Kaldır</label>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Paket ve Durum -->
                            <div class="col-md-6">
                                <label class="form-label">Paket</label>
                                <select name="package_id" class="form-select" required>
                                    <option value="">Paket Seçin</option>
                                    <?php foreach ($packages as $package): ?>
                                        <option value="<?php echo $package['id']; ?>" 
                                                <?php echo $user['package_id'] == $package['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($package['name']); ?> 
                                            (<?php echo number_format($package['price'], 2); ?> TL)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Durum</label>
                                <select name="status" class="form-select" required>
                                    <option value="active" <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="pending" <?php echo $user['status'] === 'pending' ? 'selected' : ''; ?>>Onay Bekliyor</option>
                                    <option value="suspended" <?php echo $user['status'] === 'suspended' ? 'selected' : ''; ?>>Askıya Alınmış</option>
                                </select>
                            </div>

                            <!-- Adres Bilgileri -->
                            <div class="col-md-12">
                                <label class="form-label">Adres</label>
                                <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                            </div>

                            <!-- Notlar -->
                            <div class="col-md-12">
                                <label class="form-label">Admin Notları</label>
                                <textarea name="admin_notes" class="form-control" rows="3"><?php echo htmlspecialchars($user['admin_notes'] ?? ''); ?></textarea>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Değişiklikleri Kaydet
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
        
        if (password && password !== passwordConfirm) {
            e.preventDefault();
            alert('Şifreler eşleşmiyor!');
        }
    });
});
</script>

<?php require_once '../../templates/admin/footer.php'; ?>