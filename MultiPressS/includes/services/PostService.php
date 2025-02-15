<?php
class PostService {
    private $db;
    private $userId;

    public function __construct($userId) {
        $this->db = Database::getInstance()->getConnection();
        $this->userId = $userId;
    }

    public function createPost($data) {
        try {
            $this->db->beginTransaction();

            // Ana içeriği kaydet
            $stmt = $this->db->prepare("
                INSERT INTO mph_posts (
                    user_id, 
                    title, 
                    content, 
                    excerpt,
                    featured_image,
                    status,
                    scheduled_time
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $this->userId,
                $data['title'],
                $data['content'],
                $data['excerpt'] ?? '',
                $data['featured_image'] ?? null,
                $data['status'] ?? 'draft',
                $data['scheduled_time'] ?? null
            ]);

            $postId = $this->db->lastInsertId();

            // Site hedeflerini kaydet
            if (!empty($data['site_targets'])) {
                $this->savePostTargets($postId, $data['site_targets']);
            }

            // Medya dosyalarını kaydet
            if (!empty($data['media'])) {
                $this->savePostMedia($postId, $data['media']);
            }

            $this->db->commit();

            // Eğer hemen yayınlanacaksa, hedef sitelere gönder
            if ($data['status'] === 'publish' && empty($data['scheduled_time'])) {
                $this->publishToTargetSites($postId);
            }

            return [
                'success' => true,
                'message' => 'İçerik başarıyla oluşturuldu',
                'post_id' => $postId
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function savePostTargets($postId, $targets) {
        $stmt = $this->db->prepare("
            INSERT INTO mph_post_targets (
                post_id, 
                site_id, 
                category_id, 
                tags
            ) VALUES (?, ?, ?, ?)
        ");

        foreach ($targets as $target) {
            $stmt->execute([
                $postId,
                $target['site_id'],
                $target['category_id'] ?? null,
                json_encode($target['tags'] ?? [])
            ]);
        }
    }

    private function savePostMedia($postId, $media) {
        $stmt = $this->db->prepare("
            INSERT INTO mph_post_media (
                post_id, 
                file_name, 
                file_path, 
                file_type,
                file_size
            ) VALUES (?, ?, ?, ?, ?)
        ");

        foreach ($media as $file) {
            $stmt->execute([
                $postId,
                $file['name'],
                $file['path'],
                $file['type'],
                $file['size']
            ]);
        }
    }

    public function publishToTargetSites($postId) {
        try {
            // İçerik bilgilerini al
            $post = $this->getPost($postId);
            if (!$post) {
                throw new Exception('İçerik bulunamadı');
            }

            // Hedef siteleri al
            $targets = $this->getPostTargets($postId);
            
            $results = [];
            foreach ($targets as $target) {
                // Site bilgilerini al
                $site = $this->getSiteInfo($target['site_id']);
                
                // WordPress API client oluştur
                $client = new WordPressApiClient(
                    $site['api_url'],
                    $site['consumer_key'],
                    $site['consumer_secret']
                );

                // Medya dosyalarını yükle
                $mediaIds = [];
                if ($post['featured_image']) {
                    $mediaUpload = $client->uploadMedia([
                        'tmp_name' => $post['featured_image'],
                        'type' => mime_content_type($post['featured_image']),
                        'name' => basename($post['featured_image'])
                    ]);
                    
                    if ($mediaUpload['success']) {
                        $mediaIds['featured_media'] = $mediaUpload['media']['id'];
                    }
                }

                // İçeriği gönder
                $publishResult = $client->createPost([
                    'title' => $post['title'],
                    'content' => $post['content'],
                    'excerpt' => $post['excerpt'],
                    'status' => 'publish',
                    'categories' => [$target['category_id']],
                    'tags' => json_decode($target['tags'], true),
                    'featured_media' => $mediaIds['featured_media'] ?? null
                ]);

                $results[$target['site_id']] = $publishResult;

                // Sonucu kaydet
                $this->savePublishResult($postId, $target['site_id'], $publishResult);
            }

            return [
                'success' => true,
                'message' => 'İçerik hedef sitelere gönderildi',
                'results' => $results
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function savePublishResult($postId, $siteId, $result) {
        $stmt = $this->db->prepare("
            INSERT INTO mph_publish_results (
                post_id,
                site_id,
                status,
                remote_post_id,
                remote_url,
                error_message
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $postId,
            $siteId,
            $result['success'] ? 'success' : 'error',
            $result['success'] ? $result['post']['id'] : null,
            $result['success'] ? $result['post']['link'] : null,
            $result['success'] ? null : $result['message']
        ]);
    }

    public function getPost($postId) {
        $stmt = $this->db->prepare("
            SELECT * FROM mph_posts 
            WHERE id = ? AND user_id = ?
        ");
        
        $stmt->execute([$postId, $this->userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getPostTargets($postId) {
        $stmt = $this->db->prepare("
            SELECT * FROM mph_post_targets 
            WHERE post_id = ?
        ");
        
        $stmt->execute([$postId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getSiteInfo($siteId) {
        $stmt = $this->db->prepare("
            SELECT * FROM mph_wordpress_sites 
            WHERE id = ? AND user_id = ?
        ");
        
        $stmt->execute([$siteId, $this->userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getPosts($page = 1, $perPage = 10, $filters = []) {
        try {
            $where = ['user_id = ?'];
            $params = [$this->userId];

            if (!empty($filters['status'])) {
                $where[] = 'status = ?';
                $params[] = $filters['status'];
            }

            if (!empty($filters['search'])) {
                $where[] = '(title LIKE ? OR content LIKE ?)';
                $params[] = '%' . $filters['search'] . '%';
                $params[] = '%' . $filters['search'] . '%';
            }

            $whereClause = implode(' AND ', $where);
            
            // Toplam kayıt sayısı
            $countStmt = $this->db->prepare("
                SELECT COUNT(*) as total 
                FROM mph_posts 
                WHERE {$whereClause}
            ");
            
            $countStmt->execute($params);
            $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Sayfalama
            $offset = ($page - 1) * $perPage;
            
            // İçerikleri getir
            $stmt = $this->db->prepare("
                SELECT p.*, 
                    (SELECT COUNT(*) FROM mph_post_targets WHERE post_id = p.id) as target_count,
                    (SELECT COUNT(*) FROM mph_publish_results WHERE post_id = p.id AND status = 'success') as publish_count
                FROM mph_posts p
                WHERE {$whereClause}
                ORDER BY created_at DESC
                LIMIT ? OFFSET ?
            ");

            array_push($params, $perPage, $offset);
            $stmt->execute($params);
            
            return [
                'success' => true,
                'data' => [
                    'posts' => $stmt->fetchAll(PDO::FETCH_ASSOC),
                    'pagination' => [
                        'total' => $total,
                        'per_page' => $perPage,
                        'current_page' => $page,
                        'last_page' => ceil($total / $perPage)
                    ]
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}