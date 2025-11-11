<link href="/public/sidebar.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<?php
$configFile = __DIR__ . '/../../../config/node_exporters.json';
$instances = [];

if (file_exists($configFile)) {
    $config = json_decode(file_get_contents($configFile), true);
    if ($config && isset($config['instances'])) {
        $instances = array_filter($config['instances'], function($inst) {
            return $inst['enabled'] ?? false;
        });
    }
}
?>

<div class="sidebar slide-in-left">
    <div class="logo scale-in-bounce">
        <h1>LabservNET</h1>
    </div>
    <hr>
    <div class="buttons">
        <a href="/" class="sidebar-button hover-scale fade-in-left animate-delay-1"><i class="fas fa-home"></i> Home</a>
        
        <?php if (!empty($instances)): ?>
        <div class="sidebar-section fade-in-left animate-delay-2">
            <div class="section-title">Monitored Services</div>
            <?php $delay = 3; foreach ($instances as $instance): ?>
                <a href="/dashboard/<?= htmlspecialchars($instance['id']) ?>" 
                   class="sidebar-button node-link hover-scale fade-in-left animate-delay-<?= $delay ?>" 
                   data-node-id="<?= htmlspecialchars($instance['id']) ?>">
                    <span class="node-status-dot status-pulse"><i class="fas fa-circle"></i></span>
                    <?= htmlspecialchars($instance['title']) ?>
                </a>
            <?php $delay++; endforeach; ?>
        </div>
        <?php endif; ?>
        
        <hr>
        <a href="/uptime" class="sidebar-button hover-scale fade-in-left animate-delay-<?= isset($delay) ? $delay : 3 ?>"><i class="fas fa-heartbeat"></i> Uptime Status</a>
        <a href="/docs/installation" class="sidebar-button hover-scale fade-in-left animate-delay-<?= isset($delay) ? $delay + 1 : 4 ?>"><i class="fas fa-download"></i> Install guide</a>
        <a href="/docs" class="sidebar-button hover-scale fade-in-left animate-delay-<?= isset($delay) ? $delay + 2 : 5 ?>"><i class="fas fa-book"></i> Documentation</a>
    </div>
</div>