<?php
/**
 * Database Configuration
 * 
 * Ändere diese Werte um MySQL statt SQLite zu verwenden:
 * 
 * MySQL Beispiel:
 * 'driver' => 'mysql',
 * 'host' => 'localhost',
 * 'port' => 3306,
 * 'database' => 'monitoring',
 * 'username' => 'root',
 * 'password' => 'dein_passwort',
 * 'charset' => 'utf8mb4'
 */

return [
    // SQLite (Standard)
    // 'driver' => 'sqlite',
    // 'database' => __DIR__ . '/monitoring.db',
    
    // MySQL (auskommentiert)
    'driver' => 'mysql',
    'host' => '192.168.189.206',
    'port' => 3002,
    'database' => 'monitoring',
    'username' => 'monitor',
    'password' => 'DQ6A14geNbeM',
    'charset' => 'utf8mb4',
    
    // Cleanup Settings
    'retention_days' => 30, // Daten älter als X Tage werden automatisch gelöscht
    'auto_cleanup' => true, // Automatisches Cleanup bei jedem Init
];
