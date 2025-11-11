<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quick Start Guide - Docs</title>
    <link rel="stylesheet" href="/public/dashboard.css">
    <link rel="stylesheet" href="/public/docs.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include __DIR__ . '/../../components/docs-sidebar.php'; ?>

    <div class="docs-content">
        <div class="docs-header">
            <h1><i class="fas fa-rocket"></i> Quick Start Guide</h1>
            <p>In 5 Minuten loslegen mit LabservNET Monitoring.</p>
        </div>

        <div class="docs-section">
            <h2>Voraussetzungen</h2>
            <div class="feature-grid">
                <div class="feature-card">
                    <h3><i class="fas fa-server"></i> Server</h3>
                    <ul>
                        <li>PHP 8.1+ (PDO, MySQL, cURL)</li>
                        <li>MySQL/MariaDB oder SQLite</li>
                        <li>nginx/Apache mit PHP-FPM</li>
                        <li>Cron Support</li>
                    </ul>
                </div>
                <div class="feature-card">
                    <h3><i class="fas fa-network-wired"></i> Node Exporter</h3>
                    <ul>
                        <li>Prometheus Node Exporter</li>
                        <li>Port 9100 offen</li>
                        <li>/metrics Endpoint erreichbar</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="docs-section">
            <h2>Schritt 1: Datenbank konfigurieren</h2>
            <p>Bearbeite <code>/var/www/html/resources/system/database/config.php</code></p>

            <h3>Option A: SQLite (einfach, für Testing)</h3>
            <div class="code-block">
                <pre>&lt;?php
return [
    'driver' => 'sqlite',
    'database' => __DIR__ . '/monitoring.db',
    'retention_days' => 30,
    'auto_cleanup' => true,
];</pre>
            </div>

            <h3>Option B: MySQL/MariaDB (empfohlen)</h3>
            <div class="code-block">
                <pre># Datenbank erstellen
mysql -u root -p

CREATE DATABASE monitoring CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'monitoring'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON monitoring.* TO 'monitoring'@'localhost';
FLUSH PRIVILEGES;</pre>
            </div>

            <div class="code-block">
                <pre>&lt;?php
return [
    'driver' => 'mysql',
    'host' => 'localhost',
    'port' => 3306,
    'database' => 'monitoring',
    'username' => 'monitoring',
    'password' => 'secure_password',
    'charset' => 'utf8mb4',
    'retention_days' => 30,
    'auto_cleanup' => true,
];</pre>
            </div>

            <div class="success-box">
                <h4><i class="fas fa-check-circle"></i> Automatische Tabellen</h4>
                <p>Die Tabellen <code>uptime_checks</code>, <code>metrics_history</code> und <code>downtime_events</code> werden automatisch beim ersten Zugriff erstellt!</p>
            </div>
        </div>

        <div class="docs-section">
            <h2>Schritt 2: Node Exporter konfigurieren</h2>
            <p>Bearbeite <code>/var/www/html/config/node_exporters.json</code></p>

            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> Wichtig!</h4>
                <p>Die Dashboard-Konfiguration ist <strong>direkt in dieser Datei</strong> unter jedem Instance im <code>"dashboard"</code> Objekt!</p>
            </div>

            <div class="code-block">
                <pre>{
  "instances": [
    {
      "id": "server1",
      "title": "Main Server",
      "host": "192.168.1.100",
      "port": 9100,
      "enabled": true,
      "tags": ["production"],
      "dashboard": {
        "cards": [
          {
            "type": "value",
            "title": "CPU",
            "icon": "fa-microchip",
            "metric": "cpu_usage",
            "calculation": {
              "type": "cpu_info_format"
            },
            "format": "cpu_info"
          },
          {
            "type": "value",
            "title": "Memory",
            "icon": "fa-memory",
            "metric": "memory_usage",
            "calculation": {
              "type": "memory_gb_format"
            },
            "format": "memory_gb"
          },
          {
            "type": "value",
            "title": "Disk",
            "icon": "fa-hard-drive",
            "metric": "disk_usage",
            "calculation": {
              "type": "filesystem_gb_format",
              "filter": "mountpoint=\"/\""
            },
            "format": "disk_gb"
          }
        ],
        "charts": [
          {
            "type": "line",
            "title": "CPU Usage",
            "metrics": [
              {
                "name": "CPU",
                "calculation": {
                  "type": "cpu_percentage",
                  "metrics": ["node_cpu_seconds_total"]
                },
                "color": "#D3433E",
                "fill": false
              }
            ]
          }
        ],
        "tables": []
      }
    }
  ]
}</pre>
            </div>
        </div>

        <div class="docs-section">
            <h2>Schritt 3: Cron Jobs einrichten</h2>

            <div class="code-block">
                <pre># Crontab bearbeiten
crontab -e

# Metriken jede Minute sammeln
* * * * * php /var/www/html/scripts/metrics_collector.php >> /var/log/metrics_collector.log 2>&1

# Uptime Checks alle 10 Sekunden (6x pro Minute)
* * * * * /var/www/html/scripts/uptime_checker_wrapper.sh >> /var/log/uptime_checker.log 2>&1

# Optional: Cleanup täglich um 3 Uhr
0 3 * * * php /var/www/html/scripts/cleanup_database.php 30 >> /var/log/cleanup.log 2>&1</pre>
            </div>

            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Script Pfade</h4>
                <p><strong>Scripts:</strong> <code>/var/www/html/scripts/</code></p>
                <p><strong>Config:</strong> <code>/var/www/html/config/</code></p>
                <p><strong>Database:</strong> <code>/var/www/html/resources/system/database/</code></p>
            </div>
        </div>

        <div class="docs-section">
            <h2>Schritt 4: Services konfigurieren (optional)</h2>
            <p>Bearbeite <code>/var/www/html/config/uptime_config.json</code></p>

            <div class="code-block">
                <pre>{
  "check_interval": 60,
  "ping_timeout": 2,
  "tcp_timeout": 2,
  "enable_icmp": true,
  "enable_tcp": true,
  "services": [
    {
      "id": "web-server",
      "name": "Web Server",
      "host": "192.168.1.100",
      "port": 443,
      "protocol": "tcp",
      "enabled": true,
      "icon": "fa-globe",
      "tags": ["web"]
    }
  ]
}</pre>
            </div>
        </div>

        <div class="docs-section">
            <h2>Schritt 5: Testen</h2>

            <h3>Manuell testen</h3>
            <div class="code-block">
                <pre># Node Exporter erreichbar?
curl http://192.168.1.100:9100/metrics

# Metrics Collector ausführen
php /var/www/html/scripts/metrics_collector.php

# Uptime Checker ausführen
php /var/www/html/scripts/uptime_checker.php

# Datenbank prüfen (MySQL)
mysql -u monitoring -p monitoring -e "SELECT COUNT(*) FROM metrics_history;"</pre>
            </div>

            <h3>Dashboard aufrufen</h3>
            <div class="code-block">
                <pre>http://your-server/dashboard/server1</pre>
            </div>

            <div class="success-box">
                <h4><i class="fas fa-check-circle"></i> Fertig!</h4>
                <p>Dein Dashboard sollte jetzt Live-Metriken anzeigen! 🎉</p>
            </div>
        </div>

        <div class="docs-section">
            <h2>Nächste Schritte</h2>
            <div class="quick-links">
                <a href="/docs/config/node-exporters" class="quick-link">
                    <i class="fas fa-server"></i>
                    <div class="quick-link-content">
                        <h4>Node Exporters</h4>
                        <p>Mehr Nodes hinzufügen</p>
                    </div>
                </a>
                <a href="/docs/config/dashboard" class="quick-link">
                    <i class="fas fa-tachometer-alt"></i>
                    <div class="quick-link-content">
                        <h4>Dashboard Config</h4>
                        <p>Charts und Cards anpassen</p>
                    </div>
                </a>
                <a href="/docs/config/uptime" class="quick-link">
                    <i class="fas fa-heartbeat"></i>
                    <div class="quick-link-content">
                        <h4>Uptime Monitoring</h4>
                        <p>Services überwachen</p>
                    </div>
                </a>
                <a href="/docs/troubleshooting" class="quick-link">
                    <i class="fas fa-wrench"></i>
                    <div class="quick-link-content">
                        <h4>Troubleshooting</h4>
                        <p>Probleme lösen</p>
                    </div>
                </a>
            </div>
        </div>

        <div class="docs-section">
            <h2>Häufige Probleme</h2>
            
            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> Keine Metriken im Dashboard?</h4>
                <p><strong>1.</strong> Node Exporter erreichbar? <code>curl http://HOST:9100/metrics</code></p>
                <p><strong>2.</strong> Collector läuft? <code>php scripts/metrics_collector.php</code></p>
                <p><strong>3.</strong> Datenbank? <code>SELECT COUNT(*) FROM metrics_history;</code></p>
            </div>
        </div>
    </div>
</body>
</html>
