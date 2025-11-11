<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LabservNET - Monitor</title>
    <link rel="stylesheet" href="/public/sidebar.css">
    <link rel="stylesheet" href="/public/dashboard.css">
    <link rel="stylesheet" href="/public/animations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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
            <div class="loader-text">Loading Dashboard...</div>
            <div class="loading-progress">
                <div class="progress-bar"></div>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/../components/sidebar.php'; ?>
    
    <div class="main-content fade-in">
        <div class="dashboard-header fade-in-down">
            <h1 id="dashboard-title">LabservNET Monitor</h1>
            <div class="header-controls">
                <button id="refresh-btn" class="btn-refresh btn-ripple hover-scale">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
                <div class="auto-refresh">
                    <label>
                        <input type="checkbox" id="auto-refresh" checked>
                        Auto-refresh (5s)
                    </label>
                </div>
            </div>
        </div>

        <!-- Dynamic Content will be inserted here -->
        <div id="dashboard-content">
            <!-- Metric Cards -->
            <div id="metric-cards" class="metrics-grid" data-stagger></div>
            
            <!-- Charts -->
            <div id="charts-container" class="charts-grid"></div>
            
            <!-- Tables -->
            <div id="tables-container"></div>
        </div>

        <!-- Empty State -->
        <div id="empty-state" class="empty-state fade-in-up">
            <i class="fas fa-chart-line fa-3x"></i>
            <h2>Wähle einen Node</h2>
            <p>Wähle einen Node Exporter aus dem Dropdown-Menü oben, um die Metriken anzuzeigen.</p>
        </div>
    </div>

    <script src="/public/animations.js"></script>
    <script src="/public/dashboard-dynamic.js"></script>
</body>
</html>
