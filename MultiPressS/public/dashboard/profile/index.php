<?php
require_once '../../../includes/autoload.php';
require_once '../../../includes/auth_check.php';

$userService = new UserService();
$user = $userService->getUserById($_SESSION['user_id']);
$subscriptionService = new SubscriptionService();
$subscription = $subscriptionService->getCurrentSubscription($_SESSION['user_id']);

$pageTitle = "Profil Yönetimi";
require_once '../../../templates/dashboard/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php require_once '../../../templates/dashboard/sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Profil Yönetimi</h1>
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

            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <img src="<?php echo !empty($user['profile_image']) ? '../../../public/uploads/profile_images/' . $user['profile_image'] : '../../../assets/images/default-avatar.png'; ?>" 
                                 class="rounded-circle img-fluid mb-3" 
                                 style="width: 150px; height: 150px; object-fit: cover;">
                            <h5 class="card-title"><?php echo htmlspecialchars($user['name']); ?></h5>
                            <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <form action="update.php" method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Ad Soyad</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">E-posta</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="profile_image" class="form-label">Profil Fotoğrafı</label>
                                    <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">API Anahtarı</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['api_key']); ?>" readonly>
                                        <button type="button" class="btn btn-outline-secondary" onclick="location.href='regenerate-api-key.php'">Yenile</button>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">Profili Güncelle</button>
                                <a href="update-password.php" class="btn btn-outline-secondary">Şifre Değiştir</a>
                            </form>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title">Abonelik Bilgileri</h5>
                            <p class="mb-2">Paket: <?php echo htmlspecialchars($subscription['package_name']); ?></p>
                            <p class="mb-2">Durum: <?php echo $subscription['status'] == 'active' ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-danger">Pasif</span>'; ?></p>
                            <p class="mb-2">Bitiş Tarihi: <?php echo date('d.m.Y', strtotime($subscription['end_date'])); ?></p>
                            <a href="../subscription" class="btn btn-outline-primary btn-sm">Abonelik Yönetimi</a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once '../../../templates/dashboard/footer.php'; ?>