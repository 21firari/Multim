<?php
class WordPressApiClient {
    private $apiUrl;
    private $consumerKey;
    private $consumerSecret;
    private $lastError;

    public function __construct($apiUrl, $consumerKey, $consumerSecret) {
        $this->apiUrl = rtrim($apiUrl, '/');
        $this->consumerKey = $consumerKey;
        $this->consumerSecret = $consumerSecret;
    }

    public function testConnection() {
        try {
            // WordPress REST API info endpoint'ini kontrol et
            $response = $this->makeRequest('/');

            if (!isset($response['name']) || !isset($response['namespaces'])) {
                throw new Exception('Geçersiz WordPress REST API yanıtı.');
            }

            // Authentication kontrolü
            $postsResponse = $this->makeRequest('/wp/v2/posts', ['per_page' => 1]);
            
            if (!is_array($postsResponse)) {
                throw new Exception('API erişim yetkisi yok.');
            }

            return [
                'success' => true,
                'message' => 'Bağlantı başarılı',
                'site_info' => [
                    'name' => $response['name'],
                    'description' => $response['description'] ?? '',
                    'url' => $response['url'] ?? '',
                    'version' => $response['namespaces'] ?? []
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Bağlantı hatası: ' . $e->getMessage()
            ];
        }
    }

    public function createPost($data) {
        try {
            $endpoint = '/wp/v2/posts';
            $params = [
                'title' => $data['title'],
                'content' => $data['content'],
                'status' => $data['status'] ?? 'draft',
                'categories' => $data['categories'] ?? [],
                'tags' => $data['tags'] ?? []
            ];

            if (isset($data['featured_media'])) {
                $params['featured_media'] = $data['featured_media'];
            }

            $response = $this->makeRequest($endpoint, $params, 'POST');

            if (!isset($response['id'])) {
                throw new Exception('İçerik oluşturulamadı.');
            }

            return [
                'success' => true,
                'message' => 'İçerik başarıyla oluşturuldu',
                'post' => $response
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function uploadMedia($file) {
        try {
            $endpoint = '/wp/v2/media';
            
            // Dosya kontrolü
            if (!file_exists($file['tmp_name'])) {
                throw new Exception('Dosya yüklenemedi.');
            }

            // MIME type kontrolü
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($file['type'], $allowedTypes)) {
                throw new Exception('Desteklenmeyen dosya türü.');
            }

            // Dosya içeriğini oku
            $fileContent = file_get_contents($file['tmp_name']);
            
            $headers = [
                'Content-Disposition' => 'attachment; filename="' . basename($file['name']) . '"',
                'Content-Type' => $file['type']
            ];

            $response = $this->makeRequest($endpoint, $fileContent, 'POST', $headers);

            if (!isset($response['id'])) {
                throw new Exception('Medya yüklenemedi.');
            }

            return [
                'success' => true,
                'message' => 'Medya başarıyla yüklendi',
                'media' => $response
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getCategories() {
        try {
            $endpoint = '/wp/v2/categories';
            $params = ['per_page' => 100];

            $response = $this->makeRequest($endpoint, $params);

            return [
                'success' => true,
                'categories' => $response
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getTags() {
        try {
            $endpoint = '/wp/v2/tags';
            $params = ['per_page' => 100];

            $response = $this->makeRequest($endpoint, $params);

            return [
                'success' => true,
                'tags' => $response
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function makeRequest($endpoint, $data = null, $method = 'GET', $additionalHeaders = []) {
        $url = $this->apiUrl . $endpoint;
        
        $headers = array_merge([
            'Authorization' => 'Basic ' . base64_encode($this->consumerKey . ':' . $this->consumerSecret)
        ], $additionalHeaders);

        $ch = curl_init();
        
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => $this->formatHeaders($headers)
        ];

        if ($method === 'POST') {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = is_string($data) ? $data : json_encode($data);
        } elseif ($method === 'GET' && !empty($data)) {
            $options[CURLOPT_URL] .= '?' . http_build_query($data);
        }

        curl_setopt_array($ch, $options);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $this->lastError = curl_error($ch);
            curl_close($ch);
            throw new Exception($this->lastError);
        }
        
        curl_close($ch);

        if ($httpCode >= 400) {
            $error = json_decode($response, true);
            throw new Exception($error['message'] ?? 'API hatası: ' . $httpCode);
        }

        return json_decode($response, true);
    }

    private function formatHeaders($headers) {
        $formatted = [];
        foreach ($headers as $key => $value) {
            $formatted[] = $key . ': ' . $value;
        }
        return $formatted;
    }

    public function getLastError() {
        return $this->lastError;
    }
}