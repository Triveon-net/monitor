<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LabservNET - Home</title>
    <link rel="stylesheet" href="/public/sidebar.css">
    <link rel="stylesheet" href="/public/dashboard.css">
    <link rel="stylesheet" href="/public/animations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            <div class="loader-text">Loading LabservNET...</div>
            <div class="loading-progress">
                <div class="progress-bar"></div>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/../components/sidebar.php'; ?>
    
    <div class="main-content fade-in">
        <div class="dashboard-header fade-in-down">
            <h1>LabservNET Monitor</h1>
        </div>

        <div class="empty-state scale-in-bounce">
            <div class="empty-icon"><i class="fas fa-rocket"></i></div>
            <h2>Monitoring von fast jedem System bei uns</h2>
            <br>
            <div class="fade-in-up animate-delay-2" style="text-align: left; max-width: 600px; margin: 0 auto; color: var(--text-secondary);">
                <h3 style="color: var(--accent-blue); margin-bottom: 15px;">Features:</h3>
                <ul style="list-style: none; padding: 0;" data-stagger>
                    <li class="stagger-item" style="padding: 8px 0;"><i class="fas fa-chart-line"></i> Echtzeit-Monitoring von allen Werten die Node Exporter ausgibt</li>
                    <li class="stagger-item" style="padding: 8px 0;"><i class="fas fa-chart-bar"></i> Visualisierung mit Charts</li>
                    <br>
                    <li class="stagger-item" style="padding: 8px 0;"><i class="fas fa-server"></i> Multi-Instance Support mit JSON-Konfiguration</li>
                    <li class="stagger-item" style="padding: 8px 0;"><i class="fas fa-table"></i> Tabellen für was auch immer</li>
                </ul>
                <br>
                <h3 class="fade-in-up animate-delay-3">Falls du es selbst benutzen willst, gehe auf Install guide für die Anleitung oder auf Documentation um zu sehen wie die Configs funktionieren</h3>
            </div>
        </div>
    </div>
    
    <script src="/public/animations.js"></script>
</body>
</html>
