<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Troubleshooting - Docs</title>
    <link rel="stylesheet" href="/public/dashboard.css">
    <link rel="stylesheet" href="/public/docs.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include __DIR__ . '/../../components/docs-sidebar.php'; ?>

    <div class="docs-content">
        <div class="docs-header">
            <h1><i class="fas fa-wrench"></i> Troubleshooting</h1>
            <p>Häufige Probleme und deren Lösungen.</p>
        </div>

        <div class="docs-section">
            <h2>Dashboard Problems</h2>

            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> Dashboard zeigt keine Daten</h4>
                <p><strong>Symptom:</strong> Alle Cards zeigen "N/A" oder "--"</p>
                <p><strong>Ursachen:</strong></p>
                <ul>
                    <li>Node Exporter läuft nicht</li>
                    <li>Firewall blockiert Port 9100</li>
                    <li>Falsche IP/Port in config</li>
                    <li>Instance in node_exporters.json disabled</li>
                </ul>
                <p><strong>Lösung:</strong></p>
                <pre># 1. Node Exporter Status
systemctl status node_exporter

# 2. Port erreichbar?
curl http://localhost:9100/metrics

# 3. Von extern?
curl http://192.168.1.100:9100/metrics

# 4. Config prüfen
cat /var/www/html/config/node_exporters.json

# 5. API testen
curl "http://your-domain.com/resources/system/api/node_exporter.php?action=metrics&node=server1"</pre>
            </div>

            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> Charts zeigen keine Daten</h4>
                <p><strong>Symptom:</strong> Charts sind leer oder "No data"</p>
                <p><strong>Ursachen:</strong></p>
                <ul>
                    <li>Keine historischen Daten</li>
                    <li>metrics_collector läuft nicht</li>
                    <li>Database nicht konfiguriert</li>
                    <li>Falscher metric name in chart config</li>
                </ul>
                <p><strong>Lösung:</strong></p>
                <pre># 1. Database verbindung
php -r "require '/var/www/html/resources/system/database/Database.php'; 
        \$db = Database::getInstance(); 
        echo 'Connected!';"

# 2. Daten in DB?
mysql -u labservnet -p labservnet
SELECT COUNT(*) FROM metrics_history;
SELECT DISTINCT metric_name FROM metrics_history LIMIT 10;

# 3. metrics_collector manuell
php /var/www/html/scripts/metrics_collector.php

# 4. Cron läuft?
crontab -l
tail -f /var/log/cron.log</pre>
            </div>

            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> Dashboard lädt langsam</h4>
                <p><strong>Symptom:</strong> Seitenladezeit > 5 Sekunden</p>
                <p><strong>Ursachen:</strong></p>
                <ul>
                    <li>Zu viele Metrics in Database</li>
                    <li>Keine Datenbank-Indizes</li>
                    <li>Zu viele Dashboard-Charts</li>
                    <li>Node Exporter timeout</li>
                </ul>
                <p><strong>Lösung:</strong></p>
                <pre># 1. Database cleanup
php /var/www/html/scripts/cleanup_database.php 7

# 2. Database size
mysql -u labservnet -p labservnet
SELECT 
  table_name,
  ROUND((data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)'
FROM information_schema.tables
WHERE table_schema = 'labservnet';

# 3. retention_days reduzieren
# /var/www/html/resources/system/database/config.php
'retention_days' => 7,  // statt 30

# 4. Chart count reduzieren
# Weniger charts pro dashboard!</pre>
            </div>
        </div>

        <div class="docs-section">
            <h2>Metrics Collection Problems</h2>

            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> Metrics werden nicht gesammelt</h4>
                <p><strong>Symptom:</strong> metrics_history Tabelle bleibt leer</p>
                <p><strong>Lösung:</strong></p>
                <pre># 1. Manuell ausführen
php /var/www/html/scripts/metrics_collector.php

# 2. Fehler?
php -d display_errors=1 /var/www/html/scripts/metrics_collector.php

# 3. Cron läuft?
crontab -l | grep metrics_collector

# 4. Cron logs
tail -f /var/log/cron.log
tail -f /var/log/syslog | grep CRON

# 5. Cron neu hinzufügen
crontab -e
* * * * * php /var/www/html/scripts/metrics_collector.php >> /var/log/metrics_collector.log 2>&1</pre>
            </div>

            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> Nur einige Nodes werden gesammelt</h4>
                <p><strong>Symptom:</strong> Nur 1-2 von 5 Nodes haben Daten</p>
                <p><strong>Lösung:</strong></p>
                <pre># 1. Config prüfen
cat /var/www/html/config/node_exporters.json | grep "enabled"

# 2. Alle Nodes einzeln testen
curl http://192.168.1.100:9100/metrics
curl http://192.168.1.101:9100/metrics

# 3. Node Exporter Status
# Auf jedem Node:
systemctl status node_exporter

# 4. Firewall auf Nodes
sudo ufw status
sudo ufw allow 9100/tcp</pre>
            </div>

            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> Parse Error beim Sammeln</h4>
                <p><strong>Symptom:</strong> "Failed to parse metrics" im Log</p>
                <p><strong>Lösung:</strong></p>
                <pre># 1. Metric Format prüfen
curl http://192.168.1.100:9100/metrics | head -20

# Korrekt:
# node_cpu_seconds_total{cpu="0",mode="idle"} 123.45

# 2. Node Exporter Version
node_exporter --version

# 3. Update Node Exporter
wget https://github.com/prometheus/node_exporter/releases/download/v1.7.0/node_exporter-1.7.0.linux-amd64.tar.gz
tar xvfz node_exporter-1.7.0.linux-amd64.tar.gz
sudo cp node_exporter-1.7.0.linux-amd64/node_exporter /usr/local/bin/
sudo systemctl restart node_exporter</pre>
            </div>
        </div>

        <div class="docs-section">
            <h2>Uptime Monitoring Problems</h2>

            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> ICMP Checks schlagen fehl</h4>
                <p><strong>Symptom:</strong> Alle ICMP Services zeigen "down"</p>
                <p><strong>Ursache:</strong> Ping benötigt root Rechte oder CAP_NET_RAW</p>
                <p><strong>Lösung:</strong></p>
                <pre># Lösung 1: Capability (empfohlen)
sudo setcap cap_net_raw+ep /usr/bin/ping
which ping  # Pfad prüfen

# Lösung 2: Cron als root
sudo crontab -e
* * * * * /var/www/html/scripts/uptime_checker_wrapper.sh

# Test
ping -c 1 192.168.1.1
echo $?  # Sollte 0 sein</pre>
            </div>

            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> TCP Checks falsch-positiv</h4>
                <p><strong>Symptom:</strong> Service zeigt "up" obwohl down</p>
                <p><strong>Ursache:</strong> Port ist offen, aber Service antwortet nicht</p>
                <p><strong>Lösung:</strong></p>
                <pre># TCP Check testet nur ob Port offen ist!
# Für echte Service-Checks: HTTP Request

# Beispiel: Web Server Check
curl -I http://192.168.1.100
curl -I https://192.168.1.100

# Timeout setzen
curl -I --max-time 5 http://192.168.1.100

# Alternative: use HTTP endpoint statt TCP</pre>
            </div>

            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> Uptime Prozentsatz falsch</h4>
                <p><strong>Symptom:</strong> 24h Uptime zeigt 0% obwohl Service läuft</p>
                <p><strong>Ursache:</strong> Keine oder zu wenig Check-Daten</p>
                <p><strong>Lösung:</strong></p>
                <pre># 1. Checks in DB?
mysql -u labservnet -p labservnet
SELECT node_id, COUNT(*) as checks, status 
FROM uptime_checks 
WHERE timestamp > UNIX_TIMESTAMP() - 86400
GROUP BY node_id, status;

# 2. Cron läuft?
crontab -l | grep uptime_checker

# 3. Manuell ausführen
php /var/www/html/scripts/uptime_checker.php

# 4. 10-Sekunden Wrapper
bash /var/www/html/scripts/uptime_checker_wrapper.sh</pre>
            </div>

            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> Keine Uptime Daten</h4>
                <p><strong>Symptom:</strong> uptime_checks Tabelle ist leer</p>
                <p><strong>Lösung:</strong></p>
                <pre># 1. Config existiert?
ls -la /var/www/html/config/uptime_config.json

# 2. Services enabled?
cat /var/www/html/config/uptime_config.json | grep "enabled"

# 3. Manuell testen
php /var/www/html/scripts/uptime_checker.php

# 4. Fehler ausgeben
php -d display_errors=1 /var/www/html/scripts/uptime_checker.php

# 5. Database config
cat /var/www/html/resources/system/database/config.php</pre>
            </div>
        </div>

        <div class="docs-section">
            <h2>Database Problems</h2>

            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> Connection refused (MySQL)</h4>
                <p><strong>Symptom:</strong> Can't connect to MySQL server</p>
                <p><strong>Lösung:</strong></p>
                <pre># 1. MySQL läuft?
sudo systemctl status mysql
sudo systemctl start mysql

# 2. Port offen?
sudo netstat -tlnp | grep 3306
sudo ss -tlnp | grep 3306

# 3. Firewall
sudo ufw status
sudo ufw allow 3306/tcp

# 4. Remote: bind-address
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
# bind-address = 0.0.0.0
sudo systemctl restart mysql</pre>
            </div>

            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> Access denied for user</h4>
                <p><strong>Symptom:</strong> Access denied for user 'labservnet'@'localhost'</p>
                <p><strong>Lösung:</strong></p>
                <pre>mysql -u root -p

-- User prüfen
SELECT user, host FROM mysql.user WHERE user='labservnet';

-- Privileges prüfen
SHOW GRANTS FOR 'labservnet'@'localhost';

-- User neu erstellen
DROP USER IF EXISTS 'labservnet'@'localhost';
CREATE USER 'labservnet'@'localhost' IDENTIFIED BY 'PASSWORD';
GRANT ALL PRIVILEGES ON labservnet.* TO 'labservnet'@'localhost';
FLUSH PRIVILEGES;

-- Von remote?
CREATE USER 'labservnet'@'%' IDENTIFIED BY 'PASSWORD';
GRANT ALL PRIVILEGES ON labservnet.* TO 'labservnet'@'%';
FLUSH PRIVILEGES;</pre>
            </div>

            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> SQLite database locked</h4>
                <p><strong>Symptom:</strong> database is locked</p>
                <p><strong>Lösung:</strong></p>
                <pre># 1. Rechte prüfen
ls -la /var/www/html/resources/system/database/monitoring.db

# 2. Owner setzen
sudo chown www-data:www-data /var/www/html/resources/system/database/monitoring.db
sudo chmod 644 /var/www/html/resources/system/database/monitoring.db

# 3. Directory schreibbar
sudo chmod 755 /var/www/html/resources/system/database/

# 4. WAL Mode aktivieren (bessere Concurrency)
sqlite3 /var/www/html/resources/system/database/monitoring.db
PRAGMA journal_mode=WAL;
.quit</pre>
            </div>

            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> Database zu groß</h4>
                <p><strong>Symptom:</strong> monitoring.db > 1 GB</p>
                <p><strong>Lösung:</strong></p>
                <pre># 1. Größe prüfen
du -h /var/www/html/resources/system/database/monitoring.db

# MySQL
mysql -u labservnet -p labservnet
SELECT 
  table_name,
  ROUND((data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)',
  table_rows
FROM information_schema.tables
WHERE table_schema = 'labservnet'
ORDER BY (data_length + index_length) DESC;

# 2. Cleanup old data
php /var/www/html/scripts/cleanup_database.php 7

# 3. retention_days reduzieren
# /var/www/html/resources/system/database/config.php
'retention_days' => 7,

# 4. Vacuum (SQLite)
sqlite3 /var/www/html/resources/system/database/monitoring.db "VACUUM;"</pre>
            </div>
        </div>

        <div class="docs-section">
            <h2>Configuration Problems</h2>

            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> JSON Syntax Error</h4>
                <p><strong>Symptom:</strong> Dashboard zeigt nichts oder PHP Error</p>
                <p><strong>Lösung:</strong></p>
                <pre># JSON validieren
cat /var/www/html/config/node_exporters.json | python3 -m json.tool

# Oder mit jq
cat /var/www/html/config/node_exporters.json | jq .

# Häufige Fehler:
# - Fehlendes Komma
# - Trailing Comma (letztes Element)
# - Nicht escaped quotes
# - Fehlende Klammer

# Online Validator
# https://jsonlint.com/</pre>
            </div>

            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> Dashboard Config wird nicht geladen</h4>
                <p><strong>Symptom:</strong> Cards/Charts fehlen obwohl in config definiert</p>
                <p><strong>Lösung:</strong></p>
                <pre># 1. Dashboard ist IN node_exporters.json!
cat /var/www/html/config/node_exporters.json | jq '.instances[0].dashboard'

# 2. Richtige Struktur?
{
  "instances": [{
    "id": "server1",
    "dashboard": {
      "cards": [...],
      "charts": [...],
      "tables": [...]
    }
  }]
}

# 3. Cache löschen (Browser)
Ctrl + Shift + R

# 4. PHP Errors?
tail -f /var/log/nginx/error.log</pre>
            </div>

            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> Metric nicht gefunden</h4>
                <p><strong>Symptom:</strong> Card zeigt "N/A" obwohl andere funktionieren</p>
                <p><strong>Lösung:</strong></p>
                <pre># 1. Metric name prüfen
curl http://localhost:9100/metrics | grep "metric_name"

# Beispiel:
curl http://localhost:9100/metrics | grep "node_cpu_seconds"

# 2. Calculation type korrekt?
# Calculation types:
# - cpu_info_format
# - memory_gb_format
# - filesystem_gb_format
# - cpu_percentage
# - direct
# - rate
# - percentage

# 3. Filter erforderlich?
# filesystem_gb_format benötigt: mountpoint="/"

# 4. Prometheus Syntax
curl http://localhost:9100/metrics | grep "device"
# Labels: device="eth0"</pre>
            </div>
        </div>

        <div class="docs-section">
            <h2>Performance Problems</h2>

            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> Hohe CPU Last</h4>
                <p><strong>Symptom:</strong> PHP oder MySQL verbraucht > 50% CPU</p>
                <p><strong>Lösung:</strong></p>
                <pre># 1. Prozesse identifizieren
top -c
htop

# 2. MySQL Queries
mysql -u root -p
SHOW PROCESSLIST;
SHOW FULL PROCESSLIST;

# 3. Slow Query Log aktivieren
# /etc/mysql/mysql.conf.d/mysqld.cnf
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow-query.log
long_query_time = 2

# 4. Cron Intervall erhöhen
# statt jede Minute:
*/5 * * * * php /var/www/html/scripts/metrics_collector.php

# 5. Weniger Nodes monitoren
# nodes in node_exporters.json auf "enabled": false setzen</pre>
            </div>

            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> Hoher RAM Verbrauch</h4>
                <p><strong>Symptom:</strong> System verbraucht > 80% RAM</p>
                <p><strong>Lösung:</strong></p>
                <pre># 1. RAM Verbrauch
free -h
ps aux --sort=-%mem | head -10

# 2. MySQL optimieren
# /etc/mysql/mysql.conf.d/mysqld.cnf
innodb_buffer_pool_size = 512M  # statt 2G
query_cache_size = 32M          # statt 128M

# 3. PHP Memory Limit
# /etc/php/8.1/fpm/php.ini
memory_limit = 128M

sudo systemctl restart php8.1-fpm

# 4. Database cleanup
php /var/www/html/scripts/cleanup_database.php 7

# 5. Swap aktivieren
sudo fallocate -l 2G /swapfile
sudo chmod 600 /swapfile
sudo mkswap /swapfile
sudo swapon /swapfile</pre>
            </div>
        </div>

        <div class="docs-section">
            <h2>General Debugging</h2>

            <h3>PHP Error Logging</h3>
            <div class="code-block">
                <pre># PHP Errors anzeigen
php -d display_errors=1 /var/www/html/scripts/metrics_collector.php

# PHP Error Log
tail -f /var/log/php8.1-fpm.log
tail -f /var/log/nginx/error.log

# Error Reporting aktivieren
# /etc/php/8.1/fpm/php.ini
display_errors = On
error_reporting = E_ALL
log_errors = On
error_log = /var/log/php_errors.log

sudo systemctl restart php8.1-fpm</pre>
            </div>

            <h3>nginx Debugging</h3>
            <div class="code-block">
                <pre># nginx Error Log
tail -f /var/log/nginx/error.log
tail -f /var/log/nginx/access.log

# nginx Config Test
sudo nginx -t

# nginx neu laden
sudo systemctl reload nginx

# nginx Status
sudo systemctl status nginx

# Logs mit Timestamp
tail -f /var/log/nginx/access.log | while read line; do echo "$(date): $line"; done</pre>
            </div>

            <h3>API Testing</h3>
            <div class="code-block">
                <pre># Alle Instances
curl "http://your-domain.com/resources/system/api/node_exporter.php?action=instances"

# Metrics für Node
curl "http://your-domain.com/resources/system/api/node_exporter.php?action=metrics&node=server1"

# Uptime Services
curl "http://your-domain.com/resources/system/api/uptime.php?action=services"

# Uptime History
curl "http://your-domain.com/resources/system/api/uptime.php?action=history&node=service_web-server&hours=24"

# Mit Headers
curl -v "http://your-domain.com/resources/system/api/node_exporter.php?action=instances"</pre>
            </div>

            <h3>File Permissions</h3>
            <div class="code-block">
                <pre># Alle Permissions prüfen
ls -laR /var/www/html/

# Config Files
sudo chown -R www-data:www-data /var/www/html/config/
sudo chmod 644 /var/www/html/config/*.json

# Scripts
sudo chown -R www-data:www-data /var/www/html/scripts/
sudo chmod 755 /var/www/html/scripts/*.php
sudo chmod 755 /var/www/html/scripts/*.sh

# Database
sudo chown www-data:www-data /var/www/html/resources/system/database/
sudo chmod 755 /var/www/html/resources/system/database/

# Web Root
sudo chown -R www-data:www-data /var/www/html/
sudo find /var/www/html/ -type f -exec chmod 644 {} \;
sudo find /var/www/html/ -type d -exec chmod 755 {} \;</pre>
            </div>
        </div>

        <div class="docs-section">
            <h2>Getting Help</h2>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Informationen sammeln</h4>
                <p>Beim Melden von Problemen bitte folgende Infos bereitstellen:</p>
                <ul>
                    <li><strong>System:</strong> OS Version, PHP Version, MySQL/SQLite</li>
                    <li><strong>Symptom:</strong> Was funktioniert nicht?</li>
                    <li><strong>Config:</strong> Relevante Config-Files (ohne Passwörter!)</li>
                    <li><strong>Logs:</strong> Error Logs, Cron Logs</li>
                    <li><strong>Tests:</strong> Was wurde bereits versucht?</li>
                </ul>
            </div>

            <div class="code-block">
                <pre># System Info sammeln
echo "=== System Info ===" > debug_info.txt
cat /etc/os-release >> debug_info.txt
php --version >> debug_info.txt
mysql --version >> debug_info.txt

echo -e "\n=== PHP Modules ===" >> debug_info.txt
php -m >> debug_info.txt

echo -e "\n=== Cron Jobs ===" >> debug_info.txt
crontab -l >> debug_info.txt

echo -e "\n=== Recent Errors ===" >> debug_info.txt
tail -50 /var/log/nginx/error.log >> debug_info.txt

cat debug_info.txt</pre>
            </div>
        </div>

        <div class="docs-section">
            <h2>Nächste Schritte</h2>
            <div class="quick-links">
                <a href="/docs/quick-start" class="quick-link">
                    <i class="fas fa-rocket"></i>
                    <div class="quick-link-content">
                        <h4>Quick Start</h4>
                        <p>Neu starten</p>
                    </div>
                </a>
                <a href="/docs/installation" class="quick-link">
                    <i class="fas fa-download"></i>
                    <div class="quick-link-content">
                        <h4>Installation</h4>
                        <p>Vollständige Anleitung</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</body>
</html>
