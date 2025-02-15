<?php
class WordPressSiteService {
    private $db;
    private $userId;

    public function __construct($userId) {
        $this->db = Database::getInstance()->getConnection();
        $this->userId = $userId;
    }

    public function addSite($data) {
        try {
            // Site URL'ini normalize et
            $siteUrl = rtrim($data['site_url'], '/');
            
            // Site zaten ekli mi kontrol et
            if ($this->siteExists($siteUrl)) {
                throw new Exception('Bu site zaten eklenmiş.');
            }

            // API bağlantısını test et
            $apiTest = $this->testApiConnection($data);
            if (!$apiTest['success']) {
                throw new Exception($apiTest['message']);
            }

            // Siteyi veritabanına ekle
            $stmt = $this->db->prepare("
                INSERT INTO mph_wordpress_sites 
                (user_id, site_name, site_url, api_url, consumer_key, consumer_secret, status) 
                VALUES (?, ?, ?, ?, ?, ?, 'active')
            ");

            $stmt->execute([
                $this->userId,
                $data['site_name'],
                $siteUrl,
                $data['api_url'],
                $data['consumer_key'],
                $this->encryptSecret($data['consumer_secret'])
            ]);

            return [
                'success' => true,
                'message' => 'Site başarıyla eklendi.',
                'site_id' => $this->db->lastInsertId()
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function updateSite($siteId, $data) {
        try {
            // Sitenin kullanıcıya ait olduğunu kontrol et
            if (!$this->validateSiteOwnership($siteId)) {
                throw new Exception('Bu siteyi düzenleme yetkiniz yok.');
            }

            // API bağlantısını test et
            $apiTest = $this->testApiConnection($data);
            if (!$apiTest['success']) {
                throw new Exception($apiTest['message']);
            }

            // Siteyi güncelle
            $stmt = $this->db->prepare("
                UPDATE mph_wordpress_sites 
                SET site_name = ?, 
                    api_url = ?, 
                    consumer_key = ?, 
                    consumer_secret = ? 
                WHERE id = ? AND user_id = ?
            ");

            $stmt->execute([
                $data['site_name'],
                $data['api_url'],
                $data['consumer_key'],
                $this->encryptSecret($data['consumer_secret']),
                $siteId,
                $this->userId
            ]);

            return [
                'success' => true,
                'message' => 'Site başarıyla güncellendi.'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function deleteSite($siteId) {
        try {
            // Sitenin kullanıcıya ait olduğunu kontrol et
            if (!$this->validateSiteOwnership($siteId)) {
                throw new Exception('Bu siteyi silme yetkiniz yok.');
            }

            // Siteyle ilişkili içerikleri kontrol et
            $postCount = $this->getRelatedPostCount($siteId);
            if ($postCount > 0) {
                throw new Exception('Bu siteye ait ' . $postCount . ' içerik bulunuyor. Önce içerikleri silmelisiniz.');
            }

            // Siteyi sil
            $stmt = $this->db->prepare("
                DELETE FROM mph_wordpress_sites 
                WHERE id = ? AND user_id = ?
            ");

            $stmt->execute([$siteId, $this->userId]);

            return [
                'success' => true,
                'message' => 'Site başarıyla silindi.'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getSites($page = 1, $perPage = 10) {
        try {
            // Toplam site sayısını al
            $totalStmt = $this->db->prepare("
                SELECT COUNT(*) as total 
                FROM mph_wordpress_sites 
                WHERE user_id = ?
            ");
            
            $totalStmt->execute([$this->userId]);
            $total = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Sayfalama hesapla
            $offset = ($page - 1) * $perPage;

            // Siteleri getir
            $stmt = $this->db->prepare("
                SELECT 
                    s.*,
                    (SELECT COUNT(*) FROM mph_posts WHERE site_id = s.id) as post_count,
                    (SELECT SUM(view_count) FROM mph_posts WHERE site_id = s.id) as total_views
                FROM mph_wordpress_sites s
                WHERE s.user_id = ?
                ORDER BY s.created_at DESC
                LIMIT ? OFFSET ?
            ");

            $stmt->execute([$this->userId, $perPage, $offset]);
            $sites = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => [
                    'sites' => $sites,
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

    public function testApiConnection($data) {
        try {
            $client = new WordPressApiClient(
                $data['api_url'],
                $data['consumer_key'],
                $data['consumer_secret']
            );

            $response = $client->testConnection();

            if (!$response['success']) {
                throw new Exception('API bağlantı hatası: ' . $response['message']);
            }

            return [
                'success' => true,
                'message' => 'API bağlantısı başarılı.'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function siteExists($siteUrl) {
        $stmt = $this->db->prepare("
            SELECT id 
            FROM mph_wordpress_sites 
            WHERE site_url = ? AND user_id = ?
        ");
        
        $stmt->execute([$siteUrl, $this->userId]);
        return $stmt->rowCount() > 0;
    }

    private function validateSiteOwnership($siteId) {
        $stmt = $this->db->prepare("
            SELECT id 
            FROM mph_wordpress_sites 
            WHERE id = ? AND user_id = ?
        ");
        
        $stmt->execute([$siteId, $this->userId]);
        return $stmt->rowCount() > 0;
    }

    private function getRelatedPostCount($siteId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM mph_posts 
            WHERE site_id = ?
        ");
        
        $stmt->execute([$siteId]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    private function encryptSecret($secret) {
        // Güvenli şifreleme için OpenSSL kullan
        $key = getenv('ENCRYPTION_KEY');
        $cipher = "aes-256-gcm";
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $tag = "";
        
        $encrypted = openssl_encrypt(
            $secret,
            $cipher,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );
        
        return base64_encode($iv . $tag . $encrypted);
    }

    private function decryptSecret($encrypted) {
        $key = getenv('ENCRYPTION_KEY');
        $cipher = "aes-256-gcm";
        $encrypted = base64_decode($encrypted);
        
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = substr($encrypted, 0, $ivlen);
        $tag = substr($encrypted, $ivlen, 16);
        $ciphertext = substr($encrypted, $ivlen + 16);
        
        return openssl_decrypt(
            $ciphertext,
            $cipher,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );
    }
}