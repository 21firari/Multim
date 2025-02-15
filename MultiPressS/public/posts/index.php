<?php
require_once '../../includes/autoload.php';
require_once '../../includes/auth_check.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$filters = [
    'status' => $_GET['status'] ?? '',
    'search' => $_GET['search'] ?? ''
];

$postService = new PostService($_SESSION['user_id']);
$result = $postService->getPosts($page, 10, $filters);

$pageTitle = "İçerikler - MultiPress Hub";
require_once '../../templates/header.php';
?>

<div class="dashboard-container">
    <?php require_once '../../templates/sidebar.php'; ?>

    <main class="main-content">
        <?php require_once '../../templates/topbar.php'; ?>

        <div class="container-fluid py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">İçerikler</h1>
                <a href="create.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Yeni İçerik
                </a>
            </div>

            <!-- Filtreler -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" 
                                       class="form-control" 
                                       name="search" 
                                       value="<?php echo htmlspecialchars($filters['search']); ?>"
                                       placeholder="İçerik ara...">
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">Tüm Durumlar</option>
                                <option value="draft" <?php echo $filters['status'] === 'draft' ? 'selected' : ''; ?>>
                                    Taslak
                                </option>
                                <option value="scheduled" <?php echo $filters['status'] === 'scheduled' ? 'selected' : ''; ?>>
                                    Planlandı
                                </option>
                                <option value="published" <?php echo $filters['status'] === 'published' ? 'selected' : ''; ?>>
                                    Yayınlandı
                                </option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <button type="submit" class="btn btn-secondary w-100">
                                Filtrele
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($result['success'] && !empty($result['data']['posts'])): ?>
                <div class="row">
                    <?php foreach ($result['data']['posts'] as $post): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100">
                                <?php if ($post['featured_image']): ?>
                                    <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" 
                                         class="card-img-top" 
                                         alt="<?php echo htmlspecialchars($post['title']); ?>">
                                <?php endif; ?>
                                
                                <div class="card-body">
                                    <h5 class="card-title mb-3">
                                        <?php echo htmlspecialchars($post['title']); ?>
                                    </h5>
                                    
                                    <p class="card-text text-muted small mb-3">
                                        <?php echo mb_substr(strip_tags($post['excerpt'] ?: $post['content']), 0, 150) . '...'; ?>
                                    </p>

                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="badge bg-<?php echo getStatusBadgeClass($post['status']); ?>">
                                            <?php echo getStatusLabel($post['status']); ?>
                                        </span>
                                        
                                        <div class="text-muted small">
                                            <?php echo formatDate($post['created_at']); ?>
                                        </div>
                                    </div>

                                    <div class="post-stats d-flex justify-content-between mb-3">
                                        <div class="stat">
                                            <i class="fas fa-globe"></i>
                                            <?php echo $post['target_count']; ?> site
                                        </div>
                                        <div class="stat">
                                            <i class="fas fa-check-circle"></i>
                                            <?php echo $post['publish_count']; ?> yayın
                                        </div>
                                    </div>

                                    <div class="post-actions">
                                        <a href="edit.php?id=<?php echo $post['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary me-2">
                                            <i class="fas fa-edit"></i> Düzenle
                                        </a>
                                        
                                        <?php if ($post['status'] === 'draft'): ?>
                                            <a href="publish.php?id=<?php echo $post['id']; ?>" 
                                               class="btn btn-sm btn-success me-2">
                                                <i class="fas fa-paper-plane"></i> Yayınla
                                            </a>
                                        <?php endif; ?>
                                        
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deletePostModal"
                                                data-post-id="<?php echo $post['id']; ?>"
                                                data-post-title="<?php echo htmlspecialchars($post['title']); ?>">
                                            <i class="fas fa-trash"></i>
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
                                    <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo urlencode($filters['status']); ?>&search=<?php echo urlencode($filters['search']); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>

            <?php else: ?>
                <div class="text-center py-5">
                    <img src="../../assets/images/no-posts.svg" alt="İçerik yok" class="mb-4" style="max-width: 200px;">
                    <h4>Henüz İçerik Yok</h4>
                    <p class="text-muted">
                        Yeni bir içerik oluşturarak WordPress sitelerinize paylaşmaya başlayın.
                    </p>
                    <a href="create.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> İlk İçeriğimi Oluştur
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- Delete Post Modal -->
<div class="modal fade" id="deletePostModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">İçeriği Sil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>
                    <strong id="deletePostTitle"></strong> içeriğini silmek istediğinizden emin misiniz?
                    Bu işlem geri alınamaz.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <form action="delete.php" method="POST" class="d-inline">
                    <input type="hidden" name="post_id" id="deletePostId">
                    <button type="submit" class="btn btn-danger">İçeriği Sil</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('deletePostModal').addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const postId = button.getAttribute('data-post-id');
    const postTitle = button.getAttribute('data-post-title');
    
    document.getElementById('deletePostId').value = postId;
    document.getElementById('deletePostTitle').textContent = postTitle;
});
</script>

<?php
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'draft': return 'secondary';
        case 'scheduled': return 'info';
        case 'published': return 'success';
        default: return 'secondary';
    }
}

function getStatusLabel($status) {
    switch ($status) {
        case 'draft': return 'Taslak';
        case 'scheduled': return 'Planlandı';
        case 'published': return 'Yayınlandı';
        default: return 'Bilinmiyor';
    }
}

function formatDate($date) {
    return date('d.m.Y H:i', strtotime($date));
}
?>

<?php require_once '../../templates/footer.php'; ?>