<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - Docs</title>
    <link rel="stylesheet" href="/public/dashboard.css">
    <link rel="stylesheet" href="/public/docs.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include __DIR__ . '/../../components/docs-sidebar.php'; ?>

    <div class="docs-content">
        <div class="docs-header">
            <h1><i class="fas fa-download"></i> Installation</h1>
            <p>Vollständige Installation von LabservNET Monitoring.</p>
        </div>

        <div class="docs-section">
            <h2>System Requirements</h2>
            
            <div class="table-responsive">
                <table class="docs-table">
                    <thead>
                        <tr>
                            <th>Component</th>
                            <th>Required</th>
                            <th>Recommended</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>OS</strong></td>
                            <td>Linux (Debian/Ubuntu)</td>
                            <td>Ubuntu 22.04 LTS</td>
                        </tr>
                        <tr>
                            <td><strong>Web Server</strong></td>
                            <td>nginx oder Apache</td>
                            <td>nginx latest</td>
                        </tr>
                        <tr>
                            <td><strong>PHP</strong></td>
                            <td>PHP 8.0+</td>
                            <td>PHP 8.2</td>
                        </tr>
                        <tr>
                            <td><strong>Database</strong></td>
                            <td>SQLite oder MySQL 5.7+</td>
                            <td>MySQL 8.0+</td>
                        </tr>
                        <tr>
                            <td><strong>RAM</strong></td>
                            <td>512 MB</td>
                            <td>2 GB</td>
                        </tr>
                        <tr>
                            <td><strong>Disk</strong></td>
                            <td>1 GB</td>
                            <td>10 GB</td>
                        </tr>
                        <tr>
                            <td><strong>Network</strong></td>
                            <td>100 Mbps</td>
                            <td>1 Gbps</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="docs-section">
            <h2>Step 1: System Packages</h2>

            <h3>Debian / Ubuntu</h3>
            <div class="code-block">
                <pre>sudo apt update
sudo apt upgrade -y

# Web Server & PHP
sudo apt install -y nginx php8.2-fpm php8.2-cli php8.2-mysql php8.2-sqlite3 \
    php8.2-curl php8.2-json php8.2-mbstring php8.2-xml

# Optional: MySQL
sudo apt install -y mysql-server

# Tools
sudo apt install -y git curl wget</pre>
            </div>

            <h3>CentOS / RHEL</h3>
            <div class="code-block">
                <pre>sudo yum update -y

# Web Server & PHP
sudo yum install -y nginx php php-fpm php-mysqlnd php-sqlite3 \
    php-curl php-json php-mbstring php-xml

# Optional: MySQL
sudo yum install -y mysql-server

# Tools
sudo yum install -y git curl wget</pre>
            </div>
        </div>

        <div class="docs-section">
            <h2>Step 2: MySQL Setup (Optional)</h2>
            <p>Überspringe diesen Schritt wenn du SQLite verwenden möchtest.</p>

            <h3>MySQL Installation</h3>
            <div class="code-block">
                <pre># MySQL starten
sudo systemctl start mysql
sudo systemctl enable mysql

# Sicherheit konfigurieren
sudo mysql_secure_installation</pre>
            </div>

            <h3>Database & User erstellen</h3>
            <div class="code-block">
                <pre>mysql -u root -p

CREATE DATABASE labservnet CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'labservnet'@'localhost' IDENTIFIED BY 'CHANGE_THIS_PASSWORD';
GRANT ALL PRIVILEGES ON labservnet.* TO 'labservnet'@'localhost';
FLUSH PRIVILEGES;
EXIT;</pre>
            </div>
        </div>

        <div class="docs-section">
            <h2>Step 3: Installation</h2>

            <h3>Code herunterladen</h3>
            <div class="code-block">
                <pre># Von GitHub
cd /var/www/
sudo git clone https://github.com/yourusername/labservnet.git html

# Oder als ZIP
wget https://github.com/yourusername/labservnet/archive/main.zip
sudo unzip main.zip -d /var/www/
sudo mv /var/www/labservnet-main /var/www/html</pre>
            </div>

            <h3>Permissions setzen</h3>
            <div class="code-block">
                <pre>sudo chown -R www-data:www-data /var/www/html/
sudo find /var/www/html/ -type f -exec chmod 644 {} \;
sudo find /var/www/html/ -type d -exec chmod 755 {} \;
sudo chmod 755 /var/www/html/scripts/*.sh</pre>
            </div>
        </div>

        <div class="docs-section">
            <h2>Step 4: nginx Configuration</h2>

            <h3>nginx Config erstellen</h3>
            <div class="code-block">
                <pre>sudo nano /etc/nginx/sites-available/labservnet</pre>
            </div>

            <h3>Complete nginx Config</h3>
            <div class="code-block">
                <pre>server {
    listen 80;
    server_name your-domain.com;
    root /var/www/html;
    index index.php;

    # Logging
    access_log /var/log/nginx/labservnet_access.log;
    error_log /var/log/nginx/labservnet_error.log;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    # Main location
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP Processing
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Static files caching
    location ~* \.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    # Deny access to sensitive files
    location ~ /\. {
        deny all;
    }

    location ~ /(config|scripts|resources/system/database)/.*\.(json|php|db)$ {
        deny all;
    }
}</pre>
            </div>

            <h3>Config aktivieren</h3>
            <div class="code-block">
                <pre># Symlink erstellen
sudo ln -s /etc/nginx/sites-available/labservnet /etc/nginx/sites-enabled/

# Default site deaktivieren
sudo rm /etc/nginx/sites-enabled/default

# Config testen
sudo nginx -t

# nginx neu starten
sudo systemctl restart nginx
sudo systemctl enable nginx</pre>
            </div>

            <h3>SSL mit Let's Encrypt (Optional)</h3>
            <div class="code-block">
                <pre># Certbot installieren
sudo apt install -y certbot python3-certbot-nginx

# Certificate generieren
sudo certbot --nginx -d your-domain.com

# Auto-renewal testen
sudo certbot renew --dry-run</pre>
            </div>
        </div>

        <div class="docs-section">
            <h2>Step 5: Database Configuration</h2>

            <h3>Option A: SQLite (Einfach)</h3>
            <div class="code-block">
                <pre>sudo nano /var/www/html/resources/system/database/config.php</pre>
            </div>
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

            <h3>Option B: MySQL (Production)</h3>
            <div class="code-block">
                <pre>&lt;?php

return [
    'driver' => 'mysql',
    'host' => 'localhost',
    'port' => 3306,
    'database' => 'labservnet',
    'username' => 'labservnet',
    'password' => 'CHANGE_THIS_PASSWORD',
    'charset' => 'utf8mb4',
    'retention_days' => 30,
    'auto_cleanup' => true
];</pre>
            </div>

            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Automatische Tabellen</h4>
                <p>Tabellen werden automatisch beim ersten Zugriff erstellt!</p>
            </div>
        </div>

        <div class="docs-section">
            <h2>Step 6: Node Exporter Installation</h2>
            <p>Node Exporter muss auf jedem zu überwachenden Server installiert werden.</p>

            <h3>Installation</h3>
            <div class="code-block">
                <pre>
# Repositorys updaten    
apt update

# node exporter installieren
sudo apt-get install prometheus-node-exporter

# verifizieren das Node exporter erreichbar ist
ss -tlpn | grep 0.0.0.0:9100
                </pre>
            </div>

            <h3>Firewall konfigurieren</h3>
            <div class="code-block">
                <pre># UFW
sudo ufw allow 9100/tcp

# iptables
sudo iptables -A INPUT -p tcp --dport 9100 -j ACCEPT
sudo netfilter-persistent save

# oder

iptables-persistent save > /etc/iptables/rules.v4

# firewalld
sudo firewall-cmd --permanent --add-port=9100/tcp
sudo firewall-cmd --reload</pre>
            </div>
        </div>

        <div class="docs-section">
            <h2>Step 7: Node Exporter Configuration</h2>

            <div class="code-block">
                <pre>sudo nano /var/www/html/config/node_exporters.json</pre>
            </div>

            <div class="code-block">
                <pre>{
  "instances": [
    {
      "id": "server1",
      "title": "Main Server",
      "host": "localhost",
      "port": 9100,
      "enabled": true,
      "tags": ["production"],
      "dashboard": {
        "cards": [
          {
            "type": "value",
            "title": "CPU Usage",
            "icon": "fa-microchip",
            "metric": "node_cpu_seconds_total",
            "calculation": "cpu_info_format",
            "format": "text"
          },
          {
            "type": "value",
            "title": "Memory",
            "icon": "fa-memory",
            "metric": "node_memory_MemTotal_bytes",
            "calculation": "memory_gb_format",
            "format": "text"
          },
          {
            "type": "value",
            "title": "Disk Usage",
            "icon": "fa-hard-drive",
            "metric": "node_filesystem_size_bytes",
            "calculation": "filesystem_gb_format",
            "filter": "mountpoint=\"/\"",
            "format": "text"
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
                  "metric": "node_cpu_seconds_total"
                },
                "color": "#D3433E",
                "unit": "%",
                "fill": false
              }
            ],
            "yAxis": {
              "min": 0,
              "max": 100,
              "unit": "%"
            }
          }
        ],
        "tables": [
          {
            "type": "system_info",
            "title": "System Information",
            "icon": "fa-info-circle"
          }
        ]
      }
    }
  ]
}</pre>
            </div>
        </div>

        <div class="docs-section">
            <h2>Step 8: Uptime Monitoring (Optional)</h2>

            <h3>Config erstellen</h3>
            <div class="code-block">
                <pre>sudo nano /var/www/html/config/uptime_config.json</pre>
            </div>

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
      "name": "Main Router",
      "host": "192.168.1.1",
      "protocol": "icmp",
      "enabled": true,
      "icon": "fa-network-wired",
      "tags": ["network"]
    },
    {
      "id": "webserver",
      "name": "Web Server",
      "host": "localhost",
      "port": 80,
      "protocol": "tcp",
      "enabled": true,
      "icon": "fa-globe",
      "tags": ["web"]
    }
  ]
}</pre>
            </div>

            <h3>ICMP Capability</h3>
            <div class="code-block">
                <pre>sudo setcap cap_net_raw+ep /usr/bin/ping</pre>
            </div>
        </div>

        <div class="docs-section">
            <h2>Step 9: Cron Jobs Setup</h2>

            <div class="code-block">
                <pre>crontab -e</pre>
            </div>

            <h3>Metrics Collection (jeden Minute)</h3>
            <div class="code-block">
                <pre>* * * * * php /var/www/html/scripts/metrics_collector.php >> /var/log/metrics_collector.log 2>&1</pre>
            </div>

            <h3>Uptime Checks (alle 10 Sekunden)</h3>
            <div class="code-block">
                <pre>* * * * * /var/www/html/scripts/uptime_checker_wrapper.sh >> /var/log/uptime_checker.log 2>&1</pre>
            </div>

            <h3>Database Cleanup (täglich um 3 Uhr)</h3>
            <div class="code-block">
                <pre>0 3 * * * php /var/www/html/scripts/cleanup_database.php 30 >> /var/log/db_cleanup.log 2>&1</pre>
            </div>

            <h3>Log Rotation</h3>
            <div class="code-block">
                <pre>sudo nano /etc/logrotate.d/labservnet</pre>
            </div>
            <div class="code-block">
                <pre>/var/log/metrics_collector.log /var/log/uptime_checker.log /var/log/db_cleanup.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
}</pre>
            </div>
        </div>

        <div class="docs-section">
            <h2>Step 10: First Access</h2>

            <h3>Web Interface öffnen</h3>
            <div class="code-block">
                <pre>http://your-domain.com
https://your-domain.com  # Mit SSL</pre>
            </div>

            <h3>Verification Steps</h3>
            <div class="feature-grid">
                <div class="feature-card">
                    <h3><i class="fas fa-tachometer-alt"></i> Dashboard</h3>
                    <p>Siehst du die Cards mit Daten?</p>
                </div>
                <div class="feature-card">
                    <h3><i class="fas fa-chart-line"></i> Charts</h3>
                    <p>Werden Charts nach 2-3 Min angezeigt?</p>
                </div>
                <div class="feature-card">
                    <h3><i class="fas fa-heartbeat"></i> Uptime</h3>
                    <p>Funktioniert Uptime Monitoring?</p>
                </div>
                <div class="feature-card">
                    <h3><i class="fas fa-check-circle"></i> API</h3>
                    <p>API Endpoints erreichbar?</p>
                </div>
            </div>
        </div>


            <h3>Monitoring testen</h3>
            <div class="code-block">
                <pre># Metrics Collection testen
php /var/www/html/scripts/metrics_collector.php

# Uptime Checks testen
php /var/www/html/scripts/uptime_checker.php

# Database Daten prüfen
mysql -u labservnet -p labservnet
SELECT COUNT(*) FROM metrics_history;
SELECT COUNT(*) FROM uptime_checks;

# API testen
curl "http://localhost/resources/system/api/node_exporter.php?action=instances"</pre>
            </div>

            <h3>Backup Setup</h3>
            <div class="code-block">
                <pre># Backup Script
sudo nano /root/backup_labservnet.sh</pre>
            </div>
            <div class="code-block">
                <pre>#!/bin/bash
DATE=$(date +%Y%m%d)
BACKUP_DIR="/backup/labservnet"

mkdir -p $BACKUP_DIR

# Config Files
tar -czf $BACKUP_DIR/config_$DATE.tar.gz /var/www/html/config/

# Database
mysqldump -u labservnet -pPASSWORD labservnet | gzip > $BACKUP_DIR/database_$DATE.sql.gz

# Alte Backups löschen (> 30 Tage)
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete
find $BACKUP_DIR -name "*.sql.gz" -mtime +30 -delete

echo "Backup completed: $DATE"</pre>
            </div>
            <div class="code-block">
                <pre># Executable machen
sudo chmod +x /root/backup_labservnet.sh

# Cron (täglich um 1 Uhr)
sudo crontab -e
0 1 * * * /root/backup_labservnet.sh >> /var/log/backup.log 2>&1</pre>
            </div>
        </div>
    </div>
</body>
</html>
