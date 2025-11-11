<?php
return [
    // Static routes
    '/' => 'resources/views/pages/index.php',
    'dashboard' => 'resources/views/pages/dashboard.php',
    'uptime' => 'resources/views/pages/uptime.php',
    'docs/installation' => 'resources/views/pages/internal/install-guide.php',
    'docs/configuration' => 'resources/views/pages/internal/docs.php',
    
    // Site parameter Routes
    'dashboard/{node}' => 'resources/views/pages/dashboard.php',
    'uptime/{service}' => 'resources/views/pages/uptime.php',

    // API
    'api/node-exporter' => 'resources/system/api/node_exporter.php',

    // Docs
    'docs' => 'resources/views/pages/internal/docs/index.php',
    'docs/config/node-exporters' => 'resources/views/pages/internal/docs/config-node-exporters.php',
    'docs/config/dashboard' => 'resources/views/pages/internal/docs/config-dashboard.php',
    'docs/config/uptime' => 'resources/views/pages/internal/docs/config-uptime.php',
    'docs/config/database' => 'resources/views/pages/internal/docs/config-database.php',
    'docs/components/metric-cards' => 'resources/views/pages/internal/docs/components-metric-cards.php',
    'docs/components/charts' => 'resources/views/pages/internal/docs/components-charts.php',
    'docs/components/tables' => 'resources/views/pages/internal/docs/components-tables.php',
    'docs/advanced/metrics' => 'resources/views/pages/internal/docs/advanced-metrics.php',
    'docs/advanced/routing' => 'resources/views/pages/internal/docs/advanced-routing.php',
    'docs/advanced/api' => 'resources/views/pages/internal/docs/advanced-api.php',
    'docs/structure' => 'resources/views/pages/internal/docs/file-structure.php',
    'docs/troubleshooting' => 'resources/views/pages/internal/docs/troubleshooting.php',
    'docs/quick-start' => 'resources/views/pages/internal/docs/quick-start.php',
    'docs/installation' => 'resources/views/pages/internal/docs/installation.php'
];