<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uptime Configuration - Docs</title>
    <link rel="stylesheet" href="/public/dashboard.css">
    <link rel="stylesheet" href="/public/docs.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include __DIR__ . '/../../components/docs-sidebar.php'; ?>

    <div class="docs-content">
        <div class="docs-header">
            <h1><i class="fas fa-heartbeat"></i> Uptime Monitoring Configuration</h1>
            <p>Konfiguriere Services und Hosts für Verfügbarkeitsüberwachung.</p>
        </div>

        <div class="docs-section">
            <h2>Config File Location</h2>
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Pfad</h4>
                <p><strong>File:</strong> <code>/var/www/html/config/uptime_config.json</code></p>
            </div>
        </div>

        <div class="docs-section">
            <h2>Configuration Structure</h2>
            
            <div class="code-block">
                <pre>{
  "check_interval": 60,
  "ping_timeout": 2,
  "tcp_timeout": 2,
  "icmp_count": 1,
  "retention_days": 30,
  "enable_icmp": true,
  "enable_tcp": true,
  "enable_udp": false,
  "services": [
    {
      "id": "web-server",
      "name": "Web Server",
      "host": "192.168.1.100",
      "port": 443,
      "protocol": "tcp",
      "enabled": true,
      "icon": "fa-globe",
      "tags": ["web", "production"]
    }
  ]
}</pre>
            </div>
        </div>

        <div class="docs-section">
            <h2>Global Settings</h2>
            
            <div class="table-responsive">
                <table class="docs-table">
                    <thead>
                        <tr>
                            <th>Property</th>
                            <th>Type</th>
                            <th>Default</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>check_interval</code></td>
                            <td>integer</td>
                            <td>60</td>
                            <td>Check Intervall in Sekunden</td>
                        </tr>
                        <tr>
                            <td><code>ping_timeout</code></td>
                            <td>integer</td>
                            <td>2</td>
                            <td>ICMP Ping Timeout in Sekunden</td>
                        </tr>
                        <tr>
                            <td><code>tcp_timeout</code></td>
                            <td>integer</td>
                            <td>2</td>
                            <td>TCP/UDP Connection Timeout</td>
                        </tr>
                        <tr>
                            <td><code>icmp_count</code></td>
                            <td>integer</td>
                            <td>1</td>
                            <td>Anzahl Ping Packets</td>
                        </tr>
                        <tr>
                            <td><code>retention_days</code></td>
                            <td>integer</td>
                            <td>30</td>
                            <td>Tage für Check History</td>
                        </tr>
                        <tr>
                            <td><code>enable_icmp</code></td>
                            <td>boolean</td>
                            <td>true</td>
                            <td>ICMP Checks aktiviert</td>
                        </tr>
                        <tr>
                            <td><code>enable_tcp</code></td>
                            <td>boolean</td>
                            <td>true</td>
                            <td>TCP Checks aktiviert</td>
                        </tr>
                        <tr>
                            <td><code>enable_udp</code></td>
                            <td>boolean</td>
                            <td>false</td>
                            <td>UDP Checks aktiviert</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="docs-section">
            <h2>Service Properties</h2>
            
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
                            <td><code>id</code></td>
                            <td>string</td>
                            <td>✓</td>
                            <td>Eindeutige Service-ID</td>
                        </tr>
                        <tr>
                            <td><code>name</code></td>
                            <td>string</td>
                            <td>✓</td>
                            <td>Anzeige-Name</td>
                        </tr>
                        <tr>
                            <td><code>host</code></td>
                            <td>string</td>
                            <td>✓</td>
                            <td>IP-Adresse oder Hostname</td>
                        </tr>
                        <tr>
                            <td><code>port</code></td>
                            <td>integer</td>
                            <td>✗*</td>
                            <td>Port (nur für TCP/UDP)</td>
                        </tr>
                        <tr>
                            <td><code>protocol</code></td>
                            <td>string</td>
                            <td>✓</td>
                            <td>"icmp", "tcp" oder "udp"</td>
                        </tr>
                        <tr>
                            <td><code>enabled</code></td>
                            <td>boolean</td>
                            <td>✓</td>
                            <td>Service aktiv?</td>
                        </tr>
                        <tr>
                            <td><code>icon</code></td>
                            <td>string</td>
                            <td>✗</td>
                            <td>FontAwesome Icon</td>
                        </tr>
                        <tr>
                            <td><code>tags</code></td>
                            <td>array</td>
                            <td>✗</td>
                            <td>Tags für Gruppierung</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <small>* Port ist required für TCP/UDP, nicht für ICMP</small>
        </div>

        <div class="docs-section">
            <h2>Protocol Types</h2>

            <h3>ICMP (Ping)</h3>
            <p>Standard Ping Check zur Host-Erreichbarkeit. Benötigt keine Port-Angabe.</p>
            
            <div class="code-block">
                <pre>{
  "id": "router",
  "name": "Main Router",
  "host": "192.168.1.1",
  "protocol": "icmp",
  "enabled": true,
  "icon": "fa-network-wired",
  "tags": ["network", "infrastructure"]
}</pre>
            </div>

            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> Root Rechte</h4>
                <p>ICMP Ping benötigt root Rechte oder CAP_NET_RAW capability!</p>
                <pre>sudo setcap cap_net_raw+ep /usr/bin/ping</pre>
            </div>

            <h3>TCP Port Check</h3>
            <p>Überprüft ob ein TCP Port offen ist. Ideal für Services.</p>
            
            <div class="code-block">
                <pre>{
  "id": "webserver-https",
  "name": "Web Server HTTPS",
  "host": "192.168.1.100",
  "port": 443,
  "protocol": "tcp",
  "enabled": true,
  "icon": "fa-globe",
  "tags": ["web", "production"]
}</pre>
            </div>

            <h4>Häufige TCP Ports</h4>
            <div class="table-responsive">
                <table class="docs-table">
                    <thead>
                        <tr>
                            <th>Port</th>
                            <th>Service</th>
                            <th>Port</th>
                            <th>Service</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>22</td>
                            <td>SSH</td>
                            <td>3306</td>
                            <td>MySQL</td>
                        </tr>
                        <tr>
                            <td>80</td>
                            <td>HTTP</td>
                            <td>5432</td>
                            <td>PostgreSQL</td>
                        </tr>
                        <tr>
                            <td>443</td>
                            <td>HTTPS</td>
                            <td>6379</td>
                            <td>Redis</td>
                        </tr>
                        <tr>
                            <td>25</td>
                            <td>SMTP</td>
                            <td>9100</td>
                            <td>Node Exporter</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <h3>UDP Port Check</h3>
            <p>UDP Check für verbindungslose Protokolle.</p>
            
            <div class="code-block">
                <pre>{
  "id": "dns-server",
  "name": "DNS Server",
  "host": "192.168.1.1",
  "port": 53,
  "protocol": "udp",
  "enabled": true,
  "icon": "fa-network-wired",
  "tags": ["dns", "infrastructure"]
}</pre>
            </div>

            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> UDP Limitation</h4>
                <p>UDP ist verbindungslos. Check verifiziert nur ob Packet gesendet werden kann, nicht ob Service antwortet!</p>
            </div>
        </div>

        <div class="docs-section">
            <h2>Complete Example</h2>
            
            <div class="code-block">
                <pre>{
  "check_interval": 60,
  "ping_timeout": 2,
  "tcp_timeout": 2,
  "icmp_count": 1,
  "retention_days": 30,
  "enable_icmp": true,
  "enable_tcp": true,
  "enable_udp": false,
  "services": [
    {
      "id": "router",
      "name": "OPNsense Router",
      "host": "192.168.1.1",
      "protocol": "icmp",
      "enabled": true,
      "icon": "fa-network-wired",
      "tags": ["network"]
    },
    {
      "id": "webserver-http",
      "name": "Web Server (HTTP)",
      "host": "192.168.1.100",
      "port": 80,
      "protocol": "tcp",
      "enabled": true,
      "icon": "fa-globe",
      "tags": ["web"]
    },
    {
      "id": "webserver-https",
      "name": "Web Server (HTTPS)",
      "host": "192.168.1.100",
      "port": 443,
      "protocol": "tcp",
      "enabled": true,
      "icon": "fa-lock",
      "tags": ["web"]
    },
    {
      "id": "mysql",
      "name": "MySQL Database",
      "host": "192.168.1.206",
      "port": 3306,
      "protocol": "tcp",
      "enabled": true,
      "icon": "fa-database",
      "tags": ["database"]
    },
    {
      "id": "dns",
      "name": "Internal DNS",
      "host": "192.168.1.1",
      "port": 53,
      "protocol": "udp",
      "enabled": false,
      "icon": "fa-network-wired",
      "tags": ["dns"]
    }
  ]
}</pre>
            </div>
        </div>

        <div class="docs-section">
            <h2>Cron Setup</h2>

            <h3>Empfohlene Konfiguration (alle 10 Sekunden)</h3>
            <div class="code-block">
                <pre># Uptime Checker Wrapper (läuft 6x pro Minute = alle 10 Sek)
* * * * * /var/www/html/scripts/uptime_checker_wrapper.sh >> /var/log/uptime_checker.log 2>&1</pre>
            </div>

            <h3>Alternative (jede Minute)</h3>
            <div class="code-block">
                <pre># Uptime Checker direkt
* * * * * php /var/www/html/scripts/uptime_checker.php >> /var/log/uptime_checker.log 2>&1</pre>
            </div>
        </div>

        <div class="docs-section">
            <h2>Database Tables</h2>

            <h3>uptime_checks</h3>
            <p>Speichert alle Check-Ergebnisse</p>
            <div class="code-block">
                <pre>CREATE TABLE uptime_checks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    node_id VARCHAR(255) NOT NULL,  -- Service ID mit 'service_' prefix
    timestamp BIGINT UNSIGNED NOT NULL,
    status VARCHAR(50) NOT NULL,    -- 'up' oder 'down'
    response_time DECIMAL(10,2) NULL,
    error_message TEXT NULL
);</pre>
            </div>

            <h3>downtime_events</h3>
            <p>Trackt Downtime-Perioden</p>
            <div class="code-block">
                <pre>CREATE TABLE downtime_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    node_id VARCHAR(255) NOT NULL,
    start_time BIGINT UNSIGNED NOT NULL,
    end_time BIGINT UNSIGNED NULL,
    duration BIGINT UNSIGNED NULL,
    error_message TEXT NULL
);</pre>
            </div>
        </div>

        <div class="docs-section">
            <h2>Uptime Statistics</h2>
            <p>Das System berechnet automatisch Uptime für:</p>

            <div class="feature-grid">
                <div class="feature-card">
                    <h3><i class="fas fa-clock"></i> 24 Stunden</h3>
                    <p>Letzte 24h Uptime</p>
                </div>
                <div class="feature-card">
                    <h3><i class="fas fa-calendar-week"></i> 7 Tage</h3>
                    <p>Letzte 7d Uptime</p>
                </div>
                <div class="feature-card">
                    <h3><i class="fas fa-calendar-alt"></i> 30 Tage</h3>
                    <p>Letzte 30d Uptime</p>
                </div>
            </div>

            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Berechnung</h4>
                <p><strong>Formula:</strong> (Successful Checks / Total Checks) × 100</p>
                <p><strong>Example:</strong> 1438 von 1440 Checks = 99.86% Uptime</p>
            </div>
        </div>

        <div class="docs-section">
            <h2>API Endpoints</h2>
            
            <div class="code-block">
                <pre># Alle Services mit Status
GET /resources/system/api/uptime.php?action=services

# Check History für Service
GET /resources/system/api/uptime.php?action=history&node=service_web-server&hours=24

# Downtime Events
GET /resources/system/api/uptime.php?action=downtime&node=service_web-server&limit=10</pre>
            </div>
        </div>

        <div class="docs-section">
            <h2>Best Practices</h2>
            
            <div class="feature-grid">
                <div class="feature-card">
                    <h3><i class="fas fa-check-circle"></i> Service IDs</h3>
                    <p>Verwende sprechende IDs: <code>webserver-https</code></p>
                </div>
                <div class="feature-card">
                    <h3><i class="fas fa-layer-group"></i> Multiple Checks</h3>
                    <p>Überwache kritische Services mit HTTP + HTTPS</p>
                </div>
                <div class="feature-card">
                    <h3><i class="fas fa-tachometer-alt"></i> Performance</h3>
                    <p>Max 30-50 Services für schnelle Checks</p>
                </div>
                <div class="feature-card">
                    <h3><i class="fas fa-tags"></i> Tags nutzen</h3>
                    <p>Gruppiere Services logisch mit Tags</p>
                </div>
            </div>
        </div>

        <div class="docs-section">
            <h2>Troubleshooting</h2>
            
            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> ICMP funktioniert nicht</h4>
                <p><strong>Problem:</strong> Alle ICMP Checks schlagen fehl</p>
                <p><strong>Lösung:</strong></p>
                <pre># Capability setzen
sudo setcap cap_net_raw+ep /usr/bin/ping

# ODER Cron als root ausführen
sudo crontab -e</pre>
            </div>

            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> Keine Checks in Datenbank</h4>
                <p><strong>Problem:</strong> uptime_checks Tabelle bleibt leer</p>
                <p><strong>Lösung:</strong></p>
                <pre># Manuell ausführen
php /var/www/html/scripts/uptime_checker.php

# Logs prüfen
tail -f /var/log/uptime_checker.log

# Cron läuft?
crontab -l</pre>
            </div>
        </div>

        <div class="docs-section">
            <h2>Nächste Schritte</h2>
            <div class="quick-links">
                <a href="/docs/config/database" class="quick-link">
                    <i class="fas fa-database"></i>
                    <div class="quick-link-content">
                        <h4>Database Config</h4>
                        <p>Retention und Cleanup</p>
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
    </div>
</body>
</html>
