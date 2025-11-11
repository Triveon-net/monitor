#!/usr/bin/env php
<?php
/**
 * Metrics Collector
 * 
 * Sammelt Metriken von allen Node Exporters und speichert sie in der Datenbank
 * Sollte alle 30-60 Sekunden via Cron ausgeführt werden
 */

require_once __DIR__ . '/../resources/system/monitoring/UptimeMonitor.php';

$configPath = __DIR__ . '/../config/node_exporters.json';

if (!file_exists($configPath)) {
    die("Config file not found\n");
}

$config = json_decode(file_get_contents($configPath), true);
$monitor = new UptimeMonitor();

echo "[" . date('Y-m-d H:i:s') . "] Starting metrics collection...\n";

foreach ($config['instances'] as $instance) {
    if (!$instance['enabled']) {
        continue;
    }
    
    $nodeId = $instance['id'];
    $url = "http://{$instance['host']}:{$instance['port']}/metrics";
    
    echo "Collecting from {$nodeId} ({$instance['host']})... ";
    
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        
        $metricsText = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200 || !$metricsText) {
            echo "FAILED (HTTP $httpCode)\n";
            continue;
        }
        
        $metrics = parsePrometheusMetrics($metricsText);
        $saved = 0;
        
        // Speichere wichtige Metriken
        foreach ($metrics as $metric) {
            $name = $metric['name'];
            
            // Filter: Nur wichtige Metriken speichern
            if (shouldSaveMetric($name)) {
                $labels = isset($metric['labels']) ? json_encode($metric['labels']) : null;
                $monitor->recordMetric(
                    $nodeId,
                    getMetricType($name),
                    $name,
                    $metric['value'],
                    $labels
                );
                $saved++;
            }
        }
        
        echo "OK ($saved metrics saved)\n";
        
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
}

echo "[" . date('Y-m-d H:i:s') . "] Collection completed\n\n";

/**
 * Parse Prometheus metrics format
 */
function parsePrometheusMetrics($text) {
    $metrics = [];
    $lines = explode("\n", $text);
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        // Skip comments and empty lines
        if (empty($line) || $line[0] === '#') {
            continue;
        }
        
        // Parse metric line: metric_name{label="value"} 123.45
        if (preg_match('/^([a-zA-Z_:][a-zA-Z0-9_:]*)\{([^}]*)\}\s+([0-9.e+-]+)/', $line, $matches)) {
            // With labels
            $metrics[] = [
                'name' => $matches[1],
                'labels' => parseLabels($matches[2]),
                'value' => floatval($matches[3])
            ];
        } else if (preg_match('/^([a-zA-Z_:][a-zA-Z0-9_:]*)\s+([0-9.e+-]+)/', $line, $matches)) {
            // Without labels
            $metrics[] = [
                'name' => $matches[1],
                'labels' => [],
                'value' => floatval($matches[2])
            ];
        }
    }
    
    return $metrics;
}

/**
 * Parse Prometheus labels
 */
function parseLabels($labelStr) {
    $labels = [];
    if (preg_match_all('/([a-zA-Z_][a-zA-Z0-9_]*)="([^"]*)"/', $labelStr, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $labels[$match[1]] = $match[2];
        }
    }
    return $labels;
}

/**
 * Bestimme welche Metriken gespeichert werden sollen
 */
function shouldSaveMetric($name) {
    $importantMetrics = [
        'node_cpu_seconds_total',
        'node_memory_MemTotal_bytes',
        'node_memory_MemFree_bytes',
        'node_memory_MemAvailable_bytes',
        'node_memory_Buffers_bytes',
        'node_memory_Cached_bytes',
        'node_filesystem_size_bytes',
        'node_filesystem_avail_bytes',
        'node_filesystem_free_bytes',
        'node_network_receive_bytes_total',
        'node_network_transmit_bytes_total',
        'node_disk_read_bytes_total',
        'node_disk_written_bytes_total',
        'node_load1',
        'node_load5',
        'node_load15',
        'node_boot_time_seconds',
        'node_uname_info',
    ];
    
    foreach ($importantMetrics as $pattern) {
        if (strpos($name, $pattern) === 0) {
            return true;
        }
    }
    
    return false;
}

/**
 * Bestimme Metrik-Typ für Kategorisierung
 */
function getMetricType($name) {
    if (strpos($name, 'cpu') !== false) return 'cpu';
    if (strpos($name, 'memory') !== false || strpos($name, 'Mem') !== false) return 'memory';
    if (strpos($name, 'filesystem') !== false || strpos($name, 'disk') !== false) return 'disk';
    if (strpos($name, 'network') !== false) return 'network';
    if (strpos($name, 'load') !== false) return 'load';
    return 'system';
}
