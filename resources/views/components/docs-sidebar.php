<!DOCTYPE html>
<link href="/public/sidebar.css">
<style>
.docs-sidebar {
    position: fixed;
    left: 0;
    top: 0;
    height: 100vh;
    width: 280px;
    background: #181b1f;
    border-right: 1px solid #2d2d2d;
    overflow-y: auto;
    z-index: 1000;
}

.docs-sidebar .logo {
    padding: 20px;
    text-align: center;
    border-bottom: 1px solid #2d2d2d;
}

.docs-sidebar .logo h1 {
    color: #D3433E;
    font-size: 24px;
    margin: 0;
    font-weight: 600;
}

.docs-sidebar .logo p {
    color: #9fa0a3;
    font-size: 12px;
    margin-top: 5px;
}

.docs-nav {
    padding: 20px 0;
}

.docs-category {
    margin-bottom: 20px;
}

.docs-category-title {
    padding: 10px 20px;
    color: #D3433E;
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-left: 3px solid #D3433E;
    background: rgba(211, 67, 62, 0.1);
}

.docs-link {
    display: flex;
    align-items: center;
    padding: 10px 20px 10px 30px;
    color: #9fa0a3;
    text-decoration: none;
    transition: all 0.2s ease;
    font-size: 14px;
    gap: 10px;
}

.docs-link:hover {
    background: #262626;
    color: #d8d9da;
    padding-left: 35px;
}

.docs-link.active {
    background: #262626;
    color: #D3433E;
    border-left: 3px solid #D3433E;
}

.docs-link i {
    width: 16px;
    font-size: 12px;
    text-align: center;
}

.docs-sidebar::-webkit-scrollbar {
    width: 6px;
}

.docs-sidebar::-webkit-scrollbar-track {
    background: #181b1f;
}

.docs-sidebar::-webkit-scrollbar-thumb {
    background: #2d2d2d;
    border-radius: 3px;
}

.docs-sidebar::-webkit-scrollbar-thumb:hover {
    background: #3d3d3d;
}
</style>

<div class="docs-sidebar">
    <div class="logo">
        <h1>LabservNET</h1>
        <p>Documentation</p>
    </div>
    
    <nav class="docs-nav">
        <!-- Getting Started -->
        <div class="docs-category">
            <div class="docs-category-title">
                <i class="fas fa-rocket"></i> Getting Started
            </div>
            <a href="/docs" class="docs-link <?= $_SERVER['REQUEST_URI'] === '/docs' ? 'active' : '' ?>">
                <i class="fas fa-home"></i> Overview
            </a>
            <a href="/docs/installation" class="docs-link <?= strpos($_SERVER['REQUEST_URI'], '/docs/installation') !== false ? 'active' : '' ?>">
                <i class="fas fa-download"></i> Installation
            </a>
            <a href="/docs/quick-start" class="docs-link <?= strpos($_SERVER['REQUEST_URI'], '/docs/quick-start') !== false ? 'active' : '' ?>">
                <i class="fas fa-bolt"></i> Quick Start
            </a>
        </div>

        <!-- Configuration -->
        <div class="docs-category">
            <div class="docs-category-title">
                <i class="fas fa-cog"></i> Configuration
            </div>
            <a href="/docs/config/node-exporters" class="docs-link <?= strpos($_SERVER['REQUEST_URI'], '/docs/config/node-exporters') !== false ? 'active' : '' ?>">
                <i class="fas fa-server"></i> Node Exporters
            </a>
            <a href="/docs/config/dashboard" class="docs-link <?= strpos($_SERVER['REQUEST_URI'], '/docs/config/dashboard') !== false ? 'active' : '' ?>">
                <i class="fas fa-chart-line"></i> Dashboard Config
            </a>
            <a href="/docs/config/uptime" class="docs-link <?= strpos($_SERVER['REQUEST_URI'], '/docs/config/uptime') !== false ? 'active' : '' ?>">
                <i class="fas fa-heartbeat"></i> Uptime Monitoring
            </a>
            <a href="/docs/config/database" class="docs-link <?= strpos($_SERVER['REQUEST_URI'], '/docs/config/database') !== false ? 'active' : '' ?>">
                <i class="fas fa-database"></i> Database Setup
            </a>
        </div>

        <!-- Dashboard Components -->
        <div class="docs-category">
            <div class="docs-category-title">
                <i class="fas fa-th-large"></i> Components
            </div>
            <a href="/docs/components/metric-cards" class="docs-link <?= strpos($_SERVER['REQUEST_URI'], '/docs/components/metric-cards') !== false ? 'active' : '' ?>">
                <i class="fas fa-square"></i> Metric Cards
            </a>
            <a href="/docs/components/charts" class="docs-link <?= strpos($_SERVER['REQUEST_URI'], '/docs/components/charts') !== false ? 'active' : '' ?>">
                <i class="fas fa-chart-area"></i> Charts
            </a>
            <a href="/docs/components/tables" class="docs-link <?= strpos($_SERVER['REQUEST_URI'], '/docs/components/tables') !== false ? 'active' : '' ?>">
                <i class="fas fa-table"></i> Tables
            </a>
        </div>

        <!-- Advanced -->
        <div class="docs-category">
            <div class="docs-category-title">
                <i class="fas fa-code"></i> Advanced
            </div>
            <a href="/docs/advanced/metrics" class="docs-link <?= strpos($_SERVER['REQUEST_URI'], '/docs/advanced/metrics') !== false ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt"></i> Metrics System
            </a>
            <a href="/docs/advanced/routing" class="docs-link <?= strpos($_SERVER['REQUEST_URI'], '/docs/advanced/routing') !== false ? 'active' : '' ?>">
                <i class="fas fa-route"></i> URL Routing
            </a>
            <a href="/docs/advanced/api" class="docs-link <?= strpos($_SERVER['REQUEST_URI'], '/docs/advanced/api') !== false ? 'active' : '' ?>">
                <i class="fas fa-plug"></i> API Reference
            </a>
        </div>

        <!-- File Structure -->
        <div class="docs-category">
            <div class="docs-category-title">
                <i class="fas fa-folder-open"></i> Project
            </div>
            <a href="/docs/structure" class="docs-link <?= strpos($_SERVER['REQUEST_URI'], '/docs/structure') !== false ? 'active' : '' ?>">
                <i class="fas fa-sitemap"></i> File Structure
            </a>
            <a href="/docs/troubleshooting" class="docs-link <?= strpos($_SERVER['REQUEST_URI'], '/docs/troubleshooting') !== false ? 'active' : '' ?>">
                <i class="fas fa-wrench"></i> Troubleshooting
            </a>
        </div>
    </nav>
</div>
