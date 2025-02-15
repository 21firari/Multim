<?php
require_once '../../includes/autoload.php';
require_once '../../includes/auth_check.php';

// WordPress sitelerini getir
$siteService = new WordPressSiteService($_SESSION['user_id']);
$sites = $siteService->getUserSites();

$pageTitle = "Yeni İçerik Oluştur - MultiPress Hub";
require_once '../../templates/header.php';
?>
<!-- Dropzone CSS -->
<link href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" rel="stylesheet" type="text/css" />
<!-- Dropzone JS -->
<script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>

<!-- TinyMCE CDN -->
<script src="https://cdn.tiny.cloud/1/YOUR_API_KEY/tinymce/6/tinymce.min.js"></script>

<div class="dashboard-container">
    <?php require_once '../../templates/sidebar.php'; ?>

    <main class="main-content">
        <?php require_once '../../templates/topbar.php'; ?>

        <div class="container-fluid py-4">
            <form id="postForm" method="POST" action="save.php" enctype="multipart/form-data">
                <div class="row">
                    <!-- Sol Kolon: İçerik Editörü -->
                    <div class="col-lg-8">
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="mb-4">
                                    <label for="title" class="form-label">Başlık</label>
                                    <input type="text" 
                                           class="form-control form-control-lg" 
                                           id="title" 
                                           name="title" 
                                           required>
                                </div>

                                <div class="mb-4">
                                    <label for="content" class="form-label">İçerik</label>
                                    <textarea id="content" name="content"></textarea>
                                </div>

                                <div class="mb-4">
                                    <label for="excerpt" class="form-label">Özet</label>
                                    <textarea class="form-control" 
                                              id="excerpt" 
                                              name="excerpt" 
                                              rows="3"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Medya Yükleme -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Medya</h5>
                            </div>
                            <div class="card-body">
                                <div class="featured-image-preview mb-3">
                                    <img id="featuredImagePreview" 
                                         src="../../assets/images/placeholder-image.png" 
                                         class="img-fluid d-none" 
                                         alt="Öne Çıkan Görsel">
                                </div>

                                <div class="mb-3">
                                    <label for="featuredImage" class="form-label">
                                        Öne Çıkan Görsel
                                    </label>
                                    <input type="file" 
                                           class="form-control" 
                                           id="featuredImage" 
                                           name="featured_image" 
                                           accept="image/*">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">İçerik Görselleri</label>
                                    <div id="mediaDropzone" class="dropzone">
                                        <!-- Dropzone.js buraya entegre edilecek -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sağ Kolon: Ayarlar -->
                    <div class="col-lg-4">
                        <!-- Yayın Ayarları -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Yayın Ayarları</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label d-block">Durum</label>
                                    <div class="btn-group w-100" role="group">
                                        <input type="radio" 
                                               class="btn-check" 
                                               name="status" 
                                               id="statusDraft" 
                                               value="draft" 
                                               checked>
                                        <label class="btn btn-outline-secondary" for="statusDraft">
                                            Taslak
                                        </label>

                                        <input type="radio" 
                                               class="btn-check" 
                                               name="status" 
                                               id="statusSchedule" 
                                               value="scheduled">
                                        <label class="btn btn-outline-secondary" for="statusSchedule">
                                            Planla
                                        </label>

                                        <input type="radio" 
                                               class="btn-check" 
                                               name="status" 
                                               id="statusPublish" 
                                               value="publish">
                                        <label class="btn btn-outline-secondary" for="statusPublish">
                                            Yayınla
                                        </label>
                                    </div>
                                </div>

                                <div id="scheduleDateTime" class="mb-3 d-none">
                                    <label for="scheduledTime" class="form-label">
                                        Yayın Tarihi ve Saati
                                    </label>
                                    <input type="datetime-local" 
                                           class="form-control" 
                                           id="scheduledTime" 
                                           name="scheduled_time">
                                </div>
                            </div>
                        </div>

                        <!-- Hedef Siteler -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Hedef Siteler</h5>
                            </div>
                            <div class="card-body">
                                <?php if ($sites['success'] && !empty($sites['data'])): ?>
                                    <div class="site-list">
                                        <?php foreach ($sites['data'] as $site): ?>
                                            <div class="site-item mb-3 border-bottom pb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input site-checkbox" 
                                                           type="checkbox" 
                                                           name="site_targets[]" 
                                                           value="<?php echo $site['id']; ?>" 
                                                           id="site<?php echo $site['id']; ?>">
                                                    <label class="form-check-label" 
                                                           for="site<?php echo $site['id']; ?>">
                                                        <?php echo htmlspecialchars($site['site_name']); ?>
                                                    </label>
                                                </div>

                                                <div class="site-options ms-4 mt-2 d-none">
                                                    <select class="form-select mb-2" 
                                                            name="site_category[<?php echo $site['id']; ?>]">
                                                        <option value="">Kategori Seç...</option>
                                                        <!-- Kategoriler AJAX ile yüklenecek -->
                                                    </select>

                                                    <input type="text" 
                                                           class="form-control" 
                                                           name="site_tags[<?php echo $site['id']; ?>]" 
                                                           placeholder="Etiketler (virgülle ayırın)">
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-3">
                                        <p class="text-muted mb-0">
                                            Henüz WordPress site eklenmemiş.
                                        </p>
                                        <a href="../sites/add.php" class="btn btn-sm btn-primary mt-2">
                                            Site Ekle
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Kaydet Butonu -->
                        <div class="card">
                            <div class="card-body">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-save"></i> Kaydet
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
// TinyMCE Editör Konfigürasyonu
tinymce.init({
    selector: '#content',
    height: 500,
    plugins: [
        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
        'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
        'insertdatetime', 'media', 'table', 'help', 'wordcount'
    ],
    toolbar: 'undo redo | blocks | ' +
        'bold italic backcolor | alignleft aligncenter ' +
        'alignright alignjustify | bullist numlist outdent indent | ' +
        'removeformat | help',
    content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial; font-size: 16px; }',
    images_upload_url: '../api/upload-image.php',
    images_upload_handler: function (blobInfo, success, failure) {
        let formData = new FormData();
        formData.append('file', blobInfo.blob(), blobInfo.filename());

        fetch('../api/upload-image.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                success(result.url);
            } else {
                failure(result.message);
            }
        })
        .catch(error => failure('Görsel yüklenirken hata oluştu.'));
    }
});

// Öne Çıkan Görsel Önizleme
document.getElementById('featuredImage').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('featuredImagePreview');
            preview.src = e.target.result;
            preview.classList.remove('d-none');
        }
        reader.readAsDataURL(file);
    }
});

// Planlama Seçeneği Kontrolü
document.querySelectorAll('input[name="status"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const scheduleDateTime = document.getElementById('scheduleDateTime');
        if (this.value === 'scheduled') {
            scheduleDateTime.classList.remove('d-none');
        } else {
            scheduleDateTime.classList.add('d-none');
        }
    });
});

// Site Seçimi ve Kategori/Etiket Kontrolü
document.querySelectorAll('.site-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const options = this.closest('.site-item').querySelector('.site-options');
        if (this.checked) {
            options.classList.remove('d-none');
            // Kategorileri yükle
            loadCategories(this.value, options.querySelector('select'));
        } else {
            options.classList.add('d-none');
        }
    });
});

// WordPress Kategorilerini Yükle
function loadCategories(siteId, selectElement) {
    fetch(`../api/get-categories.php?site_id=${siteId}`)
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                selectElement.innerHTML = '<option value="">Kategori Seç...</option>';
                result.categories.forEach(category => {
                    selectElement.innerHTML += `
                        <option value="${category.id}">
                            ${category.name}
                        </option>`;
                });
            }
        });
}

Dropzone.autoDiscover = false;

const mediaDropzone = new Dropzone("#mediaDropzone", {
    url: "../api/upload-image.php",
    paramName: "file",
    maxFilesize: 5, // MB
    acceptedFiles: "image/*",
    addRemoveLinks: true,
    dictDefaultMessage: "Görselleri buraya sürükleyin veya tıklayarak seçin",
    dictRemoveFile: "Sil",
    dictCancelUpload: "İptal",
    dictFileTooBig: "Dosya boyutu çok büyük ({{filesize}}MB). Maximum dosya boyutu: {{maxFilesize}}MB.",
    init: function() {
        this.on("success", function(file, response) {
            if (response.success) {
                // Yüklenen dosya bilgilerini form'a ekle
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'media_files[]';
                input.value = response.id;
                document.getElementById('postForm').appendChild(input);
                
                // Dosya nesnesine ID'yi ekle (silme işlemi için)
                file.mediaId = response.id;
            }
        });

        this.on("removedfile", function(file) {
            if (file.mediaId) {
                // Dosya ID'sini form'dan kaldır
                const input = document.querySelector(`input[value="${file.mediaId}"]`);
                if (input) input.remove();
                
                // Sunucudan dosyayı sil
                fetch('../api/delete-media.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        media_id: file.mediaId
                    })
                });
            }
        });
    }
});
</script>

<?php require_once '../../templates/footer.php'; ?>