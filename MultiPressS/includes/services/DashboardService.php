<?php
class DashboardService {
    private $db;
    private $userId;

    public function __construct($userId) {
        $this->db = Database::getInstance()->getConnection();
        $this->userId = $userId;
    }

    public function getStats() {
        try {
            // WordPress siteleri sayısı
            $sitesCount = $this->getSitesCount();
            
            // Son 30 günlük içerik sayısı
            $postsCount = $this->getRecentPostsCount();
            
            // Toplam görüntülenme
            $viewsCount = $this->getTotalViews();
            
            // Aktif abonelik bilgisi
            $subscription = $this->getActiveSubscription();
            
            // Son aktiviteler
            $recentActivities = $this->getRecentActivities();
            
            // Popüler içerikler
            $popularPosts = $this->getPopularPosts();

            return [
                'success' => true,
                'data' => [
                    'sites_count' => $sitesCount,
                    'posts_count' => $postsCount,
                    'views_count' => $viewsCount,
                    'subscription' => $subscription,
                    'recent_activities' => $recentActivities,
                    'popular_posts' => $popularPosts
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function getSitesCount() {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM mph_wordpress_sites 
            WHERE user_id = ? AND status = 'active'
        ");
        
        $stmt->execute([$this->userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    private function getRecentPostsCount() {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM mph_posts 
            WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        
        $stmt->execute([$this->userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    private function getTotalViews() {
        $stmt = $this->db->prepare("
            SELECT SUM(view_count) as total 
            FROM mph_posts 
            WHERE user_id = ?
        ");
        
        $stmt->execute([$this->userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    }

    private function getActiveSubscription() {
        $stmt = $this->db->prepare("
            SELECT s.*, p.name as package_name, p.features 
            FROM mph_subscriptions s 
            JOIN mph_packages p ON s.package_id = p.id 
            WHERE s.user_id = ? AND s.status = 'active' 
            AND s.end_date > NOW() 
            ORDER BY s.created_at DESC 
            LIMIT 1
        ");
        
        $stmt->execute([$this->userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getRecentActivities() {
        $stmt = $this->db->prepare("
            SELECT 
                'post' as type,
                id,
                title as description,
                created_at as date
            FROM mph_posts 
            WHERE user_id = ?
            
            UNION ALL
            
            SELECT 
                'site' as type,
                id,
                CONCAT('Site eklendi: ', site_url) as description,
                created_at as date
            FROM mph_wordpress_sites 
            WHERE user_id = ?
            
            ORDER BY date DESC 
            LIMIT 10
        ");
        
        $stmt->execute([$this->userId, $this->userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getPopularPosts() {
        $stmt = $this->db->prepare("
            SELECT 
                p.*,
                w.site_url,
                w.site_name
            FROM mph_posts p 
            JOIN mph_wordpress_sites w ON p.site_id = w.id 
            WHERE p.user_id = ? 
            ORDER BY p.view_count DESC 
            LIMIT 5
        ");
        
        $stmt->execute([$this->userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}