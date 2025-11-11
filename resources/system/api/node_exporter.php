<?php
header('Content-Type: application/json');

class NodeExporterAPI {
    
    private $configFile = __DIR__ . '/../../../config/node_exporters.json';
    
    public function getInstances() {
        if (!file_exists($this->configFile)) {
            return $this->error('Configuration file not found');
        }
        
        $config = json_decode(file_get_contents($this->configFile), true);
        if (!$config || !isset($config['instances'])) {
            return $this->error('Invalid configuration');
        }
        
        return $this->success($config['instances']);
    }
    
    public function getMetrics($nodeId) {
        $instances = $this->getInstancesArray();
        $instance = null;
        
        foreach ($instances as $inst) {
            if ($inst['id'] === $nodeId) {
                $instance = $inst;
                break;
            }
        }
        
        if (!$instance) {
            return $this->error('Instance not found: ' . $nodeId);
        }
        
        if (!isset($instance['enabled']) || !$instance['enabled']) {
            return $this->error('Instance is disabled');
        }
        
        $url = "http://{$instance['host']}:{$instance['port']}/metrics";
        $metrics = $this->fetchMetrics($url);
        
        if ($metrics === false) {
            return $this->error("Failed to fetch metrics from {$url}. Check if Node Exporter is running and accessible.");
        }
        
        if (empty($metrics)) {
            return $this->error('Node Exporter returned empty response');
        }
        
        $parsed = $this->parseMetrics($metrics);
        
        // Debug: Log metric counts
        error_log("Parsed metrics - CPU: " . count($parsed['cpu']) . 
                  ", Memory: " . count($parsed['memory']) . 
                  ", Disk: " . count($parsed['disk']) . 
                  ", Network: " . count($parsed['network']));
        
        return $this->success([
            'instance' => $instance,
            'cpu' => $parsed['cpu'],
            'memory' => $parsed['memory'],
            'disk' => $parsed['disk'],
            'network' => $parsed['network'],
            'system' => $parsed['system'],
            'timestamp' => time()
        ]);
    }
    
    private function fetchMetrics($url) {
        if (!function_exists('curl_init')) {
            error_log("CURL extension is not installed");
            return false;
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: text/plain']);
        
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $errno = curl_errno($ch);
        curl_close($ch);
        
        if ($errno !== 0) {
            error_log("CURL error ($errno): $error when fetching $url");
            return false;
        }
        
        if ($httpCode !== 200) {
            error_log("HTTP error: Got status code $httpCode from $url");
            return false;
        }
        
        if (empty($result)) {
            error_log("Empty response from $url");
            return false;
        }
        
        error_log("Successfully fetched " . strlen($result) . " bytes from $url");
        return $result;
    }
    
    private function parseMetrics($rawMetrics) {
        $lines = explode("\n", $rawMetrics);
        $metrics = [
            'cpu' => [],
            'memory' => [],
            'disk' => [],
            'network' => [],
            'system' => []
        ];
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Skip empty lines and comments
            if (empty($line) || $line[0] === '#') {
                continue;
            }
            
            // Parse metric line: metric_name{labels} value [timestamp]
            // Examples:
            // node_cpu_seconds_total{cpu="0",mode="idle"} 129445.44881889764
            // node_load1 0.1201171875
            
            $parts = preg_split('/\s+/', $line, 2);
            if (count($parts) < 2) {
                continue;
            }
            
            $metricPart = $parts[0];
            $valuePart = trim($parts[1]);
            
            // Extract value (ignore optional timestamp)
            $valueMatch = preg_split('/\s+/', $valuePart);
            $value = floatval($valueMatch[0]);
            
            // Parse metric name and labels
            $name = '';
            $labels = '';
            
            if (preg_match('/^([a-zA-Z_:][a-zA-Z0-9_:]*)(\{([^}]+)\})?$/', $metricPart, $matches)) {
                $name = $matches[1];
                $labels = isset($matches[3]) ? $matches[3] : '';
            } else {
                continue;
            }
            
            // Create unique key for metrics with labels
            $key = $labels ? $name . '{' . $labels . '}' : $name;
            
            // Categorize metrics
            if (strpos($name, 'node_cpu') !== false || 
                strpos($name, 'node_load') !== false) {
                $metrics['cpu'][$key] = ['value' => $value, 'labels' => $labels, 'name' => $name];
            } elseif (strpos($name, 'node_memory') !== false || 
                      strpos($name, 'go_memstats') !== false) {
                $metrics['memory'][$key] = ['value' => $value, 'labels' => $labels, 'name' => $name];
            } elseif (strpos($name, 'node_filesystem') !== false || 
                      strpos($name, 'node_disk') !== false ||
                      strpos($name, 'node_devstat') !== false) {
                $metrics['disk'][$key] = ['value' => $value, 'labels' => $labels, 'name' => $name];
            } elseif (strpos($name, 'node_network') !== false) {
                $metrics['network'][$key] = ['value' => $value, 'labels' => $labels, 'name' => $name];
            } else {
                $metrics['system'][$key] = ['value' => $value, 'labels' => $labels, 'name' => $name];
            }
        }
        
        return $metrics;
    }
    
    private function getInstancesArray() {
        if (!file_exists($this->configFile)) {
            return [];
        }
        
        $config = json_decode(file_get_contents($this->configFile), true);
        return $config['instances'] ?? [];
    }
    
    public function success($data) {
        return json_encode(['success' => true, 'data' => $data]);
    }
    
    public function error($message) {
        return json_encode(['success' => false, 'error' => $message]);
    }
}

// Handle API requests
$api = new NodeExporterAPI();
$action = $_GET['action'] ?? 'instances';

switch ($action) {
    case 'instances':
        echo $api->getInstances();
        break;
    case 'metrics':
        $nodeId = $_GET['node'] ?? null;
        if (!$nodeId) {
            echo $api->error('Node ID is required');
        } else {
            echo $api->getMetrics($nodeId);
        }
        break;
    case 'history':
        require_once __DIR__ . '/../monitoring/UptimeMonitor.php';
        $nodeId = $_GET['node'] ?? null;
        $metricType = $_GET['type'] ?? null;
        $metricName = $_GET['name'] ?? null;
        $hours = intval($_GET['hours'] ?? 24);
        
        if (!$nodeId || !$metricType || !$metricName) {
            echo $api->error('Node ID, metric type and name are required');
        } else {
            $monitor = new UptimeMonitor();
            $history = $monitor->getMetricHistory($nodeId, $metricType, $metricName, $hours);
            echo $api->success($history);
        }
        break;
    case 'aggregated':
        require_once __DIR__ . '/../monitoring/UptimeMonitor.php';
        $nodeId = $_GET['node'] ?? null;
        $metricType = $_GET['type'] ?? null;
        $hours = intval($_GET['hours'] ?? 24);
        $bucketSize = intval($_GET['bucket'] ?? 300);
        
        if (!$nodeId || !$metricType) {
            echo $api->error('Node ID and metric type are required');
        } else {
            $monitor = new UptimeMonitor();
            $aggregated = $monitor->getAggregatedMetrics($nodeId, $metricType, $hours, $bucketSize);
            echo $api->success($aggregated);
        }
        break;
    default:
        echo $api->error('Invalid action');
}
