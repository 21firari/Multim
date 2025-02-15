<?php
class Database {
    private static $instance = null;
    private $connection;
    private $prefix;

    private function __construct() {
        $config = require_once __DIR__ . '/../config/database.php';
        $this->connect($config);
        $this->prefix = $config['prefix'];
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function connect($config) {
        try {
            $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
            $this->connection = new PDO($dsn, $config['username'], $config['password']);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw new Exception("Veritabanına bağlanılamadı. Lütfen daha sonra tekrar deneyiniz.");
        }
    }

    public function getConnection() {
        return $this->connection;
    }

    public function getPrefix() {
        return $this->prefix;
    }

    public function getTable($table) {
        return $this->prefix . $table;
    }
}