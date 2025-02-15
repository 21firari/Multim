<?php
require_once '../../includes/autoload.php';
require_once '../../includes/auth_check.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$siteService = new WordPressSiteService($_SESSION['user_id']);
$result = $siteService->getSites($page);

$pageTitle = "WordPress Siteleri - MultiPress Hub";
require_once '../../templates/header.php';
?>

<div class="dashboard-container">
    <?php require_once '../../templates/sidebar.php'; ?>

    <main class="main-content">
        <?php require_once '../../templates/topbar.php'; ?>

        <div class="container-fluid py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">WordPress Siteleri</h1>
                <a href="add.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Yeni Site Ekle
                </a>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($result['success'] && !empty($result['data']['sites'])): ?>
                <div class="row">
                    <?php foreach ($result['data']['sites'] as $site): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card site-card h-100">
                                <div class="card-body">
                                    <div class="site-icon mb-3">
                                        <img src="<?php echo get_site_icon($site['site_url']); ?>" 
                                             alt="<?php echo htmlspecialchars($site['site_name']); ?>"
                                             class="site-favicon">
                                    </div>
                                    
                                    <h5 class="card-title mb-3">
                                        <?php echo htmlspecialchars($site['site_name']); ?>
                                    </h5>
                                    
                                    <p class="site-url text-muted mb-3">
                                        <a href="<?php echo $site['site_url']; ?>" target="_blank">
                                            <?php echo htmlspecialchars($site['site_url']); ?>
                                            <i class="fas fa-external-link-alt ms-1"></i>
                                        </a>
                                    </p>
                                    
                                    <div class="site-stats d-flex justify-content-between mb-3">
                                        <div class="stat">
                                            <small class="text-muted d-block">İçerikler</small>
                                            <strong><?php echo number_format($site['post_count']); ?></strong>
                                        </div>
                                        <div class="stat">
                                            <small class="text-muted d-block">Görüntülenme</small>
                                            <strong><?php echo number_format($site['total_views']); ?></strong>
                                        </div>
                                        <div class="stat">
                                            <small class="text-muted d-block">Durum</small>
                                            <span class="badge bg-<?php echo $site['status'] === 'active' ? 'success' : 'warning'; ?>">
                                                <?php echo $site['status'] === 'active' ? 'Aktif' : 'Pasif'; ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="site-actions">
                                        <a href="edit.php?id=<?php echo $site['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary me-2">
                                            <i class="fas fa-edit"></i> Düzenle
                                        </a>
                                        <a href="test.php?id=<?php echo $site['id']; ?>" 
                                           class="btn btn-sm btn-outline-info me-2">
                                            <i class="fas fa-sync"></i> Bağlantı Testi
                                        </a>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteSiteModal"
                                                data-site-id="<?php echo $site['id']; ?>"
                                                data-site-name="<?php echo htmlspecialchars($site['site_name']); ?>">
                                            <i class="fas fa-trash"></i> Sil
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($result['data']['pagination']['last_page'] > 1): ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $result['data']['pagination']['last_page']; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>

            <?php else: ?>
                <div class="text-center py-5">
                    <img src="../../assets/images/no-sites.svg" alt="Henüz site yok" class="mb-4" style="max-width: 200px;">
                    <h4>Henüz WordPress Siteniz Yok</h4>
                    <p class="text-muted">
                        WordPress sitenizi ekleyerek içeriklerinizi yönetmeye başlayın.
                    </p>
                    <a href="add.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> İlk Sitemi Ekle
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- Delete Site Modal -->
<div class="modal fade" id="deleteSiteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Siteyi Sil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>
                    <strong id="deleteSiteName"></strong> sitesini silmek istediğinizden emin misiniz?
                    Bu işlem geri alınamaz.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <form action="delete.php" method="POST" class="d-inline">
                    <input type="hidden" name="site_id" id="deleteSiteId">
                    <button type="submit" class="btn btn-danger">Siteyi Sil</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('deleteSiteModal').addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const siteId = button.getAttribute('data-site-id');
    const siteName = button.getAttribute('data-site-name');
    
    document.getElementById('deleteSiteId').value = siteId;
    document.getElementById('deleteSiteName').textContent = siteName;
});
</script>

<?php require_once '../../templates/footer.php'; ?>