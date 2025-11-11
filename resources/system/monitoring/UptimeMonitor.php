<?php

require_once __DIR__ . '/../database/Database.php';

class UptimeMonitor {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Record uptime check result
     */
    public function recordCheck($nodeId, $status, $responseTime = null, $errorMessage = null) {
        $stmt = $this->db->prepare("
            INSERT INTO uptime_checks (node_id, timestamp, status, response_time, error_message)
            VALUES (:node_id, :timestamp, :status, :response_time, :error_message)
        ");
        
        $stmt->execute([
            'node_id' => $nodeId,
            'timestamp' => time(),
            'status' => $status,
            'response_time' => $responseTime,
            'error_message' => $errorMessage
        ]);
        
        // Check if we need to create or close a downtime event
        if ($status === 'down') {
            $this->startDowntimeEvent($nodeId, $errorMessage);
        } else {
            $this->endDowntimeEvent($nodeId);
        }
    }
    
    /**
     * Start a downtime event
     */
    private function startDowntimeEvent($nodeId, $errorMessage = null) {
        // Check if there's already an active downtime
        $stmt = $this->db->prepare("
            SELECT id FROM downtime_events 
            WHERE node_id = :node_id AND end_time IS NULL
            LIMIT 1
        ");
        $stmt->execute(['node_id' => $nodeId]);
        
        if ($stmt->fetch()) {
            return; // Already in downtime
        }
        
        // Create new downtime event
        $stmt = $this->db->prepare("
            INSERT INTO downtime_events (node_id, start_time, error_message)
            VALUES (:node_id, :start_time, :error_message)
        ");
        
        $stmt->execute([
            'node_id' => $nodeId,
            'start_time' => time(),
            'error_message' => $errorMessage
        ]);
    }
    
    /**
     * End a downtime event
     */
    private function endDowntimeEvent($nodeId) {
        $stmt = $this->db->prepare("
            SELECT id, start_time FROM downtime_events 
            WHERE node_id = :node_id AND end_time IS NULL
            LIMIT 1
        ");
        $stmt->execute(['node_id' => $nodeId]);
        $downtime = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($downtime) {
            $endTime = time();
            $duration = $endTime - $downtime['start_time'];
            
            $stmt = $this->db->prepare("
                UPDATE downtime_events 
                SET end_time = :end_time, duration = :duration
                WHERE id = :id
            ");
            
            $stmt->execute([
                'end_time' => $endTime,
                'duration' => $duration,
                'id' => $downtime['id']
            ]);
        }
    }
    
    /**
     * Get uptime percentage for a node
     */
    public function getUptimePercentage($nodeId, $hours = 24) {
        $startTime = time() - ($hours * 3600);
        
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_checks,
                SUM(CASE WHEN status = 'up' THEN 1 ELSE 0 END) as up_checks
            FROM uptime_checks
            WHERE node_id = :node_id AND timestamp >= :start_time
        ");
        
        $stmt->execute([
            'node_id' => $nodeId,
            'start_time' => $startTime
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['total_checks'] == 0) {
            return null;
        }
        
        return ($result['up_checks'] / $result['total_checks']) * 100;
    }
    
    /**
     * Get uptime history for charts
     */
    public function getUptimeHistory($nodeId, $hours = 24) {
        $startTime = time() - ($hours * 3600);
        
        $stmt = $this->db->prepare("
            SELECT timestamp, status, response_time
            FROM uptime_checks
            WHERE node_id = :node_id AND timestamp >= :start_time
            ORDER BY timestamp ASC
        ");
        
        $stmt->execute([
            'node_id' => $nodeId,
            'start_time' => $startTime
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get recent downtime events
     */
    public function getDowntimeEvents($nodeId, $limit = 10) {
        $stmt = $this->db->prepare("
            SELECT start_time, end_time, duration, error_message
            FROM downtime_events
            WHERE node_id = :node_id
            ORDER BY start_time DESC
            LIMIT :limit
        ");
        
        $stmt->bindValue('node_id', $nodeId);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get all nodes status
     */
    public function getAllNodesStatus() {
        $stmt = $this->db->query("
            SELECT DISTINCT node_id FROM uptime_checks
        ");
        
        $nodes = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $status = [];
        
        foreach ($nodes as $nodeId) {
            // Get latest check
            $stmt = $this->db->prepare("
                SELECT status, timestamp, response_time
                FROM uptime_checks
                WHERE node_id = :node_id
                ORDER BY timestamp DESC
                LIMIT 1
            ");
            $stmt->execute(['node_id' => $nodeId]);
            $latestCheck = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Get uptime percentages
            $uptime24h = $this->getUptimePercentage($nodeId, 24);
            $uptime7d = $this->getUptimePercentage($nodeId, 24 * 7);
            $uptime30d = $this->getUptimePercentage($nodeId, 24 * 30);
            
            $status[$nodeId] = [
                'current_status' => $latestCheck['status'],
                'last_check' => $latestCheck['timestamp'],
                'response_time' => $latestCheck['response_time'],
                'uptime_24h' => $uptime24h,
                'uptime_7d' => $uptime7d,
                'uptime_30d' => $uptime30d
            ];
        }
        
        return $status;
    }
    
    /**
     * Record metric value for historical data
     */
    public function recordMetric($nodeId, $metricType, $metricName, $value, $labels = null) {
        $stmt = $this->db->prepare("
            INSERT INTO metrics_history (node_id, timestamp, metric_type, metric_name, value, labels)
            VALUES (:node_id, :timestamp, :metric_type, :metric_name, :value, :labels)
        ");
        
        $stmt->execute([
            'node_id' => $nodeId,
            'timestamp' => time(),
            'metric_type' => $metricType,
            'metric_name' => $metricName,
            'value' => $value,
            'labels' => $labels
        ]);
    }
    
    /**
     * Get historical metrics for charts
     */
    public function getMetricHistory($nodeId, $metricType, $metricName, $hours = 24, $labelFilter = null) {
        $startTime = time() - ($hours * 3600);
        
        $query = "SELECT timestamp, value, labels
            FROM metrics_history
            WHERE node_id = :node_id 
              AND metric_type = :metric_type 
              AND metric_name = :metric_name
              AND timestamp >= :start_time";
        
        $params = [
            'node_id' => $nodeId,
            'metric_type' => $metricType,
            'metric_name' => $metricName,
            'start_time' => $startTime
        ];
        
        if ($labelFilter) {
            $query .= " AND labels = :labels";
            $params['labels'] = $labelFilter;
        }
        
        $query .= " ORDER BY timestamp ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get aggregated metrics for time range selector
     */
    public function getAggregatedMetrics($nodeId, $metricType, $hours = 24, $bucketSize = 300) {
        $startTime = time() - ($hours * 3600);
        
        // MySQL benötigt bucket_size als direkten Wert, nicht als Parameter im FLOOR
        $stmt = $this->db->prepare("
            SELECT 
                FLOOR(timestamp / " . intval($bucketSize) . ") * " . intval($bucketSize) . " as bucket_time,
                metric_name,
                AVG(value) as avg_value,
                MIN(value) as min_value,
                MAX(value) as max_value,
                COUNT(*) as sample_count
            FROM metrics_history
            WHERE node_id = :node_id 
              AND metric_type = :metric_type 
              AND timestamp >= :start_time
            GROUP BY bucket_time, metric_name
            ORDER BY bucket_time ASC
        ");
        
        $stmt->execute([
            'node_id' => $nodeId,
            'metric_type' => $metricType,
            'start_time' => $startTime
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

