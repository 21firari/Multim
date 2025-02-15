<?php
// admin/users/view.php
require_once '../../includes/autoload.php';
require_once '../../includes/admin_auth_check.php';

if (!isset($_GET['id'])) {
    $_SESSION['message'] = 'Kullanıcı ID\'si belirtilmedi.';
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php');
    exit;
}

$userService = new UserService();
$user = $userService->getUserWithDetails($_GET['id']);

if (!$user) {
    $_SESSION['message'] = 'Kullanıcı bulunamadı.';
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php');
    exit;
}

$pageTitle = "Kullanıcı Detayları: " . htmlspecialchars($user['name']);
require_once '../../templates/admin/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once '../../templates/admin/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Kullanıcı Detayları</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="index.php" class="btn btn-secondary me-2">
                        <i class="fas fa-arrow-left me-2"></i>Geri Dön
                    </a>
                    <a href="edit.php?id=<?php echo $user['id']; ?>" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>Düzenle
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Kullanıcı Profili -->
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <?php if (!empty($user['avatar'])): ?>
                                <img src="<?php echo htmlspecialchars($user['avatar']); ?>" 
                                     alt="Profil Fotoğrafı" class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                            <?php else: ?>
                                <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center mx-auto mb-3" 
                                     style="width: 150px; height: 150px; font-size: 48px;">
                                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                            
                            <h4 class="mb-1"><?php echo htmlspecialchars($user['name']); ?></h4>
                            <p class="text-muted mb-3"><?php echo htmlspecialchars($user['email']); ?></p>
                            
                            <div class="mb-3">
                                <span class="badge bg-<?php echo $user['status'] === 'active' ? 'success' : ($user['status'] === 'pending' ? 'warning' : 'danger'); ?>">
                                    <?php echo ucfirst($user['status']); ?>
                                </span>
                            </div>
                            
                            <?php if (!empty($user['phone'])): ?>
                                <p class="mb-1">
                                    <i class="fas fa-phone me-2"></i><?php echo htmlspecialchars($user['phone']); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Kullanıcı Detayları -->
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Hesap Bilgileri</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <h6 class="text-muted">Paket</h6>
                                    <p><?php echo htmlspecialchars($user['package_name']); ?></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <h6 class="text-muted">Kayıt Tarihi</h6>
                                    <p><?php echo date('d.m.Y H:i', strtotime($user['created_at'])); ?></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <h6 class="text-muted">Son Giriş</h6>
                                    <p><?php echo $user['last_login'] ? date('d.m.Y H:i', strtotime($user['last_login'])) : '-'; ?></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <h6 class="text-muted">E-posta Doğrulaması</h6>
                                    <p><?php echo $user['email_verified'] ? 'Doğrulanmış' : 'Doğrulanmamış'; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($user['address'])): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Adres Bilgileri</h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($user['address'])); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($user['admin_notes'])): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Admin Notları</h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($user['admin_notes'])); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once '../../templates/admin/footer.php'; ?>