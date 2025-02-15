<?php
require_once '../includes/autoload.php';
require_once '../includes/auth_check.php';

$dashboardService = new DashboardService($_SESSION['user_id']);
$stats = $dashboardService->getStats();

if (!$stats['success']) {
    $_SESSION['error'] = $stats['message'];
    $stats['data'] = [];
}

$pageTitle = "Dashboard - MultiPress Hub";
require_once '../templates/header.php';
?>

<div class="dashboard-container">
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-header">
            <img src="../assets/images/logo.png" alt="MultiPress Hub" class="logo">
        </div>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link active">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="sites/index.php" class="nav-link">
                    <i class="fas fa-globe"></i> WordPress Siteleri
                </a>
            </li>
            <li class="nav-item">
                <a href="posts/index.php" class="nav-link">
                    <i class="fas fa-file-alt"></i> İçerikler
                </a>
            </li>
            <li class="nav-item">
                <a href="profile/index.php" class="nav-link">
                    <i class="fas fa-user"></i> Profil
                </a>
            </li>
            <li class="nav-item">
                <a href="subscription/index.php" class="nav-link">
                    <i class="fas fa-credit-card"></i> Abonelik
                </a>
            </li>
        </ul>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col">
                        <h1 class="h3 mb-0">Dashboard</h1>
                    </div>
                    <div class="col-auto">
                        <div class="user-menu dropdown">
                            <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown">
                                <img src="<?php echo get_gravatar($user['email']); ?>" alt="" class="avatar">
                                <span class="d-none d-md-inline ms-2"><?php echo htmlspecialchars($user['full_name']); ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="profile/index.php">Profil</a></li>
                                <li><a class="dropdown-item" href="settings/index.php">Ayarlar</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">Çıkış Yap</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="container-fluid py-4">
            <!-- Stats Cards -->
            <div class="row">
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="stat-icon">
                                <i class="fas fa-globe"></i>
                            </div>
                            <h3 class="stat-value"><?php echo number_format($stats['data']['sites_count']); ?></h3>
                            <p class="stat-label">WordPress Siteleri</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="stat-icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <h3 class="stat-value"><?php echo number_format($stats['data']['posts_count']); ?></h3>
                            <p class="stat-label">Son 30 Gün İçerik</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="stat-icon">
                                <i class="fas fa-eye"></i>
                            </div>
                            <h3 class="stat-value"><?php echo number_format($stats['data']['views_count']); ?></h3>
                            <p class="stat-label">Toplam Görüntülenme</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="stat-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <?php if ($stats['data']['subscription']): ?>
                                <h3 class="stat-value"><?php echo ceil((strtotime($stats['data']['subscription']['end_date']) - time()) / 86400); ?></h3>
                                <p class="stat-label">Kalan Gün</p>
                            <?php else: ?>
                                <h3 class="stat-value">-</h3>
                                <p class="stat-label">Aktif Abonelik Yok</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities & Popular Posts -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Son Aktiviteler</h5>
                        </div>
                        <div class="card-body">
                            <div class="activity-list">
                                <?php foreach ($stats['data']['recent_activities'] as $activity): ?>
                                    <div class="activity-item">
                                        <div class="activity-icon">
                                            <i class="fas fa-<?php echo $activity['type'] === 'post' ? 'file-alt' : 'globe'; ?>"></i>
                                        </div>
                                        <div class="activity-details">
                                            <p class="mb-1"><?php echo htmlspecialchars($activity['description']); ?></p>
                                            <small class="text-muted">
                                                <?php echo time_elapsed_string($activity['date']); ?>
                                            </small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Popüler İçerikler</h5>
                        </div>
                        <div class="card-body">
                            <div class="popular-posts">
                                <?php foreach ($stats['data']['popular_posts'] as $post): ?>
                                    <div class="post-item">
                                        <h6 class="mb-1">
                                            <a href="<?php echo $post['post_url']; ?>" target="_blank">
                                                <?php echo htmlspecialchars($post['title']); ?>
                                            </a>
                                        </h6>
                                        <div class="post-meta">
                                            <small class="text-muted">
                                                <i class="fas fa-globe me-1"></i>
                                                <?php echo htmlspecialchars($post['site_name']); ?>
                                            </small>
                                            <small class="text-muted ms-2">
                                                <i class="fas fa-eye me-1"></i>
                                                <?php echo number_format($post['view_count']); ?> görüntülenme
                                            </small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require_once '../templates/footer.php'; ?>