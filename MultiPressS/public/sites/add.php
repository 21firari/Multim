<?php
require_once '../../includes/autoload.php';
require_once '../../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $siteService = new WordPressSiteService($_SESSION['user_id']);
    $result = $siteService->addSite([
        'site_name' => $_POST['site_name'] ?? '',
        'site_url' => $_POST['site_url'] ?? '',
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

$pageTitle = "Yeni Site Ekle - MultiPress Hub";
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
                            <h3 class="card-title mb-0">WordPress Site Ekle</h3>
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
                                           required>
                                    <div class="form-text">
                                        Sitenizi tanımlamak için kullanacağınız isim
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="site_url" class="form-label">Site URL</label>
                                    <input type="url" 
                                           class="form-control" 
                                           id="site_url" 
                                           name="site_url" 
                                           placeholder="https://example.com"
                                           required>
                                    <div class="form-text">
                                        WordPress sitenizin ana adresi
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="api_url" class="form-label">REST API URL</label>
                                    <input type="url" 
                                           class="form-control" 
                                           id="api_url" 
                                           name="api_url" 
                                           placeholder="https://example.com/wp-json/wp/v2"
                                           required>
                                    <div class="form-text">
                                        WordPress REST API endpoint adresi
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="consumer_key" class="form-label">Consumer Key</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="consumer_key" 
                                           name="consumer_key" 
                                           required>
                                    <div class="form-text">
                                        WordPress API Consumer Key
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="consumer_secret" class="form-label">Consumer Secret</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="consumer_secret" 
                                           name="consumer_secret" 
                                           required>
                                    <div class="form-text">
                                        WordPress API Consumer Secret
                                    </div>
                                </div>

                                <div class="api-help-accordion mb-4">
                                    <div class="accordion" id="apiHelpAccordion">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button collapsed" type="button" 
                                                        data-bs-toggle="collapse" 
                                                        data-bs-target="#apiHelpContent">
                                                    API Bilgilerini Nasıl Alırım?
                                                </button>
                                            </h2>
                                            <div id="apiHelpContent" class="accordion-collapse collapse" 
                                                 data-bs-parent="#apiHelpAccordion">
                                                <div class="accordion-body">
                                                    <ol class="mb-0">
                                                        <li>WordPress yönetici panelinize giriş yapın</li>
                                                        <li>Eklentiler > Yeni Ekle menüsüne gidin</li>
                                                        <li>"WP REST API Authentication" eklentisini aratın ve kurun</li>
                                                        <li>Eklentiyi etkinleştirin</li>
                                                        <li>Ayarlar > WP REST API Auth menüsüne gidin</li>
                                                        <li>"Add New Application" butonuna tıklayın</li>
                                                        <li>Uygulama adını girin (örn: MultiPress Hub)</li>
                                                        <li>Consumer Key ve Consumer Secret bilgilerini kopyalayın</li>
                                                    </ol>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <a href="index.php" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Geri Dön
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Site Ekle
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

// Site URL'den API URL otomatik doldurma
document.getElementById('site_url').addEventListener('blur', function() {
    const siteUrl = this.value.trim();
    if (siteUrl) {
        const apiUrl = siteUrl.replace(/\/?$/, '/wp-json/wp/v2');
        document.getElementById('api_url').value = apiUrl;
    }
});
</script>

<?php require_once '../../templates/footer.php'; ?>