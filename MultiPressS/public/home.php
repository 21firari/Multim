<?php
require_once 'includes/autoload.php';
$pageTitle = "MultiPress Hub - WordPress Çoklu Site Yönetimi";
require_once 'templates/header.php';
?>

<!-- Hero Section -->
<section class="hero-section bg-gradient py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">WordPress Sitelerinizi Tek Panelden Yönetin</h1>
                <p class="lead mb-4">Tüm WordPress sitelerinizi tek bir yerden yönetin, içerik paylaşın ve zamandan tasarruf edin.</p>
                <div class="hero-buttons">
                    <a href="register.php" class="btn btn-primary btn-lg me-3">Ücretsiz Deneyin</a>
                    <a href="#features" class="btn btn-outline-light btn-lg">Özellikleri Keşfedin</a>
                </div>
            </div>
            <div class="col-lg-6">
                <img src="assets/images/hero-illustration.svg" alt="MultiPress Hub" class="img-fluid">
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section id="features" class="features-section py-5">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h2 class="display-5 fw-bold">Neden MultiPress Hub?</h2>
            <p class="lead">WordPress site yönetimini kolaylaştıran özellikler</p>
        </div>
        <div class="row g-4">
            <!-- Feature 1 -->
            <div class="col-md-4">
                <div class="feature-card p-4 text-center">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-sync-alt fa-3x"></i>
                    </div>
                    <h3>Toplu İçerik Yönetimi</h3>
                    <p>Tek tıkla tüm sitelerinize içerik gönderin, düzenleyin ve yönetin.</p>
                </div>
            </div>
            <!-- Feature 2 -->
            <div class="col-md-4">
                <div class="feature-card p-4 text-center">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-clock fa-3x"></i>
                    </div>
                    <h3>Zaman Tasarrufu</h3>
                    <p>Site yönetim sürenizi %80'e kadar azaltın.</p>
                </div>
            </div>
            <!-- Feature 3 -->
            <div class="col-md-4">
                <div class="feature-card p-4 text-center">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-shield-alt fa-3x"></i>
                    </div>
                    <h3>Güvenli Yönetim</h3>
                    <p>SSL şifrelemesi ve güvenli API bağlantıları ile güvenli yönetim.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Pricing Section -->
<section id="pricing" class="pricing-section py-5 bg-light">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h2 class="display-5 fw-bold">Fiyatlandırma</h2>
            <p class="lead">İhtiyacınıza uygun paketi seçin</p>
        </div>
        <div class="row g-4 justify-content-center">
            <?php
            $packages = require __DIR__ . '/../config/packages.php';
            foreach ($packages as $key => $package):
            ?>
            <div class="col-md-4">
                <div class="pricing-card <?php echo $key === 'professional' ? 'popular' : ''; ?>">
                    <div class="card h-100">
                        <div class="card-body">
                            <h3 class="card-title text-center"><?php echo $package['name']; ?></h3>
                            <div class="price text-center my-4">
                                <h4>₺<?php echo number_format($package['price'], 2); ?></h4>
                                <span class="period">/aylık</span>
                            </div>
                            <ul class="feature-list">
                                <?php foreach ($package['features'] as $feature): ?>
                                <li><i class="fas fa-check"></i> <?php echo $feature; ?></li>
                                <?php endforeach; ?>
                            </ul>
                           <div class="text-center mt-4">
    <a href="/public/register.php?package=<?php echo $key; ?>" class="btn btn-primary btn-lg w-100">
        <?php echo $package['price'] == 0 ? 'Ücretsiz Başla' : 'Hemen Başla'; ?>
    </a>
</div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once 'templates/footer.php'; ?>