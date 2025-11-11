<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Structure - Docs</title>
    <link rel="stylesheet" href="/public/dashboard.css">
    <link rel="stylesheet" href="/public/docs.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .file-tree {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            font-family: 'Monaco', 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.8;
        }
        
        .file-tree .dir {
            color: #7eb26d;
            font-weight: 600;
        }
        
        .file-tree .file {
            color: #d8d9da;
        }
        
        .file-tree .comment {
            color: #9fa0a3;
            font-style: italic;
        }
        
        .file-icon {
            margin-right: 5px;
        }
        
        .file-icon.folder {
            color: #7eb26d;
        }
        
        .file-icon.php {
            color: #777bb3;
        }
        
        .file-icon.js {
            color: #f7df1e;
        }
        
        .file-icon.css {
            color: #1572b6;
        }
        
        .file-icon.json {
            color: #ffca28;
        }
        
        .file-icon.md {
            color: #00aaff;
        }
        
        .file-desc {
            background: var(--bg-tertiary);
            padding: 15px;
            border-left: 3px solid #D3433E;
            margin: 10px 0;
        }
        
        .file-desc h4 {
            margin: 0 0 8px 0;
            color: #D3433E;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .file-desc p {
            margin: 5px 0;
            color: var(--text-secondary);
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../../components/docs-sidebar.php'; ?>

    <div class="docs-content">
        <div class="docs-header">
            <h1><i class="fas fa-folder-tree"></i> File Structure</h1>
            <p>Übersicht aller Files und deren Funktionen.</p>
        </div>

        <div class="docs-section">
            <h2>Complete Directory Tree</h2>
            
            <div class="file-tree">
<pre>/var/www/html/
│
├── <span class="dir"><i class="fas fa-folder"></i> config/</span>                           <span class="comment"># Konfigurationsdateien</span>
│   ├── <span class="file"><i class="fas fa-file-code file-icon json"></i> node_exporters.json</span>          <span class="comment"># Node Exporter Instances + Dashboard Config</span>
│   ├── <span class="file"><i class="fas fa-file-code file-icon json"></i> uptime_config.json</span>           <span class="comment"># Uptime Monitoring Services</span>
│   └── <span class="file"><i class="fas fa-file-alt file-icon md"></i> DASHBOARD_CONFIG.md</span>          <span class="comment"># Dashboard Config Dokumentation</span>
│
├── <span class="dir"><i class="fas fa-folder"></i> public/</span>                           <span class="comment"># Frontend Assets (CSS, JS)</span>
│   ├── <span class="file"><i class="fas fa-file-code file-icon css"></i> dashboard.css</span>               <span class="comment"># Haupt-Dashboard Styling</span>
│   ├── <span class="file"><i class="fas fa-file-code file-icon css"></i> sidebar.css</span>                 <span class="comment"># Sidebar Navigation Styling</span>
│   ├── <span class="file"><i class="fas fa-file-code file-icon css"></i> docs.css</span>                    <span class="comment"># Dokumentation Styling</span>
│   ├── <span class="file"><i class="fas fa-file-code file-icon css"></i> animations.css</span>              <span class="comment"># CSS Animationen</span>
│   ├── <span class="file"><i class="fas fa-file-code file-icon css"></i> errors.css</span>                  <span class="comment"># Error Pages Styling</span>
│   ├── <span class="file"><i class="fas fa-file-code file-icon js"></i> dashboard.js</span>                <span class="comment"># Original Dashboard Logic (nicht mehr benutzt)</span>
│   ├── <span class="file"><i class="fas fa-file-code file-icon js"></i> dashboard-dynamic.js</span>         <span class="comment"># Dynamic Dashboard Engine (AKTIV)</span>
│   └── <span class="file"><i class="fas fa-file-code file-icon js"></i> animations.js</span>               <span class="comment"># Animation Manager</span>
│
├── <span class="dir"><i class="fas fa-folder"></i> resources/</span>
│   ├── <span class="dir"><i class="fas fa-folder"></i> system/</span>                      <span class="comment"># Backend System</span>
│   │   ├── <span class="dir"><i class="fas fa-folder"></i> api/</span>                     <span class="comment"># REST API Endpoints</span>
│   │   │   ├── <span class="file"><i class="fas fa-file-code file-icon php"></i> node_exporter.php</span>  <span class="comment"># Node Exporter API (metrics, instances)</span>
│   │   │   └── <span class="file"><i class="fas fa-file-code file-icon php"></i> uptime.php</span>          <span class="comment"># Uptime API (services, history, downtime)</span>
│   │   │
│   │   ├── <span class="dir"><i class="fas fa-folder"></i> database/</span>                <span class="comment"># Database Layer</span>
│   │   │   ├── <span class="file"><i class="fas fa-file-code file-icon php"></i> config.php</span>          <span class="comment"># Database Config (MySQL/SQLite)</span>
│   │   │   └── <span class="file"><i class="fas fa-file-code file-icon php"></i> Database.php</span>        <span class="comment"># Database Class (Singleton PDO)</span>
│   │   │
│   │   ├── <span class="dir"><i class="fas fa-folder"></i> monitoring/</span>              <span class="comment"># Monitoring Logic</span>
│   │   │   └── <span class="file"><i class="fas fa-file-code file-icon php"></i> UptimeMonitor.php</span>   <span class="comment"># Uptime Tracking, Downtime Events</span>
│   │   │
│   │   └── <span class="dir"><i class="fas fa-folder"></i> responses/</span>               <span class="comment"># HTTP Error Pages</span>
│   │       ├── <span class="file"><i class="fas fa-file-code"></i> 400.html</span>            <span class="comment"># Bad Request</span>
│   │       ├── <span class="file"><i class="fas fa-file-code"></i> 404.html</span>            <span class="comment"># Not Found</span>
│   │       ├── <span class="file"><i class="fas fa-file-code"></i> 405.html</span>            <span class="comment"># Method Not Allowed</span>
│   │       └── <span class="file"><i class="fas fa-file-code"></i> 500.html</span>            <span class="comment"># Internal Server Error</span>
│   │
│   └── <span class="dir"><i class="fas fa-folder"></i> views/</span>                       <span class="comment"># Frontend Views (PHP)</span>
│       ├── <span class="dir"><i class="fas fa-folder"></i> components/</span>              <span class="comment"># Wiederverwendbare Komponenten</span>
│       │   ├── <span class="file"><i class="fas fa-file-code file-icon php"></i> sidebar.php</span>         <span class="comment"># Main Sidebar mit Node-Links</span>
│       │   └── <span class="file"><i class="fas fa-file-code file-icon php"></i> docs-sidebar.php</span>    <span class="comment"># Docs Sidebar mit Kategorien</span>
│       │
│       └── <span class="dir"><i class="fas fa-folder"></i> pages/</span>                   <span class="comment"># Seiten</span>
│           ├── <span class="file"><i class="fas fa-file-code file-icon php"></i> index.php</span>           <span class="comment"># Homepage mit Features</span>
│           ├── <span class="file"><i class="fas fa-file-code file-icon php"></i> dashboard.php</span>       <span class="comment"># Dynamic Dashboard Page</span>
│           ├── <span class="file"><i class="fas fa-file-code file-icon php"></i> uptime.php</span>          <span class="comment"># Uptime Status Page</span>
│           │
│           └── <span class="dir"><i class="fas fa-folder"></i> internal/docs/</span>       <span class="comment"># Dokumentation</span>
│               ├── <span class="file"><i class="fas fa-file-code file-icon php"></i> index.php</span>       <span class="comment"># Docs Startseite</span>
│               ├── <span class="file"><i class="fas fa-file-code file-icon php"></i> quick-start.php</span> <span class="comment"># 5-Minuten Guide</span>
│               ├── <span class="file"><i class="fas fa-file-code file-icon php"></i> installation.php</span><span class="comment"># Vollständige Installation</span>
│               ├── <span class="file"><i class="fas fa-file-code file-icon php"></i> troubleshooting.php</span> <span class="comment"># Problemlösungen</span>
│               ├── <span class="file"><i class="fas fa-file-code file-icon php"></i> config-node-exporters.php</span> <span class="comment"># Node Exporter Config</span>
│               ├── <span class="file"><i class="fas fa-file-code file-icon php"></i> config-dashboard.php</span> <span class="comment"># Dashboard Config Guide</span>
│               ├── <span class="file"><i class="fas fa-file-code file-icon php"></i> config-uptime.php</span> <span class="comment"># Uptime Config Guide</span>
│               └── <span class="file"><i class="fas fa-file-code file-icon php"></i> config-database.php</span> <span class="comment"># Database Config Guide</span>
│
├── <span class="dir"><i class="fas fa-folder"></i> routes/</span>                          <span class="comment"># URL Routing</span>
│   ├── <span class="file"><i class="fas fa-file-code file-icon php"></i> web.php</span>                    <span class="comment"># Authenticated Routes (leer, noch nicht implementiert)</span>
│   └── <span class="file"><i class="fas fa-file-code file-icon php"></i> public.php</span>                 <span class="comment"># Public Routes (ALLE aktiven Routes)</span>
│
├── <span class="dir"><i class="fas fa-folder"></i> scripts/</span>                         <span class="comment"># Cron Scripts</span>
│   ├── <span class="file"><i class="fas fa-file-code file-icon php"></i> metrics_collector.php</span>      <span class="comment"># Sammelt Metrics in DB (1 Min)</span>
│   ├── <span class="file"><i class="fas fa-file-code file-icon php"></i> uptime_checker.php</span>         <span class="comment"># Uptime Checks (10 Sek via Wrapper)</span>
│   ├── <span class="file"><i class="fas fa-terminal"></i> uptime_checker_wrapper.sh</span>  <span class="comment"># Wrapper für 10-Sek Checks</span>
│   ├── <span class="file"><i class="fas fa-file-code file-icon php"></i> cleanup_database.php</span>       <span class="comment"># Löscht alte Daten</span>
│   └── <span class="file"><i class="fas fa-file-code file-icon php"></i> migrate_database.php</span>       <span class="comment"># DB Migration (labels Spalte)</span>
│
├── <span class="file"><i class="fas fa-file-code file-icon php"></i> index.php</span>                       <span class="comment"># MAIN ROUTER - Dispatched alle Requests</span>
├── <span class="file"><i class="fas fa-file-code"></i> test-api.html</span>                  <span class="comment"># API Test Page</span>
├── <span class="file"><i class="fas fa-file-alt file-icon md"></i> README.md</span>                       <span class="comment"># Projekt README</span>
├── <span class="file"><i class="fas fa-file-alt file-icon md"></i> README_MONITORING.md</span>            <span class="comment"># Monitoring Features Docs</span>
├── <span class="file"><i class="fas fa-file-alt file-icon md"></i> UPTIME_SETUP.md</span>                <span class="comment"># Uptime Setup Guide</span>
└── <span class="file"><i class="fas fa-ban"></i> .gitignore</span>                     <span class="comment"># Git Ignore Rules</span>
</pre>
            </div>
        </div>

        <div class="docs-section">
            <h2>Root Files</h2>

            <div class="file-desc">
                <h4><i class="fas fa-door-open"></i> <code>index.php</code></h4>
                <p><strong>Funktion:</strong> Hauptrouter für ALLE Requests</p>
                <p><strong>Was macht es:</strong></p>
                <ul>
                    <li>Lädt routes aus <code>routes/web.php</code> und <code>routes/public.php</code></li>
                    <li>Parst REQUEST_URI und matcht gegen Route-Patterns</li>
                    <li>Unterstützt dynamische Routes mit Parametern: <code>/dashboard/{node}</code></li>
                    <li>Setzt $_GET Parameter für dynamische Segmente</li>
                    <li>Includet die gematchte PHP-Datei</li>
                    <li>404 Error wenn keine Route matched</li>
                </ul>
            </div>

            <div class="file-desc">
                <h4><i class="fas fa-book"></i> <code>README.md</code></h4>
                <p><strong>Funktion:</strong> Haupt-Dokumentation für GitHub</p>
                <p><strong>Inhalt:</strong> Features, Installation, Konfiguration, Screenshots</p>
            </div>

            <div class="file-desc">
                <h4><i class="fas fa-book"></i> <code>README_MONITORING.md</code></h4>
                <p><strong>Funktion:</strong> Monitoring System Dokumentation</p>
                <p><strong>Inhalt:</strong> Uptime Monitoring, Historical Data, API Endpoints</p>
            </div>

            <div class="file-desc">
                <h4><i class="fas fa-book"></i> <code>UPTIME_SETUP.md</code></h4>
                <p><strong>Funktion:</strong> Uptime Setup Guide</p>
                <p><strong>Inhalt:</strong> Database Setup, uptime_config.json, Cron Jobs</p>
            </div>

            <div class="file-desc">
                <h4><i class="fas fa-vial"></i> <code>test-api.html</code></h4>
                <p><strong>Funktion:</strong> API Testing Tool</p>
                <p><strong>Tests:</strong> Instances laden, Metrics fetchen, CPU berechnen</p>
            </div>
        </div>

        <div class="docs-section">
            <h2>Config Directory</h2>

            <div class="file-desc">
                <h4><i class="fas fa-cog"></i> <code>config/node_exporters.json</code></h4>
                <p><strong>Funktion:</strong> Node Exporter Instances UND Dashboard Konfiguration</p>
                <p><strong>Struktur:</strong></p>
                <pre>
{
  "instances": [
    {
      "id": "server1",
      "title": "Main Server",
      "host": "localhost",
      "port": 9100,
      "enabled": true,
      "tags": ["production"],
      "dashboard": {
        "cards": [...],    // Metric Cards
        "charts": [...],   // Charts
        "tables": [...]    // Tables
      }
    }
  ]
}</pre>
                <p><strong>Wichtig:</strong> Dashboard Config ist IN node_exporters.json!</p>
            </div>

            <div class="file-desc">
                <h4><i class="fas fa-heartbeat"></i> <code>config/uptime_config.json</code></h4>
                <p><strong>Funktion:</strong> Uptime Monitoring Services</p>
                <p><strong>Struktur:</strong></p>
                <pre>
{
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
      "protocol": "tcp",  // "tcp", "udp", oder "icmp"
      "enabled": true
    }
  ]
}</pre>
            </div>

            <div class="file-desc">
                <h4><i class="fas fa-file-alt"></i> <code>config/DASHBOARD_CONFIG.md</code></h4>
                <p><strong>Funktion:</strong> Dashboard Konfiguration Guide</p>
                <p><strong>Inhalt:</strong> Card Types, Chart Types, Calculation Types, Filter Syntax</p>
            </div>
        </div>

        <div class="docs-section">
            <h2>Public Directory (Frontend Assets)</h2>

            <div class="file-desc">
                <h4><i class="fas fa-palette"></i> <code>public/dashboard.css</code></h4>
                <p><strong>Funktion:</strong> Haupt-Styling für Dashboard</p>
                <p><strong>Styles:</strong> Metrics Grid, Charts, Tables, Cards, Gauges, Dark Theme</p>
            </div>

            <div class="file-desc">
                <h4><i class="fas fa-palette"></i> <code>public/sidebar.css</code></h4>
                <p><strong>Funktion:</strong> Sidebar Navigation Styling</p>
                <p><strong>Styles:</strong> Sidebar Layout, Node Links, Active States, Hover Effects</p>
            </div>

            <div class="file-desc">
                <h4><i class="fas fa-palette"></i> <code>public/docs.css</code></h4>
                <p><strong>Funktion:</strong> Dokumentation Styling</p>
                <p><strong>Styles:</strong> Docs Layout, Code Blocks, Tables, Info Boxes, Warning Boxes</p>
            </div>

            <div class="file-desc">
                <h4><i class="fas fa-palette"></i> <code>public/animations.css</code></h4>
                <p><strong>Funktion:</strong> CSS Animationen</p>
                <p><strong>Animations:</strong> Fade-in, Slide-in, Scale, Pulse, Ripple, Stagger</p>
            </div>

            <div class="file-desc">
                <h4><i class="fas fa-palette"></i> <code>public/errors.css</code></h4>
                <p><strong>Funktion:</strong> Error Pages Styling</p>
                <p><strong>Styles:</strong> 404, 500, etc. Error Pages</p>
            </div>

            <div class="file-desc">
                <h4><i class="fas fa-code"></i> <code>public/dashboard-dynamic.js</code> ⭐ AKTIV</h4>
                <p><strong>Funktion:</strong> Dynamic Dashboard Engine</p>
                <p><strong>Was macht es:</strong></p>
                <ul>
                    <li>Liest Dashboard Config aus node_exporters.json</li>
                    <li>Generiert Cards dynamisch (Value, Gauge)</li>
                    <li>Generiert Charts dynamisch (Line, Bar)</li>
                    <li>Generiert Tables dynamisch (System Info, Custom)</li>
                    <li>Calculation Engine: cpu_info_format, memory_gb_format, filesystem_gb_format, cpu_percentage, rate, percentage, direct</li>
                    <li>Filter Support: device="eth0", device!~"lo|veth.*"</li>
                    <li>Auto-Refresh mit 5 Sekunden Intervall</li>
                    <li>Historical Data für Charts (Live, 1h, 6h, 24h, 7d, 30d)</li>
                    <li>Chart.js Integration mit smooth Updates</li>
                </ul>
            </div>

            <div class="file-desc">
                <h4><i class="fas fa-code"></i> <code>public/dashboard.js</code> ❌ NICHT MEHR BENUTZT</h4>
                <p><strong>Status:</strong> Original Dashboard Logic, wurde durch dashboard-dynamic.js ersetzt</p>
                <p><strong>Hinweis:</strong> Kann gelöscht werden wenn dashboard-dynamic.js stabil läuft</p>
            </div>

            <div class="file-desc">
                <h4><i class="fas fa-code"></i> <code>public/animations.js</code></h4>
                <p><strong>Funktion:</strong> Animation Manager</p>
                <p><strong>Features:</strong></p>
                <ul>
                    <li>Page Loader mit Progress Bar</li>
                    <li>Scroll Reveal Animations</li>
                    <li>Hover Effects (Scale, Ripple)</li>
                    <li>Staggered Animations für Listen</li>
                </ul>
            </div>
        </div>

        <div class="docs-section">
            <h2>Routes Directory</h2>

            <div class="file-desc">
                <h4><i class="fas fa-route"></i> <code>routes/public.php</code> ⭐ AKTIV</h4>
                <p><strong>Funktion:</strong> Alle öffentlichen Routes</p>
                <p><strong>Routes:</strong></p>
                <ul>
                    <li><code>/</code> → index.php (Homepage)</li>
                    <li><code>/dashboard</code> → dashboard.php</li>
                    <li><code>/dashboard/{node}</code> → dashboard.php mit node Parameter</li>
                    <li><code>/uptime</code> → uptime.php</li>
                    <li><code>/uptime/{service}</code> → uptime.php mit service Parameter</li>
                    <li><code>/docs</code> → docs/index.php</li>
                    <li><code>/docs/*</code> → Verschiedene Docs-Seiten</li>
                </ul>
                <p><strong>Format:</strong> <code>'route/path' => 'resources/views/pages/file.php'</code></p>
            </div>

            <div class="file-desc">
                <h4><i class="fas fa-route"></i> <code>routes/web.php</code> ❌ LEER</h4>
                <p><strong>Funktion:</strong> Authentifizierte Routes</p>
                <p><strong>Status:</strong> Noch nicht implementiert, aktuell leer</p>
                <p><strong>Zukunft:</strong> Login-geschützte Bereiche, Admin Panel</p>
            </div>
        </div>

        <div class="docs-section">
            <h2>API Directory</h2>

            <div class="file-desc">
                <h4><i class="fas fa-plug"></i> <code>resources/system/api/node_exporter.php</code></h4>
                <p><strong>Funktion:</strong> Node Exporter API Endpoint</p>
                <p><strong>Actions:</strong></p>
                <ul>
                    <li><code>?action=instances</code> - Liste aller Instances aus config</li>
                    <li><code>?action=metrics&node=server1</code> - Metriken für Node</li>
                </ul>
                <p><strong>Response Format:</strong></p>
                <pre>
{
  "success": true,
  "data": {
    "instance": {...},
    "cpu": {...},
    "memory": {...},
    "disk": {...},
    "network": {...},
    "system": {...},
    "timestamp": 1234567890
  }
}</pre>
                <p><strong>Features:</strong></p>
                <ul>
                    <li>Fetcht Metrics von Node Exporter via curl</li>
                    <li>Parst Prometheus Format</li>
                    <li>Gruppiert Metrics nach Kategorie</li>
                    <li>Error Handling mit detaillierten Fehlermeldungen</li>
                </ul>
            </div>

            <div class="file-desc">
                <h4><i class="fas fa-heartbeat"></i> <code>resources/system/api/uptime.php</code></h4>
                <p><strong>Funktion:</strong> Uptime Monitoring API</p>
                <p><strong>Actions:</strong></p>
                <ul>
                    <li><code>?action=status</code> - Status aller Services</li>
                    <li><code>?action=status&node=service_web-server</code> - Status eines Service</li>
                    <li><code>?action=history&node=service_web-server&hours=24</code> - Check History</li>
                    <li><code>?action=downtime&node=service_web-server&limit=10</code> - Downtime Events</li>
                    <li><code>?action=services</code> - Alle Services mit Status</li>
                </ul>
                <p><strong>Features:</strong></p>
                <ul>
                    <li>Nutzt UptimeMonitor Class</li>
                    <li>Berechnet Uptime Prozentsätze (24h, 7d, 30d)</li>
                    <li>Liefert Downtime Events</li>
                </ul>
            </div>
        </div>

        <div class="docs-section">
            <h2>Database Directory</h2>

            <div class="file-desc">
                <h4><i class="fas fa-database"></i> <code>resources/system/database/config.php</code></h4>
                <p><strong>Funktion:</strong> Database Configuration</p>
                <p><strong>Optionen:</strong></p>
                <ul>
                    <li><strong>SQLite:</strong> <code>'driver' => 'sqlite'</code></li>
                    <li><strong>MySQL:</strong> <code>'driver' => 'mysql'</code> mit host, port, username, password</li>
                    <li><strong>Settings:</strong> retention_days, auto_cleanup</li>
                </ul>
            </div>

            <div class="file-desc">
                <h4><i class="fas fa-database"></i> <code>resources/system/database/Database.php</code></h4>
                <p><strong>Funktion:</strong> Database Singleton Class</p>
                <p><strong>Features:</strong></p>
                <ul>
                    <li>Singleton Pattern (eine Instanz pro Request)</li>
                    <li>PDO Connection Management</li>
                    <li>Auto-Create Tables (uptime_checks, metrics_history, downtime_events)</li>
                    <li>Cleanup Funktion für alte Daten</li>
                    <li>Unterstützt MySQL und SQLite</li>
                </ul>
                <p><strong>Tables:</strong></p>
                <ul>
                    <li><strong>uptime_checks:</strong> node_id, timestamp, status, response_time, error_message</li>
                    <li><strong>metrics_history:</strong> node_id, timestamp, metric_type, metric_name, value, labels</li>
                    <li><strong>downtime_events:</strong> node_id, start_time, end_time, duration, error_message</li>
                </ul>
            </div>
        </div>

        <div class="docs-section">
            <h2>Monitoring Directory</h2>

            <div class="file-desc">
                <h4><i class="fas fa-chart-line"></i> <code>resources/system/monitoring/UptimeMonitor.php</code></h4>
                <p><strong>Funktion:</strong> Uptime Tracking Logic</p>
                <p><strong>Methods:</strong></p>
                <ul>
                    <li><code>recordCheck($nodeId, $status, $responseTime, $error)</code> - Speichert Check-Ergebnis</li>
                    <li><code>getUptimePercentage($nodeId, $hours)</code> - Berechnet Uptime %</li>
                    <li><code>getUptimeHistory($nodeId, $hours)</code> - Holt Check History</li>
                    <li><code>getDowntimeEvents($nodeId, $limit)</code> - Holt Downtime Events</li>
                    <li><code>getAllNodesStatus()</code> - Status aller Nodes/Services</li>
                    <li><code>startDowntimeEvent($nodeId, $error)</code> - Startet Downtime Event</li>
                    <li><code>endDowntimeEvent($nodeId)</code> - Beendet Downtime Event</li>
                </ul>
                <p><strong>Features:</strong></p>
                <ul>
                    <li>Automatische Downtime Event Tracking</li>
                    <li>Uptime % Berechnung über verschiedene Zeiträume</li>
                    <li>Current Status + Last Check Info</li>
                </ul>
            </div>
        </div>

        <div class="docs-section">
            <h2>Scripts Directory (Cron Jobs)</h2>

            <div class="file-desc">
                <h4><i class="fas fa-chart-bar"></i> <code>scripts/metrics_collector.php</code></h4>
                <p><strong>Funktion:</strong> Sammelt Metrics von allen Node Exporters</p>
                <p><strong>Ablauf:</strong></p>
                <ol>
                    <li>Liest node_exporters.json</li>
                    <li>Iteriert durch alle enabled Instances</li>
                    <li>Fetcht Metrics via curl</li>
                    <li>Parst Prometheus Format</li>
                    <li>Speichert in metrics_history Tabelle</li>
                </ol>
                <p><strong>Cron:</strong> <code>* * * * * php /var/www/html/scripts/metrics_collector.php</code></p>
                <p><strong>Intervall:</strong> Jede Minute</p>
            </div>

            <div class="file-desc">
                <h4><i class="fas fa-heartbeat"></i> <code>scripts/uptime_checker.php</code></h4>
                <p><strong>Funktion:</strong> Führt Uptime Checks durch</p>
                <p><strong>Checks:</strong></p>
                <ul>
                    <li><strong>ICMP:</strong> Ping mit timeout</li>
                    <li><strong>TCP:</strong> Socket connect mit fsockopen</li>
                    <li><strong>UDP:</strong> Socket send mit socket_sendto</li>
                </ul>
                <p><strong>Ablauf:</strong></p>
                <ol>
                    <li>Liest uptime_config.json</li>
                    <li>Iteriert durch alle enabled Services</li>
                    <li>Führt Check basierend auf Protocol durch</li>
                    <li>Speichert Ergebnis via UptimeMonitor</li>
                </ol>
                <p><strong>Cron:</strong> Via wrapper (siehe unten)</p>
            </div>

            <div class="file-desc">
                <h4><i class="fas fa-sync"></i> <code>scripts/uptime_checker_wrapper.sh</code></h4>
                <p><strong>Funktion:</strong> Wrapper für 10-Sekunden Checks</p>
                <p><strong>Logic:</strong> Loop 6x mit 10 Sekunden sleep = alle 10 Sekunden ein Check</p>
                <p><strong>Cron:</strong> <code>* * * * * /var/www/html/scripts/uptime_checker_wrapper.sh</code></p>
                <p><strong>Warum:</strong> Cron kann nur jede Minute, Wrapper ermöglicht 10-Sekunden-Intervall</p>
            </div>

            <div class="file-desc">
                <h4><i class="fas fa-trash"></i> <code>scripts/cleanup_database.php</code></h4>
                <p><strong>Funktion:</strong> Löscht alte Daten</p>
                <p><strong>Usage:</strong> <code>php cleanup_database.php [Tage]</code></p>
                <p><strong>Beispiel:</strong> <code>php cleanup_database.php 30</code> (löscht Daten > 30 Tage)</p>
                <p><strong>Cron:</strong> <code>0 3 * * * php /var/www/html/scripts/cleanup_database.php 30</code></p>
                <p><strong>Was wird gelöscht:</strong></p>
                <ul>
                    <li>uptime_checks älter als X Tage</li>
                    <li>metrics_history älter als X Tage</li>
                    <li>downtime_events älter als X Tage</li>
                </ul>
            </div>

            <div class="file-desc">
                <h4><i class="fas fa-database"></i> <code>scripts/migrate_database.php</code></h4>
                <p><strong>Funktion:</strong> Database Migration</p>
                <p><strong>Was macht es:</strong> Fügt 'labels' Spalte zur metrics_history Tabelle hinzu</p>
                <p><strong>Usage:</strong> <code>php migrate_database.php</code></p>
                <p><strong>Einmalig:</strong> Nur ausführen wenn Tabelle bereits existiert ohne labels Spalte</p>
            </div>
        </div>

        <div class="docs-section">
            <h2>Views Directory</h2>

            <div class="file-desc">
                <h4><i class="fas fa-home"></i> <code>resources/views/pages/index.php</code></h4>
                <p><strong>Funktion:</strong> Homepage</p>
                <p><strong>Inhalt:</strong> Features Liste, Call-to-Action, Links zu Docs</p>
            </div>

            <div class="file-desc">
                <h4><i class="fas fa-tachometer-alt"></i> <code>resources/views/pages/dashboard.php</code></h4>
                <p><strong>Funktion:</strong> Dynamic Dashboard Page</p>
                <p><strong>Features:</strong></p>
                <ul>
                    <li>Lädt dashboard-dynamic.js</li>
                    <li>Container für Cards, Charts, Tables</li>
                    <li>Auto-Refresh Toggle</li>
                    <li>Refresh Button</li>
                    <li>Chart.js Integration</li>
                </ul>
            </div>

            <div class="file-desc">
                <h4><i class="fas fa-heartbeat"></i> <code>resources/views/pages/uptime.php</code></h4>
                <p><strong>Funktion:</strong> Uptime Status Page</p>
                <p><strong>Features:</strong></p>
                <ul>
                    <li>Service Status Cards (Up/Down)</li>
                    <li>Response Time</li>
                    <li>Uptime Prozentsätze (24h, 7d, 30d)</li>
                    <li>Check Frequency</li>
                    <li>Last Check Time</li>
                    <li>Filter nach Tags</li>
                </ul>
            </div>

            <div class="file-desc">
                <h4><i class="fas fa-puzzle-piece"></i> <code>resources/views/components/sidebar.php</code></h4>
                <p><strong>Funktion:</strong> Main Sidebar</p>
                <p><strong>Features:</strong></p>
                <ul>
                    <li>Liest node_exporters.json</li>
                    <li>Generiert Links für alle enabled Nodes</li>
                    <li>Home, Uptime, Docs Links</li>
                    <li>Active State Management</li>
                    <li>Node Status Dots</li>
                </ul>
            </div>

            <div class="file-desc">
                <h4><i class="fas fa-book"></i> <code>resources/views/components/docs-sidebar.php</code></h4>
                <p><strong>Funktion:</strong> Docs Sidebar</p>
                <p><strong>Kategorien:</strong></p>
                <ul>
                    <li>Getting Started (Quick Start, Installation)</li>
                    <li>Configuration (Node Exporters, Dashboard, Uptime, Database)</li>
                    <li>Advanced (Metrics, Routing, API)</li>
                    <li>Reference (File Structure, Troubleshooting)</li>
                </ul>
            </div>
        </div>

        <div class="docs-section">
            <h2>Docs Pages</h2>

            <div class="file-desc">
                <h4><i class="fas fa-book"></i> <code>resources/views/pages/internal/docs/index.php</code></h4>
                <p><strong>Funktion:</strong> Docs Startseite</p>
                <p><strong>Inhalt:</strong> Overview, Features, Quick Links</p>
            </div>

            <div class="file-desc">
                <h4><i class="fas fa-rocket"></i> <code>resources/views/pages/internal/docs/quick-start.php</code></h4>
                <p><strong>Funktion:</strong> 5-Minuten Quick Start Guide</p>
                <p><strong>Steps:</strong> Config Files, Database, Node Config, Cron Jobs, Testing</p>
            </div>

            <div class="file-desc">
                <h4><i class="fas fa-download"></i> <code>resources/views/pages/internal/docs/installation.php</code></h4>
                <p><strong>Funktion:</strong> Vollständige Installation</p>
                <p><strong>Inhalt:</strong> System Requirements, PHP/nginx Setup, Node Exporter, Multi-Server</p>
            </div>

            <div class="file-desc">
                <h4><i class="fas fa-wrench"></i> <code>resources/views/pages/internal/docs/troubleshooting.php</code></h4>
                <p><strong>Funktion:</strong> Troubleshooting Guide</p>
                <p><strong>Kategorien:</strong> Dashboard, Metrics, Uptime, Database, Config, Performance Problems</p>
            </div>

            <div class="file-desc">
                <h4><i class="fas fa-server"></i> <code>resources/views/pages/internal/docs/config-node-exporters.php</code></h4>
                <p><strong>Funktion:</strong> Node Exporter Config Guide</p>
                <p><strong>Inhalt:</strong> Instance Properties, Dashboard Object, Examples, API</p>
            </div>

            <div class="file-desc">
                <h4><i class="fas fa-chart-line"></i> <code>resources/views/pages/internal/docs/config-dashboard.php</code></h4>
                <p><strong>Funktion:</strong> Dashboard Config Guide (umfangreichste Page)</p>
                <p><strong>Inhalt:</strong></p>
                <ul>
                    <li>Card Types (Value, Gauge)</li>
                    <li>Calculation Types (cpu_info_format, memory_gb_format, etc.)</li>
                    <li>Chart Structure mit Metrics</li>
                    <li>Table Structure</li>
                    <li>Filter Syntax (Prometheus)</li>
                    <li>FontAwesome Icons</li>
                    <li>Complete Examples</li>
                </ul>
            </div>

            <div class="file-desc">
                <h4><i class="fas fa-heartbeat"></i> <code>resources/views/pages/internal/docs/config-uptime.php</code></h4>
                <p><strong>Funktion:</strong> Uptime Config Guide</p>
                <p><strong>Inhalt:</strong> Global Settings, Service Properties, Protocol Types (ICMP/TCP/UDP), Common Ports</p>
            </div>

            <div class="file-desc">
                <h4><i class="fas fa-database"></i> <code>resources/views/pages/internal/docs/config-database.php</code></h4>
                <p><strong>Funktion:</strong> Database Config Guide</p>
                <p><strong>Inhalt:</strong> SQLite vs MySQL, Table Structure, Cleanup, Remote DB, Backups</p>
            </div>
        </div>

        <div class="docs-section">
            <h2>Data Flow</h2>

            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Request Flow</h4>
                <p><strong>1. User Request:</strong> http://domain.com/dashboard/server1</p>
                <p><strong>2. index.php:</strong> Parsed URL, matched route, setzt $_GET['node'] = 'server1'</p>
                <p><strong>3. dashboard.php:</strong> Lädt, includet sidebar.php, lädt dashboard-dynamic.js</p>
                <p><strong>4. dashboard-dynamic.js:</strong> Liest URL Parameter, fetcht Config, generiert Dashboard</p>
                <p><strong>5. API Call:</strong> /resources/system/api/node_exporter.php?action=metrics&node=server1</p>
                <p><strong>6. node_exporter.php:</strong> Fetcht Metrics, parst, returned JSON</p>
                <p><strong>7. dashboard-dynamic.js:</strong> Rendert Cards, Charts, Tables mit Daten</p>
            </div>

            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Cron Job Flow</h4>
                <p><strong>Metrics Collection (jede Minute):</strong></p>
                <ol>
                    <li>Cron startet metrics_collector.php</li>
                    <li>Liest node_exporters.json</li>
                    <li>Fetcht Metrics von jedem enabled Node</li>
                    <li>Parst Prometheus Format</li>
                    <li>Speichert in metrics_history Tabelle</li>
                </ol>
                
                <p><strong>Uptime Checks (alle 10 Sekunden):</strong></p>
                <ol>
                    <li>Cron startet uptime_checker_wrapper.sh jede Minute</li>
                    <li>Wrapper führt uptime_checker.php 6x aus (alle 10 Sek)</li>
                    <li>uptime_checker.php liest uptime_config.json</li>
                    <li>Führt ICMP/TCP/UDP Checks durch</li>
                    <li>UptimeMonitor speichert Ergebnis in uptime_checks</li>
                    <li>UptimeMonitor tracked Downtime Events</li>
                </ol>
            </div>
        </div>

        <div class="docs-section">
            <h2>Wichtige Konzepte</h2>

            <div class="feature-grid">
                <div class="feature-card">
                    <h3><i class="fas fa-route"></i> Routing</h3>
                    <p>Zentrales Routing via index.php</p>
                    <p>Routes definiert in routes/public.php</p>
                    <p>Unterstützt dynamische Parameter</p>
                </div>
                
                <div class="feature-card">
                    <h3><i class="fas fa-cog"></i> Config-Driven</h3>
                    <p>Dashboard komplett aus JSON Config</p>
                    <p>Keine Code-Änderungen für neue Charts</p>
                    <p>Dashboard IN node_exporters.json</p>
                </div>
                
                <div class="feature-card">
                    <h3><i class="fas fa-database"></i> Database Layer</h3>
                    <p>Singleton Pattern für Connection</p>
                    <p>Auto-Create Tables</p>
                    <p>MySQL & SQLite Support</p>
                </div>
                
                <div class="feature-card">
                    <h3><i class="fas fa-plug"></i> API-First</h3>
                    <p>REST APIs für Metrics & Uptime</p>
                    <p>JSON Response Format</p>
                    <p>Frontend fetcht Daten via API</p>
                </div>
            </div>
        </div>

        <div class="docs-section">
            <h2>Nächste Schritte</h2>
            <div class="quick-links">
                <a href="/docs/quick-start" class="quick-link">
                    <i class="fas fa-rocket"></i>
                    <div class="quick-link-content">
                        <h4>Quick Start</h4>
                        <p>Los geht's</p>
                    </div>
                </a>
                <a href="/docs/config/dashboard" class="quick-link">
                    <i class="fas fa-tachometer-alt"></i>
                    <div class="quick-link-content">
                        <h4>Dashboard Config</h4>
                        <p>Dashboard anpassen</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</body>
</html>
