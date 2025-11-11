<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Node Exporters Configuration - Docs</title>
    <link rel="stylesheet" href="/public/dashboard.css">
    <link rel="stylesheet" href="/public/docs.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include __DIR__ . '/../../components/docs-sidebar.php'; ?>

    <div class="docs-content">
        <div class="docs-header">
            <h1><i class="fas fa-server"></i> Node Exporters Configuration</h1>
            <p>Konfiguriere alle deine Node Exporter Instanzen und deren Dashboards.</p>
        </div>

        <div class="docs-section">
            <h2>Config File Location</h2>
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Wichtiger Pfad</h4>
                <p><strong>File:</strong> <code>/var/www/html/config/node_exporters.json</code></p>
                <p>Diese Datei enthält ALLE Node Exporter Instanzen UND ihre Dashboard-Konfigurationen!</p>
            </div>
        </div>

        <div class="docs-section">
            <h2>Grundstruktur</h2>
            
            <div class="code-block">
                <pre>{
  "instances": [
    {
      "id": "server1",
      "title": "Main Server",
      "host": "192.168.1.100",
      "port": 9100,
      "enabled": true,
      "tags": ["production", "web"],
      "dashboard": {
        "cards": [ /* Metric Cards */ ],
        "charts": [ /* Charts */ ],
        "tables": [ /* Tables */ ]
      }
    }
  ]
}</pre>
            </div>
        </div>

        <div class="docs-section">
            <h2>Instance Properties</h2>
            
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
                            <td>Eindeutige ID (URL: /dashboard/{id})</td>
                        </tr>
                        <tr>
                            <td><code>title</code></td>
                            <td>string</td>
                            <td>✓</td>
                            <td>Anzeigename im Dashboard</td>
                        </tr>
                        <tr>
                            <td><code>host</code></td>
                            <td>string</td>
                            <td>✓</td>
                            <td>IP oder Hostname des Node Exporters</td>
                        </tr>
                        <tr>
                            <td><code>port</code></td>
                            <td>integer</td>
                            <td>✓</td>
                            <td>Port (Standard: 9100)</td>
                        </tr>
                        <tr>
                            <td><code>enabled</code></td>
                            <td>boolean</td>
                            <td>✓</td>
                            <td>true = aktiv, false = deaktiviert</td>
                        </tr>
                        <tr>
                            <td><code>tags</code></td>
                            <td>array</td>
                            <td>✗</td>
                            <td>Tags für Gruppierung/Filterung</td>
                        </tr>
                        <tr>
                            <td><code>dashboard</code></td>
                            <td>object</td>
                            <td>✓</td>
                            <td>Dashboard-Konfiguration (cards, charts, tables)</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="docs-section">
            <h2>Minimale Konfiguration</h2>
            
            <div class="code-block">
                <pre>{
  "instances": [
    {
      "id": "server1",
      "title": "Main Server",
      "host": "192.168.1.100",
      "port": 9100,
      "enabled": true,
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
          }
        ],
        "charts": [],
        "tables": []
      }
    }
  ]
}</pre>
            </div>
        </div>

        <div class="docs-section">
            <h2>Dashboard Object</h2>
            <p>Das <code>dashboard</code> Objekt enthält drei Hauptbereiche:</p>

            <div class="feature-grid">
                <div class="feature-card">
                    <h3><i class="fas fa-square"></i> Cards</h3>
                    <p>Einzelne Metrik-Werte mit Berechnungen</p>
                    <ul>
                        <li>CPU Usage</li>
                        <li>Memory Usage</li>
                        <li>Disk Space</li>
                        <li>System Load</li>
                    </ul>
                </div>
                <div class="feature-card">
                    <h3><i class="fas fa-chart-line"></i> Charts</h3>
                    <p>Zeit-basierte Graphen</p>
                    <ul>
                        <li>Line Charts</li>
                        <li>Bar Charts</li>
                        <li>Multiple Metriken</li>
                        <li>Per-Interface Charts</li>
                    </ul>
                </div>
                <div class="feature-card">
                    <h3><i class="fas fa-table"></i> Tables</h3>
                    <p>Strukturierte Daten-Ansichten</p>
                    <ul>
                        <li>Filesystem Usage</li>
                        <li>Network Interfaces</li>
                        <li>System Info</li>
                        <li>Custom Tables</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="docs-section">
            <h2>Mehrere Instanzen</h2>
            
            <div class="code-block">
                <pre>{
  "instances": [
    {
      "id": "webserver",
      "title": "Web Server",
      "host": "192.168.1.100",
      "port": 9100,
      "enabled": true,
      "tags": ["production", "web"],
      "dashboard": { /* ... */ }
    },
    {
      "id": "database",
      "title": "Database Server",
      "host": "192.168.1.200",
      "port": 9100,
      "enabled": true,
      "tags": ["production", "database"],
      "dashboard": { /* ... */ }
    },
    {
      "id": "backup",
      "title": "Backup Server",
      "host": "192.168.1.250",
      "port": 9100,
      "enabled": false,
      "tags": ["maintenance"],
      "dashboard": { /* ... */ }
    }
  ]
}</pre>
            </div>
        </div>

        <div class="docs-section">
            <h2>Remote Node Exporter</h2>
            
            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> Firewall</h4>
                <p>Stelle sicher, dass Port 9100 vom Monitoring-Server aus erreichbar ist!</p>
            </div>

            <div class="code-block">
                <pre># Auf dem Remote Server:
sudo ufw allow from MONITORING_SERVER_IP to any port 9100

# Node Exporter starten
sudo systemctl start node_exporter
sudo systemctl enable node_exporter

# Test von Monitoring Server
curl http://REMOTE_IP:9100/metrics</pre>
            </div>
        </div>

        <div class="docs-section">
            <h2>API Access</h2>
            <p>Die konfigurierten Instanzen sind über die API erreichbar:</p>

            <div class="code-block">
                <pre># Alle Instanzen
GET /resources/system/api/node_exporter.php?action=instances

# Metriken einer Instanz
GET /resources/system/api/node_exporter.php?action=metrics&node=server1

# Historische Daten
GET /resources/system/api/node_exporter.php?action=history&node=server1&type=cpu&hours=24</pre>
            </div>
        </div>

        <div class="docs-section">
            <h2>Best Practices</h2>
            
            <div class="feature-grid">
                <div class="feature-card">
                    <h3><i class="fas fa-check-circle"></i> Naming</h3>
                    <p>Verwende sprechende IDs ohne Sonderzeichen: <code>webserver-prod</code> statt <code>srv01</code></p>
                </div>
                <div class="feature-card">
                    <h3><i class="fas fa-tags"></i> Tags nutzen</h3>
                    <p>Gruppiere Server mit Tags: production, staging, web, database, etc.</p>
                </div>
                <div class="feature-card">
                    <h3><i class="fas fa-toggle-on"></i> enabled Flag</h3>
                    <p>Deaktiviere temporär Nodes statt sie zu löschen</p>
                </div>
                <div class="feature-card">
                    <h3><i class="fas fa-code"></i> JSON Syntax</h3>
                    <p>Validiere JSON vor dem Speichern mit <code>jsonlint</code></p>
                </div>
            </div>
        </div>

        <div class="docs-section">
            <h2>Troubleshooting</h2>
            
            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> Instance wird nicht geladen</h4>
                <p><strong>Problem:</strong> Dashboard zeigt "Instance not found"</p>
                <p><strong>Lösung:</strong></p>
                <pre>1. JSON Syntax prüfen: php -r "json_decode(file_get_contents('config/node_exporters.json'));"
2. ID stimmt mit URL überein? /dashboard/{id}
3. enabled = true gesetzt?
4. API Test: curl http://localhost/resources/system/api/node_exporter.php?action=instances</pre>
            </div>

            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> Keine Metriken</h4>
                <p><strong>Problem:</strong> Dashboard lädt aber zeigt keine Daten</p>
                <p><strong>Lösung:</strong></p>
                <pre>1. Node Exporter erreichbar? curl http://HOST:9100/metrics
2. Firewall? telnet HOST 9100
3. Collector läuft? php scripts/metrics_collector.php
4. Datenbank Verbindung? resources/system/database/config.php</pre>
            </div>
        </div>

        <div class="docs-section">
            <h2>Nächste Schritte</h2>
            <div class="quick-links">
                <a href="/docs/config/dashboard" class="quick-link">
                    <i class="fas fa-tachometer-alt"></i>
                    <div class="quick-link-content">
                        <h4>Dashboard Config</h4>
                        <p>Cards, Charts und Tables konfigurieren</p>
                    </div>
                </a>
                <a href="/docs" class="quick-link">
                    <i class="fas fa-book"></i>
                    <div class="quick-link-content">
                        <h4>Docs Overview</h4>
                        <p>Zurück zur Übersicht</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</body>
</html>
