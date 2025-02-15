<?php
// admin/settings/index.php
require_once '../../includes/autoload.php';
require_once '../../includes/admin_auth_check.php';

$settingsService = new SettingsService();
$settings = $settingsService->getAllSettings();

$pageTitle = "Sistem Ayarları";
require_once '../../templates/admin/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once '../../templates/admin/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Sistem Ayarları</h1>
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

            <div class="card">
                <div class="card-body">
                    <form action="update.php" method="POST" enctype="multipart/form-data">
                        <!-- Site Ayarları -->
                        <div class="mb-4">
                            <h4 class="card-title mb-3">Site Ayarları</h4>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Site Başlığı</label>
                                    <input type="text" name="site_title" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['site_title'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Site Açıklaması</label>
                                    <input type="text" name="site_description" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['site_description'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Site Logo</label>
                                    <input type="file" name="site_logo" class="form-control">
                                    <?php if (!empty($settings['site_logo'])): ?>
                                        <img src="<?php echo htmlspecialchars($settings['site_logo']); ?>" 
                                             alt="Site Logo" class="mt-2" style="max-height: 50px;">
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Favicon</label>
                                    <input type="file" name="site_favicon" class="form-control">
                                    <?php if (!empty($settings['site_favicon'])): ?>
                                        <img src="<?php echo htmlspecialchars($settings['site_favicon']); ?>" 
                                             alt="Favicon" class="mt-2" style="max-height: 32px;">
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Bakım Modu</label>
                                    <div class="form-check form-switch">
                                        <input type="checkbox" name="maintenance_mode" class="form-check-input" 
                                               value="1" <?php echo ($settings['maintenance_mode'] ?? '') == '1' ? 'checked' : ''; ?>>
                                        <label class="form-check-label">Bakım Modunu Etkinleştir</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- E-posta Ayarları -->
                        <div class="mb-4">
                            <h4 class="card-title mb-3">E-posta Ayarları</h4>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">SMTP Sunucu</label>
                                    <input type="text" name="smtp_host" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['smtp_host'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">SMTP Port</label>
                                    <input type="text" name="smtp_port" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['smtp_port'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">SMTP Kullanıcı Adı</label>
                                    <input type="text" name="smtp_username" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['smtp_username'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">SMTP Şifre</label>
                                    <input type="password" name="smtp_password" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['smtp_password'] ?? ''); ?>">
                                </div>
                                <div class="col-md-12">
                                    <button type="button" class="btn btn-info" onclick="testSMTP()">
                                        <i class="fas fa-envelope me-2"></i>SMTP Ayarlarını Test Et
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Ödeme Ayarları -->
                        <div class="mb-4">
                            <h4 class="card-title mb-3">Ödeme Ayarları</h4>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">PayTR Merchant ID</label>
                                    <input type="text" name="paytr_merchant_id" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['paytr_merchant_id'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">PayTR Merchant Key</label>
                                    <input type="password" name="paytr_merchant_key" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['paytr_merchant_key'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">PayTR Merchant Salt</label>
                                    <input type="password" name="paytr_merchant_salt" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['paytr_merchant_salt'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Test Modu</label>
                                    <div class="form-check form-switch">
                                        <input type="checkbox" name="paytr_test_mode" class="form-check-input" 
                                               value="1" <?php echo ($settings['paytr_test_mode'] ?? '') == '1' ? 'checked' : ''; ?>>
                                        <label class="form-check-label">Test Modunu Etkinleştir</label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <button type="button" class="btn btn-info" onclick="testPayTR()">
                                        <i class="fas fa-credit-card me-2"></i>PayTR Ayarlarını Test Et
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Sosyal Medya Ayarları -->
                        <div class="mb-4">
                            <h4 class="card-title mb-3">Sosyal Medya Ayarları</h4>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Facebook</label>
                                    <input type="url" name="social_facebook" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['social_facebook'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Twitter</label>
                                    <input type="url" name="social_twitter" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['social_twitter'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Instagram</label>
                                    <input type="url" name="social_instagram" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['social_instagram'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">LinkedIn</label>
                                    <input type="url" name="social_linkedin" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['social_linkedin'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Sistem Ayarları -->
                        <div class="mb-4">
                            <h4 class="card-title mb-3">Sistem Ayarları</h4>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Zaman Dilimi</label>
                                    <select name="timezone" class="form-select">
                                        <?php foreach (DateTimeZone::listIdentifiers() as $tz): ?>
                                            <option value="<?php echo $tz; ?>" 
                                                    <?php echo ($settings['timezone'] ?? '') == $tz ? 'selected' : ''; ?>>
                                                <?php echo $tz; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Maksimum Dosya Boyutu (MB)</label>
                                    <input type="number" name="max_upload_size" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['max_upload_size'] ?? '5'); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">İzin Verilen Dosya Türleri</label>
                                    <input type="text" name="allowed_file_types" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['allowed_file_types'] ?? 'jpg,jpeg,png,gif,pdf,doc,docx'); ?>">
                                    <small class="text-muted">Virgülle ayırarak yazın (örn: jpg,png,pdf)</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Cache Süresi (saniye)</label>
                                    <input type="number" name="cache_duration" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['cache_duration'] ?? '3600'); ?>">
                                </div>
                                <div class="col-md-12">
                                    <button type="button" class="btn btn-info" onclick="testCache()">
                                        <i class="fas fa-memory me-2"></i>Cache Sistemini Test Et
                                    </button>
                                    <button type="button" class="btn btn-warning ms-2" onclick="clearCache()">
                                        <i class="fas fa-broom me-2"></i>Cache Temizle
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Ayarları Kaydet
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Test Ayarları Bölümü -->
            <div class="mt-4">
                <div class="row g-3">
                    <!-- SMTP Test -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">SMTP Testi</h5>
                                <div class="mb-3">
                                    <input type="email" id="test_email" class="form-control" placeholder="Test e-posta adresi">
                                </div>
                                <button type="button" class="btn btn-primary" onclick="testSMTP()">
                                    <i class="fas fa-envelope me-2"></i>SMTP Test Et
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- PayTR Test -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">PayTR Testi</h5>
                                <p class="text-muted">PayTR API bağlantısını test eder.</p>
                                <button type="button" class="btn btn-primary" onclick="testPayTR()">
                                    <i class="fas fa-credit-card me-2"></i>PayTR Test Et
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Cache Test -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Cache Testi</h5>
                                <p class="text-muted">Cache sisteminin çalışmasını test eder.</p>
                                <button type="button" class="btn btn-primary" onclick="testCache()">
                                    <i class="fas fa-memory me-2"></i>Cache Test Et
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Upload Test -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Dosya Yükleme Testi</h5>
                                <div class="mb-3">
                                    <input type="file" id="test_file" class="form-control">
                                </div>
                                <button type="button" class="btn btn-primary" onclick="testUpload()">
                                    <i class="fas fa-upload me-2"></i>Yükleme Test Et
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Test İşlemleri için JavaScript Kodları -->
<script>
async function showAlert(message, type = 'success') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.querySelector('main').insertBefore(alertDiv, document.querySelector('main').firstChild);
    
    setTimeout(() => alertDiv.remove(), 5000);
}

async function testSMTP() {
    const testEmail = document.getElementById('test_email').value;
    if (!testEmail) {
        showAlert('Lütfen test için bir e-posta adresi girin.', 'warning');
        return;
    }

    try {
        const response = await fetch('test.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=test_smtp&test_email=${encodeURIComponent(testEmail)}`
        });
        
        const result = await response.json();
        showAlert(result.message, result.success ? 'success' : 'danger');
    } catch (error) {
        showAlert('Test sırasında bir hata oluştu: ' + error.message, 'danger');
    }
}

async function testPayTR() {
    try {
        const response = await fetch('test.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=test_paytr'
        });
        
        const result = await response.json();
        showAlert(result.message, result.success ? 'success' : 'danger');
    } catch (error) {
        showAlert('Test sırasında bir hata oluştu: ' + error.message, 'danger');
    }
}

async function testCache() {
    try {
        const response = await fetch('test.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=test_cache'
        });
        
        const result = await response.json();
        showAlert(result.message, result.success ? 'success' : 'danger');
    } catch (error) {
        showAlert('Test sırasında bir hata oluştu: ' + error.message, 'danger');
    }
}

async function testUpload() {
    const fileInput = document.getElementById('test_file');
    if (!fileInput.files.length) {
        showAlert('Lütfen test için bir dosya seçin.', 'warning');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'test_upload');
    formData.append('test_file', fileInput.files[0]);

    try {
        const response = await fetch('test.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        showAlert(result.message, result.success ? 'success' : 'danger');
    } catch (error) {
        showAlert('Test sırasında bir hata oluştu: ' + error.message, 'danger');
    }
}

async function clearCache() {
    try {
        const response = await fetch('test.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=clear_cache'
        });
        
        const result = await response.json();
        showAlert(result.message, result.success ? 'success' : 'danger');
    } catch (error) {
        showAlert('Cache temizleme sırasında bir hata oluştu: ' + error.message, 'danger');
    }
}
</script>

<?php require_once '../../templates/admin/footer.php'; ?>