#!/usr/bin/env php
<?php
/**
 * Database Migration Script
 * Fügt labels Spalte zur metrics_history Tabelle hinzu
 */

require_once __DIR__ . '/../resources/system/database/Database.php';

echo "[" . date('Y-m-d H:i:s') . "] Starting database migration...\n";

try {
    $db = Database::getInstance()->getConnection();
    $config = require __DIR__ . '/../resources/system/database/config.php';
    $driver = $config['driver'] ?? 'sqlite';
    
    if ($driver === 'mysql') {
        echo "Adding 'labels' column to metrics_history table (MySQL)...\n";
        
        // Prüfe ob Spalte schon existiert
        $stmt = $db->query("SHOW COLUMNS FROM metrics_history LIKE 'labels'");
        if ($stmt->rowCount() > 0) {
            echo "Column 'labels' already exists. Skipping.\n";
        } else {
            $db->exec("ALTER TABLE metrics_history ADD COLUMN labels JSON NULL AFTER value");
            echo "Column 'labels' added successfully.\n";
        }
    } else {
        echo "Adding 'labels' column to metrics_history table (SQLite)...\n";
        
        // SQLite prüfen
        $stmt = $db->query("PRAGMA table_info(metrics_history)");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $hasLabels = false;
        
        foreach ($columns as $col) {
            if ($col['name'] === 'labels') {
                $hasLabels = true;
                break;
            }
        }
        
        if ($hasLabels) {
            echo "Column 'labels' already exists. Skipping.\n";
        } else {
            $db->exec("ALTER TABLE metrics_history ADD COLUMN labels TEXT");
            echo "Column 'labels' added successfully.\n";
        }
    }
    
    echo "[" . date('Y-m-d H:i:s') . "] Migration completed successfully.\n";
    exit(0);
    
} catch (Exception $e) {
    echo "[ERROR] Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
