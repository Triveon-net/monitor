#!/usr/bin/env php
<?php
/**
 * Database Cleanup Script
 * 
 * Löscht alte Daten aus der Monitoring-Datenbank
 * 
 * Verwendung:
 *   php cleanup_database.php [Tage]
 * 
 * Beispiel:
 *   php cleanup_database.php 30  # Löscht Daten älter als 30 Tage
 * 
 * Cron Setup (täglich um 3 Uhr nachts):
 *   0 3 * * * php /var/www/html/scripts/cleanup_database.php >> /var/log/monitoring_cleanup.log 2>&1
 */

require_once __DIR__ . '/../resources/system/database/Database.php';

// Parse arguments
$daysToKeep = isset($argv[1]) ? intval($argv[1]) : 30;

if ($daysToKeep < 1) {
    echo "[ERROR] Ungültige Anzahl Tage: $daysToKeep\n";
    exit(1);
}

echo "[" . date('Y-m-d H:i:s') . "] Starte Datenbank-Cleanup...\n";
echo "Behalte Daten der letzten $daysToKeep Tage\n\n";

try {
    $db = Database::getInstance();
    $config = $db->getConfig();
    
    echo "Datenbank-Typ: " . ($config['driver'] ?? 'sqlite') . "\n";
    
    // Führe Cleanup aus
    $result = $db->cleanup($daysToKeep);
    
    if ($result === false) {
        echo "[ERROR] Cleanup fehlgeschlagen\n";
        exit(1);
    }
    
    echo "\n[SUCCESS] Cleanup abgeschlossen:\n";
    echo "  - Uptime Checks gelöscht: " . $result['uptime_checks'] . "\n";
    echo "  - Metrics History gelöscht: " . $result['metrics_history'] . "\n";
    echo "  - Downtime Events gelöscht: " . $result['downtime_events'] . "\n";
    echo "  - Gesamt gelöscht: " . array_sum($result) . " Einträge\n";
    
    exit(0);
    
} catch (Exception $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    exit(1);
}
