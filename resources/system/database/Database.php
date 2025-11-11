<?php

class Database {
    private static $instance = null;
    private $pdo;
    private $config;
    
    private function __construct() {
        $this->config = require __DIR__ . '/config.php';
        
        try {
            $this->connect();
            $this->initializeTables();
            
            // Automatisches Cleanup wenn aktiviert
            if ($this->config['auto_cleanup'] ?? true) {
                $this->cleanup($this->config['retention_days'] ?? 30);
            }
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function connect() {
        $driver = $this->config['driver'] ?? 'sqlite';
        
        if ($driver === 'mysql') {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $this->config['host'] ?? 'localhost',
                $this->config['port'] ?? 3306,
                $this->config['database'] ?? 'monitoring',
                $this->config['charset'] ?? 'utf8mb4'
            );
            
            $this->pdo = new PDO(
                $dsn,
                $this->config['username'] ?? 'root',
                $this->config['password'] ?? '',
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } else {
            // SQLite (Standard)
            $dbPath = $this->config['database'] ?? __DIR__ . '/monitoring.db';
            $this->pdo = new PDO('sqlite:' . $dbPath);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    private function initializeTables() {
        $driver = $this->config['driver'] ?? 'sqlite';
        
        if ($driver === 'mysql') {
            // MySQL Tabellen
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS uptime_checks (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    node_id VARCHAR(255) NOT NULL,
                    timestamp BIGINT UNSIGNED NOT NULL,
                    status VARCHAR(50) NOT NULL,
                    response_time DECIMAL(10,2) NULL,
                    error_message TEXT NULL,
                    INDEX idx_node_timestamp (node_id, timestamp)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS metrics_history (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    node_id VARCHAR(255) NOT NULL,
                    timestamp BIGINT UNSIGNED NOT NULL,
                    metric_type VARCHAR(100) NOT NULL,
                    metric_name VARCHAR(255) NOT NULL,
                    value DECIMAL(20,6) NOT NULL,
                    labels JSON NULL,
                    INDEX idx_node_metric (node_id, metric_type, timestamp)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS downtime_events (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    node_id VARCHAR(255) NOT NULL,
                    start_time BIGINT UNSIGNED NOT NULL,
                    end_time BIGINT UNSIGNED NULL,
                    duration BIGINT UNSIGNED NULL,
                    error_message TEXT NULL,
                    INDEX idx_node_downtime (node_id, start_time)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } else {
            // SQLite Tabellen
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS uptime_checks (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    node_id TEXT NOT NULL,
                    timestamp INTEGER NOT NULL,
                    status TEXT NOT NULL,
                    response_time REAL,
                    error_message TEXT
                )
            ");
            
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS metrics_history (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    node_id TEXT NOT NULL,
                    timestamp INTEGER NOT NULL,
                    metric_type TEXT NOT NULL,
                    metric_name TEXT NOT NULL,
                    value REAL NOT NULL,
                    labels TEXT
                )
            ");
            
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS downtime_events (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    node_id TEXT NOT NULL,
                    start_time INTEGER NOT NULL,
                    end_time INTEGER,
                    duration INTEGER,
                    error_message TEXT
                )
            ");
            
            // SQLite Indexes
            $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_uptime_node_timestamp ON uptime_checks(node_id, timestamp)");
            $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_metrics_node_metric ON metrics_history(node_id, metric_type, timestamp)");
            $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_downtime_node ON downtime_events(node_id, start_time)");
        }
    }
    
    public function cleanup($daysToKeep = 30) {
        $cutoffTimestamp = time() - ($daysToKeep * 86400);
        
        try {
            $stmt1 = $this->pdo->prepare("DELETE FROM uptime_checks WHERE timestamp < ?");
            $stmt1->execute([$cutoffTimestamp]);
            $deleted1 = $stmt1->rowCount();
            
            $stmt2 = $this->pdo->prepare("DELETE FROM metrics_history WHERE timestamp < ?");
            $stmt2->execute([$cutoffTimestamp]);
            $deleted2 = $stmt2->rowCount();
            
            $stmt3 = $this->pdo->prepare("DELETE FROM downtime_events WHERE start_time < ? AND end_time IS NOT NULL");
            $stmt3->execute([$cutoffTimestamp]);
            $deleted3 = $stmt3->rowCount();
            
            error_log("Database cleanup completed: Deleted $deleted1 uptime checks, $deleted2 metrics, $deleted3 downtime events older than $daysToKeep days");
            
            return [
                'uptime_checks' => $deleted1,
                'metrics_history' => $deleted2,
                'downtime_events' => $deleted3
            ];
        } catch (PDOException $e) {
            error_log("Database cleanup failed: " . $e->getMessage());
            return false;
        }
    }
    
    public function getConfig() {
        return $this->config;
    }
}
