<?php
require_once '../../includes/autoload.php';
require_once '../../includes/auth_check.php';

$postId = $_GET['id'] ?? 0;
if (!$postId) {
    $_SESSION['error'] = 'Geçersiz içerik ID\'si.';
    header('Location: index.php');
    exit;
}

$postService = new PostService($_SESSION['user_id']);
$post = $postService->getPost($postId);

if (!$post['success']) {
    $_SESSION['error'] = 'İçerik bulunamadı.';
    header('Location: index.php');
    exit;
}

$siteService = new WordPressSiteService($_SESSION['user_id']);
$sites = $siteService->getUserSites();

$pageTitle = "İçerik Düzenle - " . htmlspecialchars($post['data']['title']);
require_once '../../templates/header.php';
?>

<!-- TinyMCE ve Dropzone CDN'leri -->
<script src="https://cdn.tiny.cloud/1/YOUR_API_KEY/tinymce/6/tinymce.min.js"></script>
<link href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" rel="stylesheet" type="text/css" />
<script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>

<div class="dashboard-container">
    <?php require_once '../../templates/sidebar.php'; ?>

    <main class="main-content">
        <?php require_once '../../templates/topbar.php'; ?>

        <div class="container-fluid py-4">
            <form id="postForm" method="POST" action="update.php" enctype="multipart/form-data">
                <input type="hidden" name="post_id" value="<?php echo $postId; ?>">
                
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="mb-4">
                                    <label for="title" class="form-label">Başlık</label>
                                    <input type="text" 
                                           class="form-control form-control-lg" 
                                           id="title" 
                                           name="title" 
                                           value="<?php echo htmlspecialchars($post['data']['title']); ?>" 
                                           required>
                                </div>

                                <div class="mb-4">
                                    <label for="content" class="form-label">İçerik</label>
                                    <textarea id="content" name="content">
                                        <?php echo htmlspecialchars($post['data']['content']); ?>
                                    </textarea>
                                </div>

                                <div class="mb-4">
                                    <label for="excerpt" class="form-label">Özet</label>
                                    <textarea class="form-control" 
                                              id="excerpt" 
                                              name="excerpt" 
                                              rows="3"><?php echo htmlspecialchars($post['data']['excerpt']); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Medya Bölümü -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Medya</h5>
                            </div>
                            <div class="card-body">
                                <div class="featured-image-preview mb-3">
                                    <?php if ($post['data']['featured_image']): ?>
                                        <img id="featuredImagePreview" 
                                             src="<?php echo htmlspecialchars($post['data']['featured_image']); ?>" 
                                             class="img-fluid" 
                                             alt="Öne Çıkan Görsel">
                                    <?php else: ?>
                                        <img id="featuredImagePreview" 
                                             src="../../assets/images/placeholder-image.png" 
                                             class="img-fluid d-none" 
                                             alt="Öne Çıkan Görsel">
                                    <?php endif; ?>
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
                                        <!-- Mevcut görseller JavaScript ile yüklenecek -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sağ Kolon -->
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
                                        <?php
                                        $statuses = [
                                            'draft' => 'Taslak',
                                            'scheduled' => 'Planla',
                                            'publish' => 'Yayınla'
                                        ];
                                        foreach ($statuses as $value => $label):
                                        ?>
                                            <input type="radio" 
                                                   class="btn-check" 
                                                   name="status" 
                                                   id="status<?php echo ucfirst($value); ?>" 
                                                   value="<?php echo $value; ?>"
                                                   <?php echo $post['data']['status'] === $value ? 'checked' : ''; ?>>
                                            <label class="btn btn-outline-secondary" 
                                                   for="status<?php echo ucfirst($value); ?>">
                                                <?php echo $label; ?>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <div id="scheduleDateTime" 
                                     class="mb-3 <?php echo $post['data']['status'] !== 'scheduled' ? 'd-none' : ''; ?>">
                                    <label for="scheduledTime" class="form-label">
                                        Yayın Tarihi ve Saati
                                    </label>
                                    <input type="datetime-local" 
                                           class="form-control" 
                                           id="scheduledTime" 
                                           name="scheduled_time"
                                           value="<?php echo $post['data']['scheduled_time'] ?? ''; ?>">
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
                                        <?php foreach ($sites['data'] as $site): 
                                            $isTargeted = in_array($site['id'], array_column($post['data']['targets'], 'site_id'));
                                            $target = $isTargeted ? array_filter($post['data']['targets'], 
                                                fn($t) => $t['site_id'] === $site['id'])[0] : null;
                                        ?>
                                            <div class="site-item mb-3 border-bottom pb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input site-checkbox" 
                                                           type="checkbox" 
                                                           name="site_targets[]" 
                                                           value="<?php echo $site['id']; ?>" 
                                                           id="site<?php echo $site['id']; ?>"
                                                           <?php echo $isTargeted ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" 
                                                           for="site<?php echo $site['id']; ?>">
                                                        <?php echo htmlspecialchars($site['site_name']); ?>
                                                    </label>
                                                </div>

                                                <div class="site-options ms-4 mt-2 <?php echo !$isTargeted ? 'd-none' : ''; ?>">
                                                    <select class="form-select mb-2" 
                                                            name="site_category[<?php echo $site['id']; ?>]"
                                                            data-selected="<?php echo $target['category_id'] ?? ''; ?>">
                                                        <option value="">Kategori Seç...</option>
                                                    </select>

                                                    <input type="text" 
                                                           class="form-control" 
                                                           name="site_tags[<?php echo $site['id']; ?>]" 
                                                           placeholder="Etiketler (virgülle ayırın)"
                                                           value="<?php echo htmlspecialchars(implode(',', $target['tags'] ?? [])); ?>">
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

                        <!-- Kaydet ve Sil Butonları -->
                        <div class="card">
                            <div class="card-body">
                                <button type="submit" class="btn btn-primary w-100 mb-2">
                                    <i class="fas fa-save"></i> Değişiklikleri Kaydet
                                </button>
                                
                                <button type="button" 
                                        class="btn btn-danger w-100"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deletePostModal">
                                    <i class="fas fa-trash"></i> İçeriği Sil
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </main>
</div>

<!-- Silme Modal -->
<div class="modal fade" id="deletePostModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">İçeriği Sil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>
                    <strong><?php echo htmlspecialchars($post['data']['title']); ?></strong> 
                    içeriğini silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <form action="delete.php" method="POST">
                    <input type="hidden" name="post_id" value="<?php echo $postId; ?>">
                    <button type="submit" class="btn btn-danger">İçeriği Sil</button>
                </form>
            </div>
        </div>
    </div>
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

// Dropzone Konfigürasyonu
Dropzone.autoDiscover = false;

const mediaDropzone = new Dropzone("#mediaDropzone", {
    url: "../api/upload-image.php",
    paramName: "file",
    maxFilesize: 5,
    acceptedFiles: "image/*",
    addRemoveLinks: true,
    dictDefaultMessage: "Görselleri buraya sürükleyin veya tıklayarak seçin",
    dictRemoveFile: "Sil",
    dictCancelUpload: "İptal",
    dictFileTooBig: "Dosya boyutu çok büyük ({{filesize}}MB). Maximum dosya boyutu: {{maxFilesize}}MB.",
    init: function() {
        // Mevcut görselleri yükle
        const existingMedia = <?php echo json_encode($post['data']['media'] ?? []); ?>;
        existingMedia.forEach(media => {
            const mockFile = { 
                name: media.file_name, 
                size: media.file_size,
                mediaId: media.id,
                accepted: true
            };
            
            this.emit("addedfile", mockFile);
            this.emit("thumbnail", mockFile, media.file_path);
            this.emit("complete", mockFile);
            this.files.push(mockFile);
        });

        this.on("success", function(file, response) {
            if (response.success) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'media_files[]';
                input.value = response.id;
                document.getElementById('postForm').appendChild(input);
                file.mediaId = response.id;
            }
        });

        this.on("removedfile", function(file) {
            if (file.mediaId) {
                const input = document.querySelector(`input[value="${file.mediaId}"]`);
                if (input) input.remove();
                
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
            loadCategories(this.value, options.querySelector('select'));
        } else {
            options.classList.add('d-none');
        }
    });
});

// WordPress Kategorilerini Yükle
async function loadCategories(siteId, selectElement) {
    try {
        const response = await fetch(`../api/get-categories.php?site_id=${siteId}`);
        const result = await response.json();
        
        if (result.success) {
            const selectedValue = selectElement.getAttribute('data-selected');
            
            selectElement.innerHTML = '<option value="">Kategori Seç...</option>';
            result.categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.name;
                if (category.id === parseInt(selectedValue)) {
                    option.selected = true;
                }
                selectElement.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Kategoriler yüklenirken hata oluştu:', error);
    }
}

// Form Gönderimi Öncesi Kontrol
document.getElementById('postForm').addEventListener('submit', function(e) {
    const title = document.getElementById('title').value.trim();
    const content = tinymce.get('content').getContent().trim();
    
    if (!title || !content) {
        e.preventDefault();
        alert('Başlık ve içerik alanları zorunludur.');
        return;
    }

    const status = document.querySelector('input[name="status"]:checked').value;
    if (status === 'scheduled') {
        const scheduledTime = document.getElementById('scheduledTime').value;
        if (!scheduledTime) {
            e.preventDefault();
            alert('Planlanan yayın tarihi ve saati seçilmelidir.');
            return;
        }
    }

    const selectedSites = document.querySelectorAll('.site-checkbox:checked');
    if (selectedSites.length === 0) {
        e.preventDefault();
        alert('En az bir hedef site seçilmelidir.');
        return;
    }
});

// Sayfa yüklendiğinde seçili sitelerin kategorilerini yükle
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.site-checkbox:checked').forEach(checkbox => {
        const select = checkbox.closest('.site-item').querySelector('select');
        loadCategories(checkbox.value, select);
    });
});
</script>

<?php require_once '../../templates/footer.php'; ?>