<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Configuration - Docs</title>
    <link rel="stylesheet" href="/public/dashboard.css">
    <link rel="stylesheet" href="/public/docs.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include __DIR__ . '/../../components/docs-sidebar.php'; ?>

    <div class="docs-content">
        <div class="docs-header">
            <h1><i class="fas fa-database"></i> Database Configuration</h1>
            <p>Konfiguriere MySQL oder SQLite für Uptime & Metrics History.</p>
        </div>

        <div class="docs-section">
            <h2>Config File Location</h2>
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Pfad</h4>
                <p><strong>File:</strong> <code>/var/www/html/resources/system/database/config.php</code></p>
            </div>
        </div>

        <div class="docs-section">
            <h2>SQLite Setup (Empfohlen für Einsteiger)</h2>
            <p>Einfach und ohne externe Datenbank.</p>
            
            <div class="code-block">
                <pre>&lt;?php

return [
    'driver' => 'sqlite',
    'database' => __DIR__ . '/monitoring.db',
    'charset' => 'utf8mb4',
    'retention_days' => 30,
    'auto_cleanup' => true
];</pre>
            </div>

            <div class="success-box">
                <h4><i class="fas fa-check-circle"></i> Vorteile</h4>
                <ul>
                    <li>Keine Installation erforderlich</li>
                    <li>Automatische Datenbank-Erstellung</li>
                    <li>Perfekt für kleine bis mittlere Setups</li>
                    <li>File-basiert, einfache Backups</li>
                </ul>
            </div>

            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> Rechte setzen</h4>
                <pre>sudo chown www-data:www-data /var/www/html/resources/system/database/monitoring.db
sudo chmod 644 /var/www/html/resources/system/database/monitoring.db</pre>
            </div>
        </div>

        <div class="docs-section">
            <h2>MySQL Setup (Production)</h2>
            <p>Für größere Deployments mit vielen Metrics.</p>

            <h3>1. Datenbank erstellen</h3>
            <div class="code-block">
                <pre>mysql -u root -p

CREATE DATABASE labservnet CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'labservnet'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD';
GRANT ALL PRIVILEGES ON labservnet.* TO 'labservnet'@'localhost';
FLUSH PRIVILEGES;
EXIT;</pre>
            </div>

            <h3>2. Config File</h3>
            <div class="code-block">
                <pre>&lt;?php

return [
    'driver' => 'mysql',
    'host' => 'localhost',
    'port' => 3306,
    'database' => 'labservnet',
    'username' => 'labservnet',
    'password' => 'STRONG_PASSWORD',
    'charset' => 'utf8mb4',
    'retention_days' => 30,
    'auto_cleanup' => true
];</pre>
            </div>

            <div class="success-box">
                <h4><i class="fas fa-check-circle"></i> Vorteile</h4>
                <ul>
                    <li>Bessere Performance bei vielen Metriken</li>
                    <li>Remote Database möglich</li>
                    <li>Professionelles Backup-System</li>
                    <li>Skalierbar für große Deployments</li>
                </ul>
            </div>
        </div>

        <div class="docs-section">
            <h2>Configuration Properties</h2>
            
            <div class="table-responsive">
                <table class="docs-table">
                    <thead>
                        <tr>
                            <th>Property</th>
                            <th>Type</th>
                            <th>Required</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>driver</code></td>
                            <td>string</td>
                            <td>✓</td>
                            <td>"mysql" oder "sqlite"</td>
                        </tr>
                        <tr>
                            <td><code>host</code></td>
                            <td>string</td>
                            <td>MySQL</td>
                            <td>Database Host (z.B. localhost)</td>
                        </tr>
                        <tr>
                            <td><code>port</code></td>
                            <td>integer</td>
                            <td>MySQL</td>
                            <td>Database Port (Standard: 3306)</td>
                        </tr>
                        <tr>
                            <td><code>database</code></td>
                            <td>string</td>
                            <td>✓</td>
                            <td>MySQL: DB Name / SQLite: File Path</td>
                        </tr>
                        <tr>
                            <td><code>username</code></td>
                            <td>string</td>
                            <td>MySQL</td>
                            <td>Database User</td>
                        </tr>
                        <tr>
                            <td><code>password</code></td>
                            <td>string</td>
                            <td>MySQL</td>
                            <td>Database Password</td>
                        </tr>
                        <tr>
                            <td><code>charset</code></td>
                            <td>string</td>
                            <td>✗</td>
                            <td>Standard: utf8mb4</td>
                        </tr>
                        <tr>
                            <td><code>retention_days</code></td>
                            <td>integer</td>
                            <td>✗</td>
                            <td>Tage für Datenaufbewahrung (Standard: 30)</td>
                        </tr>
                        <tr>
                            <td><code>auto_cleanup</code></td>
                            <td>boolean</td>
                            <td>✗</td>
                            <td>Automatisches Cleanup (Standard: true)</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="docs-section">
            <h2>Database Tables</h2>
            <p>Tabellen werden automatisch beim ersten Zugriff erstellt!</p>

            <h3>uptime_checks</h3>
            <p>Speichert alle Uptime Check Ergebnisse</p>
            <div class="code-block">
                <pre>CREATE TABLE uptime_checks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    node_id VARCHAR(255) NOT NULL,
    timestamp BIGINT UNSIGNED NOT NULL,
    status VARCHAR(50) NOT NULL,
    response_time DECIMAL(10,2) NULL,
    error_message TEXT NULL,
    INDEX idx_node_timestamp (node_id, timestamp),
    INDEX idx_timestamp (timestamp)
);</pre>
            </div>

            <h3>metrics_history</h3>
            <p>Historische Metrik-Daten für Charts</p>
            <div class="code-block">
                <pre>CREATE TABLE metrics_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    node_id VARCHAR(255) NOT NULL,
    timestamp BIGINT UNSIGNED NOT NULL,
    metric_type VARCHAR(100) NOT NULL,
    metric_name VARCHAR(255) NOT NULL,
    value DECIMAL(20,4) NOT NULL,
    labels TEXT NULL,
    INDEX idx_node_metric (node_id, metric_name, timestamp),
    INDEX idx_timestamp (timestamp)
);</pre>
            </div>

            <h3>downtime_events</h3>
            <p>Downtime Perioden mit Start/End</p>
            <div class="code-block">
                <pre>CREATE TABLE downtime_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    node_id VARCHAR(255) NOT NULL,
    start_time BIGINT UNSIGNED NOT NULL,
    end_time BIGINT UNSIGNED NULL,
    duration BIGINT UNSIGNED NULL,
    error_message TEXT NULL,
    INDEX idx_node_start (node_id, start_time)
);</pre>
            </div>
        </div>

        <div class="docs-section">
            <h2>Data Retention & Cleanup</h2>

            <h3>Automatisches Cleanup</h3>
            <p>Wenn <code>auto_cleanup: true</code> wird alte Data automatisch gelöscht:</p>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Trigger</h4>
                <p>Cleanup läuft bei jeder Metrik-Collection (alle 1 Min)</p>
                <p>Löscht Daten älter als <code>retention_days</code></p>
            </div>

            <h3>Manuelles Cleanup</h3>
            <div class="code-block">
                <pre># Cleanup für 30 Tage
php /var/www/html/scripts/cleanup_database.php 30

# Cleanup für 7 Tage
php /var/www/html/scripts/cleanup_database.php 7

# Komplettes Cleanup
php /var/www/html/scripts/cleanup_database.php 0</pre>
            </div>

            <h3>Cron für tägliches Cleanup (optional)</h3>
            <div class="code-block">
                <pre># Jeden Tag um 3 Uhr morgens
0 3 * * * php /var/www/html/scripts/cleanup_database.php 30 >> /var/log/db_cleanup.log 2>&1</pre>
            </div>
        </div>

        <div class="docs-section">
            <h2>Remote Database Setup</h2>
            <p>Für separaten Database Server</p>

            <h3>1. MySQL Server Setup</h3>
            <div class="code-block">
                <pre># Auf dem Database Server
mysql -u root -p

CREATE DATABASE labservnet CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'labservnet'@'192.168.1.%' IDENTIFIED BY 'STRONG_PASSWORD';
GRANT ALL PRIVILEGES ON labservnet.* TO 'labservnet'@'192.168.1.%';
FLUSH PRIVILEGES;
EXIT;</pre>
            </div>

            <h3>2. MySQL Binding anpassen</h3>
            <div class="code-block">
                <pre># /etc/mysql/mysql.conf.d/mysqld.cnf
bind-address = 0.0.0.0

# MySQL neu starten
sudo systemctl restart mysql</pre>
            </div>

            <h3>3. Firewall öffnen</h3>
            <div class="code-block">
                <pre># UFW
sudo ufw allow from 192.168.1.0/24 to any port 3306

# iptables
sudo iptables -A INPUT -p tcp -s 192.168.1.0/24 --dport 3306 -j ACCEPT</pre>
            </div>

            <h3>4. Config File</h3>
            <div class="code-block">
                <pre>&lt;?php

return [
    'driver' => 'mysql',
    'host' => '192.168.1.206',  // Remote DB Server
    'port' => 3306,
    'database' => 'labservnet',
    'username' => 'labservnet',
    'password' => 'STRONG_PASSWORD',
    'charset' => 'utf8mb4',
    'retention_days' => 30,
    'auto_cleanup' => true
];</pre>
            </div>
        </div>

        <div class="docs-section">
            <h2>Backup Strategies</h2>

            <h3>SQLite Backup</h3>
            <div class="code-block">
                <pre># Simple Copy
sudo cp /var/www/html/resources/system/database/monitoring.db /backup/monitoring_$(date +%Y%m%d).db

# Mit Kompression
sudo tar -czf /backup/monitoring_$(date +%Y%m%d).tar.gz /var/www/html/resources/system/database/monitoring.db

# Cron (täglich um 2 Uhr)
0 2 * * * sudo cp /var/www/html/resources/system/database/monitoring.db /backup/monitoring_$(date +\%Y\%m\%d).db</pre>
            </div>

            <h3>MySQL Backup</h3>
            <div class="code-block">
                <pre># mysqldump
mysqldump -u labservnet -p labservnet > /backup/labservnet_$(date +%Y%m%d).sql

# Mit Kompression
mysqldump -u labservnet -p labservnet | gzip > /backup/labservnet_$(date +%Y%m%d).sql.gz

# Cron (täglich um 2 Uhr)
0 2 * * * mysqldump -u labservnet -pPASSWORD labservnet | gzip > /backup/labservnet_$(date +\%Y\%m\%d).sql.gz</pre>
            </div>

            <h3>Restore</h3>
            <div class="code-block">
                <pre># SQLite
sudo cp /backup/monitoring_20240101.db /var/www/html/resources/system/database/monitoring.db

# MySQL
mysql -u labservnet -p labservnet < /backup/labservnet_20240101.sql

# MySQL compressed
gunzip < /backup/labservnet_20240101.sql.gz | mysql -u labservnet -p labservnet</pre>
            </div>
        </div>

        <div class="docs-section">
            <h2>Database Class Usage</h2>
            <p>PHP API für Database Operations</p>

            <h3>Connection</h3>
            <div class="code-block">
                <pre>&lt;?php

require_once __DIR__ . '/resources/system/database/Database.php';

$db = Database::getInstance();
$pdo = $db->getConnection();</pre>
            </div>

            <h3>Query Examples</h3>
            <div class="code-block">
                <pre>&lt;?php

// Alle Uptime Checks (letzte 24h)
$checks = $db->query(
    "SELECT * FROM uptime_checks 
     WHERE timestamp > ? 
     ORDER BY timestamp DESC",
    [time() - 86400]
);

// Service Status
$status = $db->query(
    "SELECT status, COUNT(*) as count 
     FROM uptime_checks 
     WHERE node_id = ? AND timestamp > ? 
     GROUP BY status",
    ['service_web-server', time() - 86400]
);

// Latest Metrics
$metrics = $db->query(
    "SELECT * FROM metrics_history 
     WHERE node_id = ? 
     ORDER BY timestamp DESC 
     LIMIT 100",
    ['server1']
);</pre>
            </div>

            <h3>Cleanup</h3>
            <div class="code-block">
                <pre>&lt;?php

$db = Database::getInstance();
$result = $db->cleanup(30);  // Löscht Daten älter als 30 Tage

echo "Deleted:\n";
echo "- uptime_checks: " . $result['uptime_checks'] . "\n";
echo "- metrics_history: " . $result['metrics_history'] . "\n";
echo "- downtime_events: " . $result['downtime_events'] . "\n";</pre>
            </div>
        </div>

        <div class="docs-section">
            <h2>Performance Tuning</h2>

            <h3>MySQL Optimization</h3>
            <div class="code-block">
                <pre># /etc/mysql/mysql.conf.d/mysqld.cnf

[mysqld]
# InnoDB Buffer Pool (50-70% RAM)
innodb_buffer_pool_size = 2G

# Query Cache
query_cache_size = 128M
query_cache_limit = 2M

# Connections
max_connections = 200

# Logs
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow-query.log
long_query_time = 2</pre>
            </div>

            <h3>Indizes prüfen</h3>
            <div class="code-block">
                <pre>mysql -u labservnet -p labservnet

-- Zeige alle Indizes
SHOW INDEX FROM uptime_checks;
SHOW INDEX FROM metrics_history;

-- Query Performance
EXPLAIN SELECT * FROM uptime_checks WHERE node_id = 'service_web-server' AND timestamp > 1234567890;</pre>
            </div>
        </div>

        <div class="docs-section">
            <h2>Troubleshooting</h2>
            
            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> Connection refused</h4>
                <p><strong>Problem:</strong> Can't connect to MySQL server</p>
                <p><strong>Lösung:</strong></p>
                <pre># MySQL läuft?
sudo systemctl status mysql

# Port offen?
sudo netstat -tlnp | grep 3306

# Firewall?
sudo ufw status

# Remote: bind-address?
grep bind-address /etc/mysql/mysql.conf.d/mysqld.cnf</pre>
            </div>

            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> Access denied</h4>
                <p><strong>Problem:</strong> Access denied for user</p>
                <p><strong>Lösung:</strong></p>
                <pre>mysql -u root -p

-- User existiert?
SELECT user, host FROM mysql.user WHERE user='labservnet';

-- Privileges?
SHOW GRANTS FOR 'labservnet'@'localhost';

-- Neu erstellen
DROP USER 'labservnet'@'localhost';
CREATE USER 'labservnet'@'localhost' IDENTIFIED BY 'PASSWORD';
GRANT ALL PRIVILEGES ON labservnet.* TO 'labservnet'@'localhost';
FLUSH PRIVILEGES;</pre>
            </div>

            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> SQLite locked</h4>
                <p><strong>Problem:</strong> Database is locked</p>
                <p><strong>Lösung:</strong></p>
                <pre># Rechte prüfen
ls -la /var/www/html/resources/system/database/monitoring.db

# Rechte setzen
sudo chown www-data:www-data /var/www/html/resources/system/database/monitoring.db
sudo chmod 644 /var/www/html/resources/system/database/monitoring.db

# Directory auch schreibbar?
sudo chmod 755 /var/www/html/resources/system/database/</pre>
            </div>
        </div>

        <div class="docs-section">
            <h2>Nächste Schritte</h2>
            <div class="quick-links">
                <a href="/docs/troubleshooting" class="quick-link">
                    <i class="fas fa-wrench"></i>
                    <div class="quick-link-content">
                        <h4>Troubleshooting</h4>
                        <p>Probleme lösen</p>
                    </div>
                </a>
                <a href="/docs/quick-start" class="quick-link">
                    <i class="fas fa-rocket"></i>
                    <div class="quick-link-content">
                        <h4>Quick Start</h4>
                        <p>Erste Schritte</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</body>
</html>
