<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LabservNET - Uptime Status</title>
    <link rel="stylesheet" href="/public/sidebar.css">
    <link rel="stylesheet" href="/public/dashboard.css">
    <link rel="stylesheet" href="/public/animations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .status-card {
            background: var(--bg-secondary);
            border-radius: 12px;
            padding: 20px;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }
        
        .status-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .status-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .status-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 16px;
            font-weight: 600;
        }
        
        .status-title i {
            font-size: 18px;
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-badge.up {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
        }
        
        .status-badge.down {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }
        
        .status-badge.unknown {
            background: rgba(156, 163, 175, 0.2);
            color: #9ca3af;
        }
        
        .status-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 15px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        
        .info-label {
            font-size: 11px;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-value {
            font-size: 14px;
            font-weight: 600;
        }
        
        .response-time {
            display: flex;
            align-items: center;
            gap: 5px;
            color: var(--text-secondary);
            font-size: 13px;
            margin-top: 10px;
        }
        
        .uptime-bars {
            display: flex;
            gap: 8px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid var(--border-color);
        }
        
        .uptime-bar {
            flex: 1;
            text-align: center;
        }
        
        .uptime-percentage {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 4px;
        }
        
        .uptime-label {
            font-size: 11px;
            color: var(--text-secondary);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .page-section-title {
            font-size: 20px;
            font-weight: 700;
        }
        
        .last-update {
            color: var(--text-secondary);
            font-size: 13px;
        }
        
        .loading {
            text-align: center;
            padding: 40px;
            color: var(--text-secondary);
        }
        
        .loading i {
            font-size: 32px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .service-details {
            font-size: 12px;
            color: var(--text-secondary);
            margin-top: 8px;
        }
        
        .service-tags {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            margin-top: 10px;
        }
        
        .service-tag {
            background: var(--bg-primary);
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            color: var(--text-secondary);
        }
    </style>
</head>
<body>
    <!-- Page Loader -->
    <div id="page-loader" class="page-loader">
        <div class="loader-content">
            <div class="spinner">
                <div class="spinner-ring"></div>
                <div class="spinner-ring"></div>
                <div class="spinner-ring"></div>
            </div>
            <div class="loader-text">Loading Uptime Status...</div>
            <div class="loading-progress">
                <div class="progress-bar"></div>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/../components/sidebar.php'; ?>
    
    <div class="main-content fade-in">
        <div class="dashboard-header fade-in-down">
            <h1><i class="fas fa-heartbeat status-pulse"></i> Uptime Status</h1>
            <div class="last-update">Letztes Update: <span id="last-update">-</span></div>
        </div>

        <div id="services-grid" class="status-grid" data-stagger>
            <div class="loading skeleton">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Lade Services...</p>
            </div>
        </div>
    </div>

    <script>
        class UptimeMonitor {
            constructor() {
                this.updateInterval = 10000; // 10 Sekunden
                this.init();
            }

            init() {
                this.loadStatus();
                setInterval(() => this.loadStatus(), this.updateInterval);
            }

            async loadStatus() {
                try {
                    // Load services
                    const servicesResponse = await fetch('/resources/system/api/uptime.php?action=services');
                    const servicesData = await servicesResponse.json();

                    if (servicesData.success) {
                        this.renderServices(servicesData.data);
                    }

                    // Update timestamp
                    document.getElementById('last-update').textContent = new Date().toLocaleTimeString('de-DE');
                } catch (error) {
                    console.error('Error loading status:', error);
                    document.getElementById('services-grid').innerHTML = '<div class="loading"><p style="color: #ef4444;">Fehler beim Laden der Services</p></div>';
                }
            }

            renderServices(services) {
                const grid = document.getElementById('services-grid');

                if (services.length === 0) {
                    grid.innerHTML = '<div class="loading"><p>Keine Services konfiguriert</p></div>';
                    return;
                }

                grid.innerHTML = services.map(service => {
                    const status = service.status;
                    const isUp = status.current_status === 'up';
                    const statusClass = status.current_status === 'unknown' ? 'unknown' : (isUp ? 'up' : 'down');
                    
                    return `
                        <div class="status-card">
                            <div class="status-header">
                                <div class="status-title">
                                    <i class="fas ${service.icon || 'fa-circle'}"></i>
                                    ${service.name}
                                </div>
                                <div class="status-badge ${statusClass}">
                                    ${status.current_status}
                                </div>
                            </div>
                            
                            
                            ${service.tags && service.tags.length > 0 ? `
                                <div class="service-tags">
                                    ${service.tags.map(tag => `<span class="service-tag">${tag}</span>`).join('')}
                                </div>
                            ` : ''}
                            
                            ${status.last_check ? `
                                <div class="response-time">
                                    <i class="fas fa-clock"></i>
                                    Letzter Check: ${new Date(status.last_check).toLocaleString('de-DE')}
                                </div>
                            ` : ''}
                            
                            <div class="uptime-bars">
                                <div class="uptime-bar">
                                    <div class="uptime-percentage">${status.uptime_24h !== null ? status.uptime_24h.toFixed(2) + '%' : '-'}</div>
                                    <div class="uptime-label">24 Stunden</div>
                                </div>
                                <div class="uptime-bar">
                                    <div class="uptime-percentage">${status.uptime_7d !== null ? status.uptime_7d.toFixed(2) + '%' : '-'}</div>
                                    <div class="uptime-label">7 Tage</div>
                                </div>
                                <div class="uptime-bar">
                                    <div class="uptime-percentage">${status.uptime_30d !== null ? status.uptime_30d.toFixed(2) + '%' : '-'}</div>
                                    <div class="uptime-label">30 Tage</div>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');
            }
        }

        // Initialize
        new UptimeMonitor();
    </script>
    <script src="/public/animations.js"></script>
</body>
</html>
