#!/usr/bin/env php
<?php

require_once __DIR__ . '/../resources/system/monitoring/UptimeMonitor.php';

$uptimeConfigPath = __DIR__ . '/../config/uptime_config.json';

if (!file_exists($uptimeConfigPath)) {
    die("Uptime config file not found\n");
}

$uptimeConfig = json_decode(file_get_contents($uptimeConfigPath), true);

// Default values
$pingTimeout = $uptimeConfig['ping_timeout'] ?? 2;
$tcpTimeout = $uptimeConfig['tcp_timeout'] ?? 2;
$icmpCount = $uptimeConfig['icmp_count'] ?? 1;
$enableIcmp = $uptimeConfig['enable_icmp'] ?? true;
$enableTcp = $uptimeConfig['enable_tcp'] ?? true;

$monitor = new UptimeMonitor();

echo "[" . date('Y-m-d H:i:s') . "] Starting uptime check...\n";

// Check custom services
if (isset($uptimeConfig['services']) && is_array($uptimeConfig['services'])) {
    echo "\n=== Checking Custom Services ===\n";
    
    foreach ($uptimeConfig['services'] as $service) {
        if (!isset($service['enabled']) || !$service['enabled']) {
            continue;
        }
        
        $serviceId = $service['id'];
        $serviceName = $service['name'];
        $host = $service['host'];
        $port = $service['port'];
        $protocol = strtolower($service['protocol'] ?? 'tcp');
        
        echo "Checking {$serviceName} ({$host}:{$port}/{$protocol})... ";
        
        $isUp = false;
        $responseTime = null;
        $errorMsg = null;
        
        if ($protocol === 'icmp') {
            // ICMP only check
            $startTime = microtime(true);
            exec("ping -c {$icmpCount} -W {$pingTimeout} {$host} 2>&1", $output, $returnCode);
            $responseTime = (microtime(true) - $startTime) * 1000;
            
            if ($returnCode === 0) {
                $isUp = true;
                if (preg_match('/time=([0-9.]+)\s*ms/', implode("\n", $output), $matches)) {
                    $responseTime = floatval($matches[1]);
                }
                echo "UP (ICMP: {$responseTime}ms)\n";
            } else {
                $errorMsg = "ICMP ping failed";
                echo "DOWN ($errorMsg)\n";
            }
            
        } else if ($protocol === 'tcp') {
            // TCP port check
            $startTime = microtime(true);
            $connection = @fsockopen($host, $port, $errno, $errstr, $tcpTimeout);
            $responseTime = (microtime(true) - $startTime) * 1000;
            
            if ($connection) {
                fclose($connection);
                $isUp = true;
                echo "UP (TCP: {$responseTime}ms)\n";
            } else {
                $errorMsg = "TCP port {$port} closed: {$errstr}";
                echo "DOWN ($errorMsg)\n";
            }
            
        } else if ($protocol === 'udp') {
            // UDP port check (basic - just checks if port accepts packets)
            $startTime = microtime(true);
            $socket = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
            
            if ($socket) {
                socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => $tcpTimeout, 'usec' => 0]);
                socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, ['sec' => $tcpTimeout, 'usec' => 0]);
                
                // Send a dummy packet
                $sent = @socket_sendto($socket, "\x00", 1, 0, $host, $port);
                
                if ($sent !== false) {
                    // For UDP we assume success if we can send
                    // (no easy way to verify without protocol-specific responses)
                    $responseTime = (microtime(true) - $startTime) * 1000;
                    $isUp = true;
                    echo "UP (UDP: {$responseTime}ms - packet sent)\n";
                } else {
                    $errorMsg = "UDP packet send failed";
                    echo "DOWN ($errorMsg)\n";
                }
                
                socket_close($socket);
            } else {
                $errorMsg = "Could not create UDP socket";
                echo "DOWN ($errorMsg)\n";
            }
        }
        
        // Record check result with service ID
        $recordId = "service_{$serviceId}";
        if ($isUp) {
            $monitor->recordCheck($recordId, 'up', $responseTime);
        } else {
            $monitor->recordCheck($recordId, 'down', null, $errorMsg);
        }
    }
}

echo "\n[" . date('Y-m-d H:i:s') . "] Check completed\n\n";
