<?php
require_once '../../includes/autoload.php';
require_once '../../includes/auth_check.php';

$siteId = $_GET['id'] ?? 0;
$siteService = new WordPressSiteService($_SESSION['user_id']);

// Site bilgilerini getir
$site = $siteService->getSite($siteId);

if (!$site['success']) {
    $_SESSION['error'] = $site['message'];
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $siteService->updateSite($siteId, [
        'site_name' => $_POST['site_name'] ?? '',
        'api_url' => $_POST['api_url'] ?? '',
        'consumer_key' => $_POST['consumer_key'] ?? '',
        'consumer_secret' => $_POST['consumer_secret'] ?? ''
    ]);

    if ($result['success']) {
        $_SESSION['success'] = $result['message'];
        header('Location: index.php');
        exit;
    } else {
        $_SESSION['error'] = $result['message'];
    }
}

$pageTitle = "Site Düzenle - " . htmlspecialchars($site['data']['site_name']);
require_once '../../templates/header.php';
?>

<div class="dashboard-container">
    <?php require_once '../../templates/sidebar.php'; ?>

    <main class="main-content">
        <?php require_once '../../templates/topbar.php'; ?>

        <div class="container-fluid py-4">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title mb-0">Site Düzenle</h3>
                        </div>
                        
                        <div class="card-body">
                            <?php if (isset($_SESSION['error'])): ?>
                                <div class="alert alert-danger">
                                    <?php 
                                    echo $_SESSION['error'];
                                    unset($_SESSION['error']);
                                    ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="" class="needs-validation" novalidate>
                                <div class="mb-4">
                                    <label for="site_name" class="form-label">Site Adı</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="site_name" 
                                           name="site_name" 
                                           value="<?php echo htmlspecialchars($site['data']['site_name']); ?>"
                                           required>
                                </div>

                                <div class="mb-4">
                                    <label for="site_url" class="form-label">Site URL</label>
                                    <input type="url" 
                                           class="form-control" 
                                           value="<?php echo htmlspecialchars($site['data']['site_url']); ?>"
                                           disabled>
                                    <div class="form-text">
                                        Site URL'i değiştirilemez. Yeni URL için yeni site eklemelisiniz.
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="api_url" class="form-label">REST API URL</label>
                                    <input type="url" 
                                           class="form-control" 
                                           id="api_url" 
                                           name="api_url" 
                                           value="<?php echo htmlspecialchars($site['data']['api_url']); ?>"
                                           required>
                                </div>

                                <div class="mb-4">
                                    <label for="consumer_key" class="form-label">Consumer Key</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="consumer_key" 
                                           name="consumer_key" 
                                           value="<?php echo htmlspecialchars($site['data']['consumer_key']); ?>"
                                           required>
                                </div>

                                <div class="mb-4">
                                    <label for="consumer_secret" class="form-label">Consumer Secret</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="consumer_secret" 
                                           name="consumer_secret" 
                                           placeholder="Değiştirmek için yeni secret girin">
                                    <div class="form-text">
                                        Boş bırakırsanız mevcut secret kullanılmaya devam edecek
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <a href="index.php" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Geri Dön
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Değişiklikleri Kaydet
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
// Form doğrulama
(function () {
    'use strict'
    const forms = document.querySelectorAll('.needs-validation')
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()
</script>

<?php require_once '../../templates/footer.php'; ?>