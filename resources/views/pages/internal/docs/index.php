<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LabservNET - Documentation</title>
    <link rel="stylesheet" href="/public/sidebar.css">
    <link rel="stylesheet" href="/public/dashboard.css">
    <link rel="stylesheet" href="/public/docs.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #0b0c0e;
            margin: 0;
            padding: 0;
        }
        .docs-content {
            margin-left: 280px;
            padding: 40px;
            max-width: 1400px;
            margin-right: auto;
        }

        .docs-header {
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #2d2d2d;
        }

        .docs-header h1 {
            font-size: 42px;
            color: #d8d9da;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .docs-header h1 i {
            color: #D3433E;
        }

        .docs-header p {
            font-size: 18px;
            color: #9fa0a3;
            line-height: 1.6;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 40px 0;
        }

        .feature-card {
            background: #181b1f;
            padding: 30px;
            border-radius: 12px;
            border: 1px solid #2d2d2d;
            transition: all 0.2s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            border-color: #D3433E;
            box-shadow: 0 8px 24px rgba(211, 67, 62, 0.2);
        }

        .feature-card h3 {
            color: #D3433E;
            font-size: 20px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .feature-card p {
            color: #9fa0a3;
            line-height: 1.6;
            font-size: 14px;
        }

        .feature-card ul {
            margin-top: 15px;
            padding-left: 20px;
            color: #9fa0a3;
        }

        .feature-card ul li {
            margin: 8px 0;
            font-size: 14px;
        }

        .quick-links {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin: 30px 0;
        }

        .quick-link {
            display: flex;
            align-items: center;
            padding: 20px;
            background: #181b1f;
            border: 1px solid #2d2d2d;
            border-radius: 8px;
            text-decoration: none;
            color: #d8d9da;
            transition: all 0.2s ease;
            gap: 15px;
        }

        .quick-link:hover {
            transform: translateX(5px);
            border-color: #D3433E;
            background: rgba(211, 67, 62, 0.1);
        }

        .quick-link i {
            font-size: 24px;
            color: #D3433E;
        }

        .quick-link-content h4 {
            font-size: 16px;
            margin-bottom: 5px;
            color: #d8d9da;
        }

        .quick-link-content p {
            font-size: 12px;
            color: #9fa0a3;
        }

        .section {
            margin: 50px 0;
        }

        .section h2 {
            font-size: 32px;
            color: #d8d9da;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #2d2d2d;
        }

        .tech-stack {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 20px 0;
        }

        .tech-badge {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            background: rgba(211, 67, 62, 0.1);
            border: 1px solid #D3433E;
            border-radius: 20px;
            color: #D3433E;
            font-size: 14px;
            font-weight: 600;
            gap: 8px;
        }

        .warning-box {
            background: rgba(255, 152, 0, 0.1);
            border-left: 4px solid #ff9800;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }

        .warning-box h4 {
            color: #ff9800;
            margin-bottom: 10px;
        }

        .info-box {
            background: rgba(211, 67, 62, 0.1);
            border-left: 4px solid #D3433E;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }

        .info-box h4 {
            color: #D3433E;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../../components/docs-sidebar.php'; ?>
    
    <div class="docs-content">
        <div class="docs-header">
            <h1>
                <i class="fas fa-book"></i>
                LabservNET Documentation
            </h1>
            <p>
                Comprehensive monitoring solution für Node Exporter Metriken. 
                Echtzeit-Dashboard, Uptime-Monitoring, historische Daten und mehr.
            </p>
        </div>

        <!-- Tech Stack -->
        <div class="section">
            <h2>Tech Stack</h2>
            <div class="tech-stack">
                <span class="tech-badge"><i class="fab fa-php"></i> PHP 8.1+</span>
                <span class="tech-badge"><i class="fab fa-js"></i> JavaScript ES6</span>
                <span class="tech-badge"><i class="fas fa-database"></i> MySQL / MariaDB</span>
                <span class="tech-badge"><i class="fas fa-chart-line"></i> Chart.js 4.4</span>
                <span class="tech-badge"><i class="fab fa-html5"></i> HTML5</span>
                <span class="tech-badge"><i class="fab fa-css3-alt"></i> CSS3</span>
                <span class="tech-badge"><i class="fas fa-server"></i> nginx</span>
                <span class="tech-badge"><i class="fas fa-tachometer-alt"></i> Prometheus Node Exporter</span>
            </div>
        </div>

        <!-- Quick Start Links -->
        <div class="section">
            <h2>Quick Start</h2>
            <div class="quick-links">
                <a href="/docs/installation" class="quick-link">
                    <i class="fas fa-download"></i>
                    <div class="quick-link-content">
                        <h4>Installation</h4>
                        <p>System aufsetzen und konfigurieren</p>
                    </div>
                </a>
                <a href="/docs/quick-start" class="quick-link">
                    <i class="fas fa-rocket"></i>
                    <div class="quick-link-content">
                        <h4>Quick Start Guide</h4>
                        <p>In 5 Minuten loslegen</p>
                    </div>
                </a>
                <a href="/docs/config/node-exporters" class="quick-link">
                    <i class="fas fa-server"></i>
                    <div class="quick-link-content">
                        <h4>Node Exporter Config</h4>
                        <p>Nodes hinzufügen und konfigurieren</p>
                    </div>
                </a>
                <a href="/docs/config/dashboard" class="quick-link">
                    <i class="fas fa-chart-line"></i>
                    <div class="quick-link-content">
                        <h4>Dashboard Anpassen</h4>
                        <p>Cards, Charts und Tables konfigurieren</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Features -->
        <div class="section">
            <h2>Features</h2>
            <div class="feature-grid">
                <div class="feature-card">
                    <h3><i class="fas fa-chart-line"></i> Echtzeit Monitoring</h3>
                    <p>Live-Metriken von allen Node Exportern mit automatischer Aktualisierung alle 1.5 Sekunden.</p>
                    <ul>
                        <li>CPU, Memory, Disk Usage</li>
                        <li>Network Traffic</li>
                        <li>System Load</li>
                        <li>Prozess-Monitoring</li>
                    </ul>
                </div>

                <div class="feature-card">
                    <h3><i class="fas fa-database"></i> Metriken Sammlung</h3>
                    <p>Automatische Sammlung aller Node Exporter Metriken via Cron Job.</p>
                    <ul>
                        <li>Jede Minute automatisch</li>
                        <li>30 Tage Retention (Standard)</li>
                        <li>Automatisches Cleanup</li>
                        <li>Label-basierte Filterung</li>
                    </ul>
                </div>

                <div class="feature-card">
                    <h3><i class="fas fa-heartbeat"></i> Uptime Monitoring</h3>
                    <p>Überwachung der Verfügbarkeit via ICMP, TCP und UDP mit Downtime-Tracking.</p>
                    <ul>
                        <li>Multi-Protokoll Support</li>
                        <li>Custom Services</li>
                        <li>Uptime Prozente (24h/7d/30d)</li>
                        <li>Downtime History</li>
                    </ul>
                </div>

                <div class="feature-card">
                    <h3><i class="fas fa-th-large"></i> Anpassbare Dashboards</h3>
                    <p>Vollständig konfigurierbare Dashboards per JSON-Config ohne Code-Änderungen.</p>
                    <ul>
                        <li>Metric Cards</li>
                        <li>Charts (Line, Bar, Area)</li>
                        <li>Tabellen</li>
                        <li>Per-Interface Charts</li>
                    </ul>
                </div>

                <div class="feature-card">
                    <h3><i class="fas fa-route"></i> Dynamisches Routing</h3>
                    <p>Flexibles PHP-basiertes Routing-System mit Parameter-Extraktion.</p>
                    <ul>
                        <li>Pattern-basierte Routes</li>
                        <li>URL Parameter Extraktion</li>
                        <li>Clean URLs (/dashboard/node1)</li>
                        <li>Einfach erweiterbar</li>
                    </ul>
                </div>

                <div class="feature-card">
                    <h3><i class="fas fa-paint-brush"></i> Modern Dark Theme</h3>
                    <p>Grafana-inspiriertes Design mit smooth Animations und Loading States.</p>
                    <ul>
                        <li>Dark Theme UI</li>
                        <li>Hover Animations</li>
                        <li>Loading Animations</li>
                        <li>Accent Colors</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Architecture Overview -->
        <div class="section">
            <h2>Architektur</h2>
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> System-Übersicht</h4>
                <p><strong>Frontend:</strong> Vanilla JavaScript mit Chart.js für Visualisierungen</p>
                <p><strong>Backend:</strong> PHP 8.1 mit PDO für Datenbankzugriff</p>
                <p><strong>Daten-Source:</strong> Prometheus Node Exporter (Port 9100)</p>
                <p><strong>Routing:</strong> Custom PHP Router mit Parameter-Extraktion</p>
                <p><strong>Automation:</strong> Cron Jobs für Metrics Collection und Uptime Checks</p>
            </div>

            <div class="feature-card">
                <h3><i class="fas fa-sitemap"></i> Datenfluss</h3>
                <p><strong>1. Node Exporter</strong> → Stellt Metriken auf Port 9100 bereit</p>
                <p><strong>2. Metrics Collector</strong> → Cron Job sammelt alle Metriken (jede Minute)</p>
                <p><strong>3. Database</strong> → Speichert historische Daten mit Labels</p>
                <p><strong>4. API</strong> → Stellt Daten für Dashboard bereit</p>
                <p><strong>5. Dashboard</strong> → Zeigt Live-Daten mit Auto-Refresh an</p>
            </div>
        </div>

        <!-- Getting Help -->
        <div class="section">
            <h2>Hilfe & Support</h2>
            <div class="warning-box">
                <h4><i class="fas fa-question-circle"></i> Probleme?</h4>
                <p>Schau in die <a href="/docs/troubleshooting" style="color: #ff9800; text-decoration: underline;">Troubleshooting-Sektion</a> für häufige Probleme und Lösungen.</p>
            </div>
        </div>

        <!-- Next Steps -->
        <div class="section">
            <h2>Nächste Schritte</h2>
            <div class="quick-links">
                <a href="/docs/installation" class="quick-link">
                    <i class="fas fa-play"></i>
                    <div class="quick-link-content">
                        <h4>Installation starten</h4>
                        <p>System installieren und einrichten</p>
                    </div>
                </a>
                <a href="/docs/config/node-exporters" class="quick-link">
                    <i class="fas fa-cog"></i>
                    <div class="quick-link-content">
                        <h4>Konfiguration</h4>
                        <p>Nodes und Services konfigurieren</p>
                    </div>
                </a>
                <a href="/docs/structure" class="quick-link">
                    <i class="fas fa-folder-tree"></i>
                    <div class="quick-link-content">
                        <h4>Projekt-Struktur</h4>
                        <p>File Structure verstehen</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</body>
</html>
