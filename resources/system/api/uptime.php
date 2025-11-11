<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../monitoring/UptimeMonitor.php';

class UptimeAPI {
    private $monitor;
    private $configPath;
    private $uptimeConfigPath;
    
    public function __construct() {
        $this->monitor = new UptimeMonitor();
        $this->configPath = __DIR__ . '/../../../config/node_exporters.json';
        $this->uptimeConfigPath = __DIR__ . '/../../../config/uptime_config.json';
    }
    
    public function handleRequest() {
        $action = $_GET['action'] ?? '';
        
        switch ($action) {
            case 'status':
                return $this->getStatus();
            case 'history':
                return $this->getHistory();
            case 'downtime':
                return $this->getDowntime();
            case 'services':
                return $this->getServices();
            default:
                return $this->error('Invalid action');
        }
    }
    
    private function getStatus() {
        $nodeId = $_GET['node'] ?? null;
        
        if ($nodeId) {
            // Get status for specific node/service
            if (strpos($nodeId, 'service_') === 0) {
                $serviceId = substr($nodeId, 8);
                $service = $this->getServiceById($serviceId);
                
                if (!$service) {
                    return $this->error('Service not found');
                }
                
                $status = $this->monitor->getAllNodesStatus();
                $serviceStatus = $status[$nodeId] ?? null;
                
                if ($serviceStatus) {
                    $serviceStatus['name'] = $service['name'];
                    $serviceStatus['host'] = $service['host'];
                    $serviceStatus['port'] = $service['port'];
                    $serviceStatus['protocol'] = $service['protocol'];
                }
                
                return $this->success($serviceStatus);
            } else {
                // Node exporter status
                $status = $this->monitor->getAllNodesStatus();
                return $this->success($status[$nodeId] ?? null);
            }
        } else {
            // Get all statuses
            return $this->success($this->monitor->getAllNodesStatus());
        }
    }
    
    private function getHistory() {
        $nodeId = $_GET['node'] ?? null;
        $hours = intval($_GET['hours'] ?? 24);
        
        if (!$nodeId) {
            return $this->error('Node ID required');
        }
        
        $history = $this->monitor->getUptimeHistory($nodeId, $hours);
        return $this->success($history);
    }
    
    private function getDowntime() {
        $nodeId = $_GET['node'] ?? null;
        $limit = intval($_GET['limit'] ?? 10);
        
        if (!$nodeId) {
            return $this->error('Node ID required');
        }
        
        $downtime = $this->monitor->getDowntimeEvents($nodeId, $limit);
        return $this->success($downtime);
    }
    
    private function getServices() {
        $uptimeConfig = $this->loadUptimeConfig();
        $services = $uptimeConfig['services'] ?? [];
        
        // Add status to each service
        $allStatus = $this->monitor->getAllNodesStatus();
        
        foreach ($services as &$service) {
            $recordId = "service_{$service['id']}";
            $service['status'] = $allStatus[$recordId] ?? [
                'current_status' => 'unknown',
                'last_check' => null,
                'uptime_24h' => null,
                'uptime_7d' => null,
                'uptime_30d' => null
            ];
        }
        
        return $this->success($services);
    }
    
    private function getServiceById($serviceId) {
        $uptimeConfig = $this->loadUptimeConfig();
        $services = $uptimeConfig['services'] ?? [];
        
        foreach ($services as $service) {
            if ($service['id'] === $serviceId) {
                return $service;
            }
        }
        
        return null;
    }
    
    private function loadUptimeConfig() {
        if (!file_exists($this->uptimeConfigPath)) {
            return [];
        }
        
        return json_decode(file_get_contents($this->uptimeConfigPath), true) ?: [];
    }
    
    private function success($data) {
        return json_encode(['success' => true, 'data' => $data]);
    }
    
    private function error($message) {
        return json_encode(['success' => false, 'error' => $message]);
    }
}

$api = new UptimeAPI();
echo $api->handleRequest();
