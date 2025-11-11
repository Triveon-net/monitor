<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Configuration - Docs</title>
    <link rel="stylesheet" href="/public/dashboard.css">
    <link rel="stylesheet" href="/public/docs.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include __DIR__ . '/../../components/docs-sidebar.php'; ?>

    <div class="docs-content">
        <div class="docs-header">
            <h1><i class="fas fa-tachometer-alt"></i> Dashboard Configuration</h1>
            <p>Konfiguriere Cards, Charts und Tables für deine Node Dashboards.</p>
        </div>

        <div class="docs-section">
            <h2>Wichtig: Dashboard Location</h2>
            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> Keine separaten Dashboard-Dateien!</h4>
                <p>Die Dashboard-Konfiguration ist <strong>direkt in <code>/var/www/html/config/node_exporters.json</code></strong> unter jedem Instance!</p>
                <p>Es gibt KEINE separaten JSON-Dateien pro Dashboard!</p>
            </div>
        </div>

        <div class="docs-section">
            <h2>Dashboard Structure</h2>
            
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
            <h2>1. Cards (Metric Cards)</h2>
            <p>Cards zeigen einzelne Metrik-Werte mit Berechnungen an.</p>

            <h3>Card Types</h3>
            <div class="feature-grid">
                <div class="feature-card">
                    <h3><i class="fas fa-square"></i> value</h3>
                    <p>Einfacher Wert mit Formatierung</p>
                </div>
                <div class="feature-card">
                    <h3><i class="fas fa-chart-pie"></i> gauge</h3>
                    <p>Kreis-Anzeige mit Prozent</p>
                </div>
            </div>

            <h3>Card Properties</h3>
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
                            <td><code>type</code></td>
                            <td>string</td>
                            <td>✓</td>
                            <td>"value" oder "gauge"</td>
                        </tr>
                        <tr>
                            <td><code>title</code></td>
                            <td>string</td>
                            <td>✓</td>
                            <td>Anzeige-Titel</td>
                        </tr>
                        <tr>
                            <td><code>icon</code></td>
                            <td>string</td>
                            <td>✓</td>
                            <td>FontAwesome Icon (z.B. "fa-microchip")</td>
                        </tr>
                        <tr>
                            <td><code>metric</code></td>
                            <td>string</td>
                            <td>✓</td>
                            <td>Interne Metrik-ID</td>
                        </tr>
                        <tr>
                            <td><code>calculation</code></td>
                            <td>object</td>
                            <td>✓</td>
                            <td>Berechnungs-Konfiguration</td>
                        </tr>
                        <tr>
                            <td><code>format</code></td>
                            <td>string</td>
                            <td>✗</td>
                            <td>Output-Format (cpu_info, memory_gb, disk_gb, load)</td>
                        </tr>
                        <tr>
                            <td><code>precision</code></td>
                            <td>integer</td>
                            <td>✗</td>
                            <td>Dezimalstellen (default: 1)</td>
                        </tr>
                        <tr>
                            <td><code>unit</code></td>
                            <td>string</td>
                            <td>✗</td>
                            <td>Einheit (%, °C, MB/s, etc.)</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <h3>Calculation Types</h3>
            <div class="table-responsive">
                <table class="docs-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Use Case</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>cpu_info_format</code></td>
                            <td>CPU Usage mit cores count</td>
                            <td>CPU Karte</td>
                        </tr>
                        <tr>
                            <td><code>cpu_percentage</code></td>
                            <td>CPU Usage als Prozent</td>
                            <td>CPU Charts</td>
                        </tr>
                        <tr>
                            <td><code>memory_gb_format</code></td>
                            <td>Memory in GB mit Prozent</td>
                            <td>Memory Karte</td>
                        </tr>
                        <tr>
                            <td><code>filesystem_gb_format</code></td>
                            <td>Disk Usage in GB mit Prozent</td>
                            <td>Disk Karte (mit filter)</td>
                        </tr>
                        <tr>
                            <td><code>direct</code></td>
                            <td>Direkte Werte ohne Berechnung</td>
                            <td>System Load, Uptime</td>
                        </tr>
                        <tr>
                            <td><code>rate</code></td>
                            <td>Berechnet Rate (Bytes/s)</td>
                            <td>Network Traffic</td>
                        </tr>
                        <tr>
                            <td><code>percentage</code></td>
                            <td>Mit Formula berechnen</td>
                            <td>Custom Berechnungen</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <h3>Card Examples</h3>

            <h4>CPU Card</h4>
            <div class="code-block">
                <pre>{
  "type": "value",
  "title": "CPU",
  "icon": "fa-microchip",
  "metric": "cpu_usage",
  "precision": 1,
  "calculation": {
    "type": "cpu_info_format"
  },
  "format": "cpu_info"
}</pre>
            </div>

            <h4>Memory Card</h4>
            <div class="code-block">
                <pre>{
  "type": "value",
  "title": "Memory",
  "icon": "fa-memory",
  "metric": "memory_usage",
  "precision": 1,
  "calculation": {
    "type": "memory_gb_format"
  },
  "format": "memory_gb"
}</pre>
            </div>

            <h4>Disk Card (mit Filter)</h4>
            <div class="code-block">
                <pre>{
  "type": "value",
  "title": "Disk",
  "icon": "fa-hard-drive",
  "metric": "disk_usage",
  "precision": 1,
  "calculation": {
    "type": "filesystem_gb_format",
    "filter": "mountpoint=\"/\""
  },
  "format": "disk_gb"
}</pre>
            </div>

            <h4>System Load Card</h4>
            <div class="code-block">
                <pre>{
  "type": "value",
  "title": "System Load",
  "icon": "fa-chart-line",
  "metric": "system_load",
  "precision": 2,
  "calculation": {
    "type": "direct",
    "metrics": ["node_load1", "node_load5", "node_load15"]
  },
  "format": "load"
}</pre>
            </div>

            <h4>Gauge Card (mit Thresholds)</h4>
            <div class="code-block">
                <pre>{
  "type": "gauge",
  "title": "CPU Usage",
  "icon": "fa-microchip",
  "metric": "cpu_usage",
  "calculation": {
    "type": "cpu_percentage",
    "metrics": ["node_cpu_seconds_total"]
  },
  "unit": "%",
  "thresholds": {
    "warning": 60,
    "critical": 80
  }
}</pre>
            </div>
        </div>

        <div class="docs-section">
            <h2>2. Charts</h2>
            <p>Charts zeigen Zeit-basierte Metriken als Graphen.</p>

            <h3>Chart Properties</h3>
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
                            <td><code>type</code></td>
                            <td>string</td>
                            <td>✓</td>
                            <td>"line" oder "bar"</td>
                        </tr>
                        <tr>
                            <td><code>title</code></td>
                            <td>string</td>
                            <td>✓</td>
                            <td>Chart Titel</td>
                        </tr>
                        <tr>
                            <td><code>metrics</code></td>
                            <td>array</td>
                            <td>✓</td>
                            <td>Array von Metric Objects</td>
                        </tr>
                        <tr>
                            <td><code>yAxis</code></td>
                            <td>object</td>
                            <td>✗</td>
                            <td>Y-Achsen Konfiguration</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <h3>Metric Object Properties</h3>
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
                            <td><code>name</code></td>
                            <td>string</td>
                            <td>✓</td>
                            <td>Name in Legende</td>
                        </tr>
                        <tr>
                            <td><code>calculation</code></td>
                            <td>object</td>
                            <td>✓</td>
                            <td>Berechnung (type, metric, filter)</td>
                        </tr>
                        <tr>
                            <td><code>color</code></td>
                            <td>string</td>
                            <td>✓</td>
                            <td>Hex Color (#D3433E)</td>
                        </tr>
                        <tr>
                            <td><code>fill</code></td>
                            <td>boolean</td>
                            <td>✗</td>
                            <td>Area füllen? (default: false)</td>
                        </tr>
                        <tr>
                            <td><code>unit</code></td>
                            <td>string</td>
                            <td>✗</td>
                            <td>"bytes" oder "bits" für Network</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <h3>Chart Examples</h3>

            <h4>CPU Usage Chart</h4>
            <div class="code-block">
                <pre>{
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
  ],
  "yAxis": {
    "min": 0,
    "max": 100,
    "unit": "%"
  }
}</pre>
            </div>

            <h4>Network Traffic Chart (mit Filter)</h4>
            <div class="code-block">
                <pre>{
  "type": "line",
  "title": "Network Traffic - eth0",
  "metrics": [
    {
      "name": "RX",
      "calculation": {
        "type": "rate",
        "metric": "node_network_receive_bytes_total",
        "filter": "device=\"eth0\""
      },
      "color": "#7eb26d",
      "unit": "bits",
      "fill": false
    },
    {
      "name": "TX",
      "calculation": {
        "type": "rate",
        "metric": "node_network_transmit_bytes_total",
        "filter": "device=\"eth0\""
      },
      "color": "#ff9830",
      "unit": "bits",
      "fill": false
    }
  ],
  "yAxis": {
    "unit": "bits",
    "dynamic": true
  }
}</pre>
            </div>

            <h4>Disk I/O Chart</h4>
            <div class="code-block">
                <pre>{
  "type": "line",
  "title": "Disk I/O",
  "metrics": [
    {
      "name": "Read",
      "calculation": {
        "type": "rate",
        "metric": "node_devstat_bytes_total",
        "filter": "device=\"da0\",type=\"read\""
      },
      "color": "#33b5e5",
      "fill": false
    },
    {
      "name": "Write",
      "calculation": {
        "type": "rate",
        "metric": "node_devstat_bytes_total",
        "filter": "device=\"da0\",type=\"write\""
      },
      "color": "#9954bb",
      "fill": false
    }
  ],
  "yAxis": {
    "unit": "bytes",
    "dynamic": true
  }
}</pre>
            </div>
        </div>

        <div class="docs-section">
            <h2>3. Tables</h2>
            <p>Tables zeigen strukturierte Daten in Tabellen-Form.</p>

            <h3>Table Properties</h3>
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
                            <td><code>title</code></td>
                            <td>string</td>
                            <td>✓</td>
                            <td>Tabellen Titel</td>
                        </tr>
                        <tr>
                            <td><code>icon</code></td>
                            <td>string</td>
                            <td>✗</td>
                            <td>FontAwesome Icon</td>
                        </tr>
                        <tr>
                            <td><code>columns</code></td>
                            <td>array</td>
                            <td>✓</td>
                            <td>Spalten-Definitionen</td>
                        </tr>
                        <tr>
                            <td><code>type</code></td>
                            <td>string</td>
                            <td>✗</td>
                            <td>"system_info" für spezielle System Info Tabelle</td>
                        </tr>
                        <tr>
                            <td><code>metrics</code></td>
                            <td>object</td>
                            <td>✗</td>
                            <td>Metric Mappings für Columns</td>
                        </tr>
                        <tr>
                            <td><code>filter</code></td>
                            <td>string</td>
                            <td>✗</td>
                            <td>Label Filter (z.B. fstype!~"tmpfs")</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <h3>Table Examples</h3>

            <h4>System Info Table</h4>
            <div class="code-block">
                <pre>{
  "title": "System Information",
  "icon": "fa-info-circle",
  "columns": [
    { "header": "Property", "field": "property", "width": "30%" },
    { "header": "Value", "field": "value", "width": "70%" }
  ],
  "type": "system_info"
}</pre>
            </div>

            <h4>Filesystem Usage Table</h4>
            <div class="code-block">
                <pre>{
  "title": "Filesystem Usage",
  "icon": "fa-folder-open",
  "columns": [
    { "header": "Mount Point", "field": "mountpoint", "width": "30%" },
    { "header": "Device", "field": "device", "width": "25%" },
    { "header": "Size", "field": "size", "format": "bytes", "width": "15%" },
    { "header": "Used", "field": "used", "format": "bytes", "width": "15%" },
    { "header": "Usage", "field": "usage", "format": "percentage", "width": "15%" }
  ],
  "metrics": {
    "size": "node_filesystem_size_bytes",
    "avail": "node_filesystem_avail_bytes"
  },
  "filter": "fstype!~\"tmpfs|devtmpfs|overlay\""
}</pre>
            </div>
        </div>

        <div class="docs-section">
            <h2>Filters</h2>
            <p>Filters verwenden Prometheus Label-Matching Syntax:</p>

            <div class="table-responsive">
                <table class="docs-table">
                    <thead>
                        <tr>
                            <th>Filter</th>
                            <th>Description</th>
                            <th>Example</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>label="value"</code></td>
                            <td>Exakte Übereinstimmung</td>
                            <td>device="eth0"</td>
                        </tr>
                        <tr>
                            <td><code>label!="value"</code></td>
                            <td>Nicht gleich</td>
                            <td>device!="lo"</td>
                        </tr>
                        <tr>
                            <td><code>label=~"regex"</code></td>
                            <td>Regex Match</td>
                            <td>device=~"eth.*"</td>
                        </tr>
                        <tr>
                            <td><code>label!~"regex"</code></td>
                            <td>Regex Negation</td>
                            <td>device!~"lo|veth.*"</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="docs-section">
            <h2>FontAwesome Icons</h2>
            <div class="feature-grid">
                <div class="feature-card">
                    <h3>Hardware</h3>
                    <ul>
                        <li><code>fa-microchip</code> - CPU</li>
                        <li><code>fa-memory</code> - RAM</li>
                        <li><code>fa-hard-drive</code> - Disk</li>
                        <li><code>fa-hdd</code> - HDD</li>
                    </ul>
                </div>
                <div class="feature-card">
                    <h3>Network</h3>
                    <ul>
                        <li><code>fa-network-wired</code> - Network</li>
                        <li><code>fa-wifi</code> - Wireless</li>
                        <li><code>fa-globe</code> - Internet</li>
                        <li><code>fa-ethernet</code> - Ethernet</li>
                    </ul>
                </div>
                <div class="feature-card">
                    <h3>System</h3>
                    <ul>
                        <li><code>fa-server</code> - Server</li>
                        <li><code>fa-chart-line</code> - Performance</li>
                        <li><code>fa-info-circle</code> - Info</li>
                        <li><code>fa-folder-open</code> - Files</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="docs-section">
            <h2>Nächste Schritte</h2>
            <div class="quick-links">
                <a href="/docs/config/node-exporters" class="quick-link">
                    <i class="fas fa-server"></i>
                    <div class="quick-link-content">
                        <h4>Node Exporters</h4>
                        <p>Zurück zu Node Config</p>
                    </div>
                </a>
                <a href="/docs" class="quick-link">
                    <i class="fas fa-book"></i>
                    <div class="quick-link-content">
                        <h4>Docs Home</h4>
                        <p>Zur Übersicht</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</body>
</html>
