// Dynamic Dashboard JavaScript - Reads config and generates dashboard

class DynamicDashboard {
    constructor() {
        this.apiBase = '/resources/system/api/node_exporter.php';
        this.charts = {};
        this.gauges = {};
        // Store our dashboard chart configs separately to avoid clobbering Chart.js internals
        this.chartConfigs = {};
        // Track if a chart has performed its first data update (to allow initial animation)
        this.firstChartUpdateDone = {};
        this.selectedNode = null;
        this.nodeConfig = null;
        this.autoRefreshInterval = null;
        this.historyData = {};
        this.maxDataPoints = 20;
        this.lastValues = {}; // For rate calculations
        this.timeRange = 'live'; // 'live' or hours (1, 6, 24, 168, 720)
        this.isHistoricalMode = false;
        
        this.init();
    }
    
    async init() {
        await this.loadInstances();
        
        this.setupEventListeners();
        
        // Check URL parameter for node selection (supports both query string and path parameter)
        const urlParams = new URLSearchParams(window.location.search);
        let nodeId = urlParams.get('node');
        
        // If not in query string, check if it's in the URL path (e.g., /dashboard/node1)
        if (!nodeId) {
            const pathParts = window.location.pathname.split('/').filter(p => p);
            if (pathParts.length >= 2 && pathParts[0] === 'dashboard') {
                nodeId = pathParts[1];
            }
        }
        
        if (nodeId) {
            // Find and select the node from loaded instances
            this.selectNodeById(nodeId);
        }
    }
    
    async loadInstances() {
        try {
            const response = await fetch(`${this.apiBase}?action=instances`);
            const data = await response.json();
            
            if (data.success) {
                this.populateNodeSelector(data.data);
            } else {
                this.showError('Failed to load instances: ' + data.error);
            }
        } catch (error) {
            this.showError('Error loading instances: ' + error.message);
        }
    }
    
    populateNodeSelector(instances) {
        // Store instances for later use
        this.instances = instances;
    }
    
    selectNodeById(nodeId) {
        if (!this.instances) {
            return;
        }
        
        const instance = this.instances.find(i => i.id === nodeId);
        
        if (instance) {
            this.selectNode(instance);
            
            // Update sidebar active state
            document.querySelectorAll('.sidebar .node-link').forEach(link => {
                link.classList.remove('active');
                if (link.dataset.nodeId === nodeId) {
                    link.classList.add('active');
                }
            });
        }
    }
    
    setupEventListeners() {
        document.getElementById('refresh-btn').addEventListener('click', () => {
            if (this.selectedNode) {
                this.loadMetrics(this.selectedNode);
            }
        });
        
        document.getElementById('auto-refresh').addEventListener('change', () => {
            this.checkAutoRefresh();
        });
    }
    
    selectNode(config) {
        this.selectedNode = config.id;
        this.nodeConfig = config;
        this.historyData = {};
        this.lastValues = {};
        this.interfaceChartsExpanded = false;
        
        // Update page title
        document.getElementById('dashboard-title').textContent = `${config.title} - Dashboard`;
        document.title = `${config.title} - LabservNET`;
        
        // Hide empty state
        document.getElementById('empty-state').style.display = 'none';
        
        // Build dashboard from config
        this.buildDashboard(config.dashboard);
        
        // Load initial metrics after a brief delay to ensure charts are rendered
        setTimeout(async () => {
            // Wait for metrics to load completely
            await this.loadMetrics(config.id);
            
            // Wait a bit more for all charts to fully render
            await new Promise(resolve => setTimeout(resolve, 200));
            
            this.checkAutoRefresh();
            
            // Hide loader after everything is loaded and rendered
            this.hideLoader();
        }, 300);
        
        // Update URL without reload (use clean URL format)
        const newUrl = `/dashboard/${config.id}`;
        window.history.pushState({}, '', newUrl);
    }
    
    hideLoader() {
        const loader = document.getElementById('page-loader');
        if (loader) {
            loader.classList.add('hidden');
            setTimeout(() => {
                loader.style.display = 'none';
            }, 500);
        }
    }
    
    buildDashboard(dashboardConfig) {
        // Clear existing content
        document.getElementById('metric-cards').innerHTML = '';
        document.getElementById('charts-container').innerHTML = '';
        document.getElementById('tables-container').innerHTML = '';
        
        // Reset charts storage
        this.charts = {};
        this.gauges = {};
        this.historyData = {};
        this.chartConfigs = {};
        this.firstChartUpdateDone = {};
        
        // Store original config for later expansion
        this.originalDashboardConfig = JSON.parse(JSON.stringify(dashboardConfig));
        
        // Build metric cards
        if (dashboardConfig.cards) {
            dashboardConfig.cards.forEach((card, index) => {
                this.createMetricCard(card, index);
            });
        }
        
        // Build charts
        if (dashboardConfig.charts) {
            dashboardConfig.charts.forEach((chart, index) => {
                this.createChart(chart, index);
            });
        }
        
        // Build tables
        if (dashboardConfig.tables) {
            dashboardConfig.tables.forEach((table, index) => {
                this.createTable(table, index);
            });
        }
    }
    
    createMetricCard(cardConfig, index) {
        const container = document.getElementById('metric-cards');
        const cardId = `card-${index}`;
        
        const card = document.createElement('div');
        card.className = 'metric-card';
        card.innerHTML = `
            <div class="metric-header">
                <h3>${cardConfig.title}</h3>
                <span class="metric-icon"><i class="fas ${cardConfig.icon}"></i></span>
            </div>
            <div class="metric-value" id="${cardId}-value">--${cardConfig.unit || ''}</div>
            ${cardConfig.type === 'gauge' ? `<div class="metric-chart-small"><canvas id="${cardId}-gauge"></canvas></div>` : ''}
            ${cardConfig.type === 'value' ? `<div class="metric-subtext" id="${cardId}-subtext"></div>` : ''}
        `;
        
        container.appendChild(card);
    }
    
    createChart(chartConfig, index) {
        const container = document.getElementById('charts-container');
        const chartId = `chart-${index}`;
        
        const chartCard = document.createElement('div');
        chartCard.className = 'chart-card';
        chartCard.style.opacity = '0';
        chartCard.innerHTML = `
            <div class="chart-header">
                <h3>${chartConfig.title}</h3>
            </div>
            <div class="chart-container" style="position: relative; height: 300px;">
                <canvas id="${chartId}" style="max-height: 300px;"></canvas>
            </div>
        `;
        
        container.appendChild(chartCard);
        
        // Keep a copy of the dashboard chart config by id (do not store on the Chart object)
        this.chartConfigs[chartId] = chartConfig;

        // Initialize chart immediately since DOM is ready
        this.initChart(chartId, chartConfig);
        
        // Fade in the card container smoothly
        requestAnimationFrame(() => {
            chartCard.style.transition = 'opacity 800ms ease-out';
            chartCard.style.opacity = '1';
        });
    }
    
    createTable(tableConfig, index) {
        const container = document.getElementById('tables-container');
        const tableId = `table-${index}`;
        
        const tableCard = document.createElement('div');
        tableCard.className = 'table-card';
        
        let columnsHtml = tableConfig.columns.map(col => 
            `<th style="width: ${col.width || 'auto'}">${col.header}</th>`
        ).join('');
        
        tableCard.innerHTML = `
            <div class="table-header">
                <h3><i class="fas ${tableConfig.icon}"></i> ${tableConfig.title}</h3>
            </div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>${columnsHtml}</tr>
                    </thead>
                    <tbody id="${tableId}-body">
                        <tr><td colspan="${tableConfig.columns.length}">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        `;
        
        container.appendChild(tableCard);
    }
    
    initChart(chartId, chartConfig) {
        const canvas = document.getElementById(chartId);
        if (!canvas) {
            return false;
        }
        
        // If chart already exists and is valid, return early - don't recreate
        if (this.charts[chartId] && this.charts[chartId].data) {
            return true;
        }
        
        try {
            const ctx = canvas.getContext('2d');
            if (!ctx) {
                return false;
            }
            
            const datasets = chartConfig.metrics.map((metric, idx) => ({
                label: metric.name,
                data: [],
                borderColor: metric.color,
                backgroundColor: metric.fill ? this.hexToRgba(metric.color, 0.1) : 'transparent',
                tension: 0.4,
                fill: metric.fill || false,
                pointRadius: 0,
                pointHoverRadius: 4,
                borderWidth: 2
            }));
            
            const yAxisConfig = {
                ticks: { color: '#9fa0a3' },
                grid: { color: '#2d2d2d' }
            };
            
            if (chartConfig.yAxis && chartConfig.yAxis.min !== undefined) {
                yAxisConfig.min = chartConfig.yAxis.min;
            }
            if (chartConfig.yAxis && chartConfig.yAxis.max !== undefined) {
                yAxisConfig.max = chartConfig.yAxis.max;
            }
            
            const options = {
                responsive: true,
                maintainAspectRatio: true,
                animation: {
                    duration: 1500,
                    easing: 'easeInOutQuart',
                    onProgress: function(animation) {
                        // Progressive reveal effect
                        if (animation.currentStep === 0) {
                            animation.chart.ctx.globalAlpha = 0;
                        } else {
                            animation.chart.ctx.globalAlpha = Math.min(1, animation.currentStep / (animation.numSteps * 0.3));
                        }
                    },
                    onComplete: function(animation) {
                        animation.chart.ctx.globalAlpha = 1;
                    }
                },
                plugins: {
                    legend: { labels: { color: '#d8d9da' } },
                    tooltip: { callbacks: {} }
                },
                scales: {
                    x: {
                        ticks: { color: '#9fa0a3' },
                        grid: { color: '#2d2d2d' }
                    },
                    y: yAxisConfig
                }
            };
            
            try {
                this.charts[chartId] = new Chart(ctx, {
                    type: chartConfig.type,
                    data: { labels: [], datasets },
                    options
                });
            } catch (chartError) {
                return false;
            }
            
            if (!this.charts[chartId]) {
                return false;
            }
            
            if (!this.charts[chartId].data) {
                return false;
            }
            
            // Do NOT overwrite chart.config (Chart.js uses this internally). Our config lives in this.chartConfigs.
            return true;
            
        } catch (e) {
            return false;
        }
    }
    
    async loadMetrics(nodeId) {
        try {
            const url = `${this.apiBase}?action=metrics&node=${nodeId}`;
            const response = await fetch(url);
            const data = await response.json();
            
            if (data.success && data.data) {
                await this.updateDashboard(data.data);
            } else {
                const errorMsg = data.error || 'Unknown error';
                this.showError('Failed to load metrics: ' + errorMsg);
            }
        } catch (error) {
            this.showError('Error loading metrics: ' + error.message);
        }
    }
    
    async updateDashboard(metrics) {
        const config = this.nodeConfig.dashboard;
        
        // Update metric cards
        if (config.cards) {
            config.cards.forEach((card, index) => {
                this.updateMetricCard(card, index, metrics);
            });
        }
        
        // Update history and charts
        if (config.charts) {
            this.updateHistory(config.charts, metrics);
            
            config.charts.forEach((chart, index) => {
                this.updateChart(`chart-${index}`, chart, metrics);
            });
            
            // Wait for charts to render
            await new Promise(resolve => setTimeout(resolve, 100));
        }
        
        // Update tables
        if (config.tables) {
            config.tables.forEach((table, index) => {
                this.updateTable(`table-${index}`, table, metrics);
            });
        }
    }
    
    expandInterfaceCharts(metrics) {
        const config = this.nodeConfig.dashboard;
        if (!config.charts) return;
        
        // Find all perInterface charts
        const perInterfaceCharts = config.charts.filter(c => c.perInterface);
        
        perInterfaceCharts.forEach((templateChart, templateIndex) => {
            // Extract all interfaces from metrics
            const interfaces = this.extractInterfaces(metrics, templateChart);
            
            console.log('Found interfaces:', interfaces);
            
            // Create a chart for each interface
            interfaces.forEach(interfaceName => {
                const chartConfig = JSON.parse(JSON.stringify(templateChart));
                chartConfig.title = `${interfaceName} Traffic`;
                chartConfig.interfaceName = interfaceName;
                chartConfig.perInterface = false; // Mark as expanded
                
                // Add interface-specific filter to each metric
                chartConfig.metrics.forEach(metric => {
                    const deviceFilter = `device="${interfaceName}"`;
                    if (metric.calculation.filter) {
                        metric.calculation.filter = `${metric.calculation.filter},${deviceFilter}`;
                    } else {
                        metric.calculation.filter = deviceFilter;
                    }
                });
                
                // Add to config and create chart
                const newIndex = config.charts.length;
                config.charts.push(chartConfig);
                this.createChart(chartConfig, newIndex);
            });
        });
        
        // Remove template charts from config
        config.charts = config.charts.filter(c => !c.perInterface);
    }
    
    extractInterfaces(metrics, chartConfig) {
        const interfaces = new Set();
        
        // Look at network metrics to find all devices
        if (metrics.network) {
            for (const [key, metric] of Object.entries(metrics.network)) {
                if (metric.labels) {
                    const deviceMatch = metric.labels.match(/device="([^"]+)"/);
                    if (deviceMatch) {
                        const device = deviceMatch[1];
                        
                        // Apply filter if specified
                        if (chartConfig.interfaceFilter) {
                            if (this.matchesFilter(metric.labels, chartConfig.interfaceFilter)) {
                                interfaces.add(device);
                            }
                        } else {
                            interfaces.add(device);
                        }
                    }
                }
            }
        }
        
        return Array.from(interfaces).sort();
    }
    
    updateMetricCard(cardConfig, index, metrics) {
        const cardId = `card-${index}`;
        const value = this.calculateMetric(cardConfig.calculation, metrics);
        
        const valueEl = document.getElementById(`${cardId}-value`);
        if (valueEl) {
            if (cardConfig.format === 'load') {
                const loads = value.split(',');
                valueEl.textContent = loads[0] || '--';
                const subtextEl = document.getElementById(`${cardId}-subtext`);
                if (subtextEl) {
                    subtextEl.textContent = loads.join(' / ');
                }
            } else if (cardConfig.format === 'cpu_info') {
                // For CPU info format, value contains main line + model on separate line
                const lines = value.split('\n');
                valueEl.textContent = lines[0] || '--';
                if (lines.length > 1) {
                    const subtextEl = document.getElementById(`${cardId}-subtext`);
                    if (subtextEl) {
                        subtextEl.textContent = lines[1];
                    }
                }
            } else if (cardConfig.format === 'memory_gb' || cardConfig.format === 'disk_gb') {
                // For GB format, value is already formatted as "X / Y GB"
                valueEl.textContent = value;
            } else {
                // precision: cardConfig.precision can be a number or 'raw' to avoid rounding
                let precision;
                if (cardConfig.precision !== undefined) {
                    if (cardConfig.precision === 'raw') precision = null; else if (typeof cardConfig.precision === 'number') precision = cardConfig.precision;
                }
                if (precision === undefined) {
                    precision = (cardConfig.unit === '%' || cardConfig.format === 'percentage') ? 1 : 2;
                }
                const displayValue = this.formatValue(value, precision, cardConfig);
                valueEl.textContent = `${displayValue}${cardConfig.unit || ''}`;
            }
        }
        
        // Update gauge if applicable
        if (cardConfig.type === 'gauge') {
            this.updateGauge(`${cardId}-gauge`, value, cardConfig.thresholds);
        }
    }

    // Generic value formatter honoring precision (or raw)
    formatValue(val, precision, cardConfig) {
        if (val === null || val === undefined || isNaN(val)) return '--';
        if (precision === null) return String(val);
        if (cardConfig && (cardConfig.unit === '%' || cardConfig.format === 'percentage')) {
            if (val < 0) val = 0;
            if (val > 100) val = 100;
        }
        return Number(val).toFixed(precision);
    }
    
    updateGauge(canvasId, value, thresholds) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        
        // Determine color based on thresholds
        let color = '#7eb26d'; // green
        if (thresholds) {
            if (value > thresholds.critical) color = '#e24d42'; // red
            else if (value > thresholds.warning) color = '#ff9830'; // orange
        }
        
        if (!this.gauges[canvasId]) {
            this.gauges[canvasId] = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: [value, 100 - value],
                        backgroundColor: [color, '#2d2d2d'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '75%',
                    animation: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: false }
                    }
                }
            });
        } else {
            this.gauges[canvasId].data.datasets[0].data = [value, 100 - value];
            this.gauges[canvasId].data.datasets[0].backgroundColor = [color, '#2d2d2d'];
            this.gauges[canvasId].update('none');
        }
    }
    
    calculateMetric(calculation, metrics) {
        if (!calculation || !metrics) {
            return 0;
        }
        
        switch (calculation.type) {
            case 'cpu_percentage':
                return this.calculateCPUUsage(metrics.cpu);
            
            case 'cpu_info_format':
                return this.calculateCPUInfoFormat(metrics.cpu, metrics.system);
            
            case 'memory_percentage':
                return this.calculateMemoryUsage(metrics.memory);
            
            case 'memory_gb_used':
                return this.calculateMemoryGBUsed(metrics.memory);
            
            case 'memory_gb_format':
                return this.calculateMemoryGBFormat(metrics.memory);
            
            case 'filesystem_percentage':
                return this.calculateFilesystemUsage(metrics.disk, calculation.filter);
            
            case 'filesystem_gb_used':
                return this.calculateFilesystemGBUsed(metrics.disk, calculation.filter);
            
            case 'filesystem_gb_format':
                return this.calculateFilesystemGBFormat(metrics.disk, calculation.filter);
            
            case 'percentage':
                return this.calculateFromFormula(calculation.formula, metrics);
            
            case 'direct':
                // Check if any of the metrics includes "load"
                if (calculation.metrics && calculation.metrics.some(m => m.includes('node_load'))) {
                    return this.getSystemLoad(metrics);
                }
                return 0;
            
            case 'rate':
                return this.calculateRate(calculation.metric, calculation.filter, metrics);
            
            default:
                return 0;
        }
    }
    
    calculateMemoryUsage(memoryMetrics) {
        if (!memoryMetrics || typeof memoryMetrics !== 'object') {
            return 0;
        }
        
        let totalMem = 0, freeMem = 0, inactiveMem = 0, cacheMem = 0, bufferMem = 0;
        let linuxAvailMem = 0;
        
        for (const metric of Object.values(memoryMetrics)) {
            if (!metric || !metric.name) continue;
            
            const name = metric.name;
            
            if (name === 'node_memory_MemTotal_bytes') {
                totalMem = metric.value;
            } else if (name === 'node_memory_MemAvailable_bytes') {
                linuxAvailMem = metric.value;
            } else if (name === 'node_memory_size_bytes') {
                totalMem = metric.value;
            } else if (name === 'node_memory_free_bytes') {
                freeMem = metric.value;
            } else if (name === 'node_memory_inactive_bytes') {
                inactiveMem = metric.value;
            } else if (name === 'node_memory_cache_bytes') {
                cacheMem = metric.value;
            } else if (name === 'node_memory_buffer_bytes') {
                bufferMem = metric.value;
            }
        }
        
        let usedMem;
        if (linuxAvailMem > 0) {
            // Linux: use MemAvailable
            usedMem = totalMem - linuxAvailMem;
        } else {
            // FreeBSD: available = free + inactive + cache + buffer
            const availMem = freeMem + inactiveMem + cacheMem + bufferMem;
            usedMem = totalMem - availMem;
        }
        
        return totalMem > 0 ? ((usedMem / totalMem) * 100) : 0;
    }
    
    calculateMemoryGBUsed(memoryMetrics) {
        if (!memoryMetrics || typeof memoryMetrics !== 'object') {
            return 0;
        }
        
        let totalMem = 0, freeMem = 0, inactiveMem = 0, cacheMem = 0, bufferMem = 0;
        let linuxAvailMem = 0;
        
        for (const metric of Object.values(memoryMetrics)) {
            if (!metric || !metric.name) continue;
            
            const name = metric.name;
            
            if (name === 'node_memory_MemTotal_bytes') {
                totalMem = metric.value;
            } else if (name === 'node_memory_MemAvailable_bytes') {
                linuxAvailMem = metric.value;
            } else if (name === 'node_memory_size_bytes') {
                totalMem = metric.value;
            } else if (name === 'node_memory_free_bytes') {
                freeMem = metric.value;
            } else if (name === 'node_memory_inactive_bytes') {
                inactiveMem = metric.value;
            } else if (name === 'node_memory_cache_bytes') {
                cacheMem = metric.value;
            } else if (name === 'node_memory_buffer_bytes') {
                bufferMem = metric.value;
            }
        }
        
        let usedMem;
        if (linuxAvailMem > 0) {
            // Linux: use MemAvailable
            usedMem = totalMem - linuxAvailMem;
        } else {
            // FreeBSD: available = free + inactive + cache + buffer
            const availMem = freeMem + inactiveMem + cacheMem + bufferMem;
            usedMem = totalMem - availMem;
        }
        
        // Convert bytes to GB
        return usedMem / (1024 * 1024 * 1024);
    }
    
    calculateMemoryGBFormat(memoryMetrics) {
        if (!memoryMetrics || typeof memoryMetrics !== 'object') {
            return '0 / 0 GB';
        }
        
        let totalMem = 0, freeMem = 0, inactiveMem = 0, cacheMem = 0, bufferMem = 0;
        let linuxAvailMem = 0;
        
        for (const metric of Object.values(memoryMetrics)) {
            if (!metric || !metric.name) continue;
            
            const name = metric.name;
            
            if (name === 'node_memory_MemTotal_bytes') {
                totalMem = metric.value;
            } else if (name === 'node_memory_MemAvailable_bytes') {
                linuxAvailMem = metric.value;
            } else if (name === 'node_memory_size_bytes') {
                totalMem = metric.value;
            } else if (name === 'node_memory_free_bytes') {
                freeMem = metric.value;
            } else if (name === 'node_memory_inactive_bytes') {
                inactiveMem = metric.value;
            } else if (name === 'node_memory_cache_bytes') {
                cacheMem = metric.value;
            } else if (name === 'node_memory_buffer_bytes') {
                bufferMem = metric.value;
            }
        }
        
        let usedMem;
        if (linuxAvailMem > 0) {
            // Linux: use MemAvailable
            usedMem = totalMem - linuxAvailMem;
        } else {
            // FreeBSD: available = free + inactive + cache + buffer
            const availMem = freeMem + inactiveMem + cacheMem + bufferMem;
            usedMem = totalMem - availMem;
        }
        
        // Convert bytes to GB and return formatted string
        const usedGB = (usedMem / (1024 * 1024 * 1024)).toFixed(1);
        const totalGB = (totalMem / (1024 * 1024 * 1024)).toFixed(1);
        return `${usedGB} / ${totalGB} GB`;
    }
    
    calculateFilesystemUsage(diskMetrics, filter) {
        if (!diskMetrics) {
            console.warn('No disk metrics provided');
            return 0;
        }
        
        let sizeBytes = 0, availBytes = 0;
        
        // Find the filesystem matching the filter
        for (const [key, value] of Object.entries(diskMetrics)) {
            if (value && value.name) {
                if (value.name === 'node_filesystem_size_bytes') {
                    if (!filter || this.matchesFilter(value.labels, filter)) {
                        sizeBytes = value.value;
                    }
                }
                if (value.name === 'node_filesystem_avail_bytes') {
                    if (!filter || this.matchesFilter(value.labels, filter)) {
                        availBytes = value.value;
                    }
                }
            }
        }
        
        const usedBytes = sizeBytes - availBytes;
        return sizeBytes > 0 ? ((usedBytes / sizeBytes) * 100) : 0;
    }
    
    calculateFilesystemGBUsed(diskMetrics, filter) {
        if (!diskMetrics) {
            return 0;
        }
        
        let sizeBytes = 0, availBytes = 0;
        
        // Find the filesystem matching the filter
        for (const [key, value] of Object.entries(diskMetrics)) {
            if (value && value.name) {
                if (value.name === 'node_filesystem_size_bytes') {
                    if (!filter || this.matchesFilter(value.labels, filter)) {
                        sizeBytes = value.value;
                    }
                }
                if (value.name === 'node_filesystem_avail_bytes') {
                    if (!filter || this.matchesFilter(value.labels, filter)) {
                        availBytes = value.value;
                    }
                }
            }
        }
        
        const usedBytes = sizeBytes - availBytes;
        // Convert bytes to GB
        return usedBytes / (1024 * 1024 * 1024);
    }
    
    calculateFilesystemGBFormat(diskMetrics, filter) {
        if (!diskMetrics) {
            return '0 / 0 GB';
        }
        
        let sizeBytes = 0, availBytes = 0;
        
        // Find the filesystem matching the filter
        for (const [key, value] of Object.entries(diskMetrics)) {
            if (value && value.name) {
                if (value.name === 'node_filesystem_size_bytes') {
                    if (!filter || this.matchesFilter(value.labels, filter)) {
                        sizeBytes = value.value;
                    }
                }
                if (value.name === 'node_filesystem_avail_bytes') {
                    if (!filter || this.matchesFilter(value.labels, filter)) {
                        availBytes = value.value;
                    }
                }
            }
        }
        
        const usedBytes = sizeBytes - availBytes;
        // Convert bytes to GB and return formatted string
        const usedGB = (usedBytes / (1024 * 1024 * 1024)).toFixed(1);
        const totalGB = (sizeBytes / (1024 * 1024 * 1024)).toFixed(1);
        return `${usedGB} / ${totalGB} GB`;
    }
    
    calculateCPUUsage(cpuMetrics) {
        if (!cpuMetrics || typeof cpuMetrics !== 'object') {
            return 0;
        }
        
        // Collect current CPU times per mode
        let idleSum = 0, activeSum = 0;
        
        for (const metric of Object.values(cpuMetrics)) {
            if (!metric || !metric.name) continue;
            if (metric.name !== 'node_cpu_seconds_total') continue;
            
            const labels = metric.labels || '';
            if (labels.includes('mode="idle"')) {
                idleSum += metric.value;
            } else if (!labels.includes('mode="iowait"')) {
                activeSum += metric.value;
            }
        }
        
        // Calculate delta-based CPU usage
        if (!this.lastCPUValues) {
            this.lastCPUValues = { idle: idleSum, active: activeSum, time: Date.now() };
            return 0; // First measurement
        }
        
        const idleDelta = idleSum - this.lastCPUValues.idle;
        const activeDelta = activeSum - this.lastCPUValues.active;
        const totalDelta = idleDelta + activeDelta;
        
        let cpuPercent = 0;
        if (totalDelta > 0) {
            cpuPercent = (activeDelta / totalDelta) * 100;
        }
        
        this.lastCPUValues = { idle: idleSum, active: activeSum, time: Date.now() };
        
        return cpuPercent;
    }
    
    calculateCPUInfoFormat(cpuMetrics, systemMetrics) {
        if (!cpuMetrics || typeof cpuMetrics !== 'object') {
            return '0% of 0 Cores';
        }
        
        // Get CPU percentage
        const cpuPercent = this.calculateCPUUsage(cpuMetrics);
        
        // Count CPU cores
        const cpuCores = new Set();
        for (const metric of Object.values(cpuMetrics)) {
            if (metric && metric.name === 'node_cpu_seconds_total' && metric.labels) {
                const cpuMatch = metric.labels.match(/cpu="([^"]+)"/);
                if (cpuMatch) cpuCores.add(cpuMatch[1]);
            }
        }
        
        // Get CPU model
        let cpuModel = '';
        if (systemMetrics) {
            for (const metric of Object.values(systemMetrics)) {
                if (metric && metric.name === 'node_cpu_info' && metric.labels) {
                    const modelMatch = metric.labels.match(/model_name="([^"]+)"/);
                    if (modelMatch) {
                        cpuModel = modelMatch[1];
                        // Shorten model name (remove extra spaces and common suffixes)
                        cpuModel = cpuModel.replace(/\s+/g, ' ').trim();
                        cpuModel = cpuModel.replace(/ CPU @ .*$/, '');
                        cpuModel = cpuModel.replace(/ Processor$/, '');
                        break;
                    }
                }
            }
        }
        
        const cores = cpuCores.size;
        const percent = cpuPercent.toFixed(1);
        
        if (cpuModel) {
            return `${percent}% of ${cores} Cores\n${cpuModel}`;
        } else {
            return `${percent}% of ${cores} Cores`;
        }
    }
    
    calculateFromFormula(formula, metrics) {
        // Simple formula parser for basic calculations
        // Replace metric names with actual values
        let expression = formula;
        
        // Extract metric names from formula
        const metricNames = formula.match(/node_[a-zA-Z_]+/g) || [];
        
        metricNames.forEach(metricName => {
            let value = 0;
            let found = false;
            
            // Search in all metric categories
            for (const category of Object.values(metrics)) {
                if (typeof category === 'object') {
                    for (const [key, metric] of Object.entries(category)) {
                        if (metric && metric.name === metricName) {
                            value = metric.value;
                            found = true;
                            break;
                        }
                    }
                }
                if (found) break;
            }
            
            expression = expression.replace(new RegExp(metricName, 'g'), value);
        });
        
        try {
            const result = eval(expression);
            return result;
        } catch (e) {
            return 0;
        }
    }
    
    getSystemLoad(metrics) {
        let load1 = 0, load5 = 0, load15 = 0;
        
        // Search in all metric categories, not just CPU
        for (const category of Object.values(metrics)) {
            if (typeof category === 'object') {
                for (const [key, value] of Object.entries(category)) {
                    if (value && value.name) {
                        if (value.name === 'node_load1') load1 = value.value;
                        if (value.name === 'node_load5') load5 = value.value;
                        if (value.name === 'node_load15') load15 = value.value;
                    }
                }
            }
        }
        
        return `${load1.toFixed(2)},${load5.toFixed(2)},${load15.toFixed(2)}`;
    }
    
    calculateRate(metricName, filter, metrics) {
        // Aggregate all values matching the metric and filter
        let total = 0;
        let matchCount = 0;
        
        for (const category of Object.values(metrics)) {
            if (typeof category !== 'object' || !category) continue;
            
            for (const [key, metric] of Object.entries(category)) {
                if (!metric || typeof metric !== 'object') continue;
                
                // Must match the metric name exactly
                if (metric.name !== metricName) continue;
                
                // Must pass the filter
                if (filter && !this.matchesFilter(metric.labels || '', filter)) {
                    continue;
                }
                
                total += metric.value || 0;
                matchCount++;
            }
        }
        
        return total;
    }
    
    matchesFilter(labels, filter) {
        // Simple filter matching
        if (!filter) return true;
        
        // Handle multiple filters separated by comma
        if (filter.includes(',')) {
            const filters = filter.split(',');
            return filters.every(f => this.matchesFilter(labels, f.trim()));
        }
        
        // Handle negative filters like device!~"lo|veth.*"
        if (filter.includes('!~')) {
            const [field, pattern] = filter.split('!~');
            const regex = new RegExp(pattern.replace(/"/g, ''));
            const value = this.extractLabelValue(labels, field);
            return !regex.test(value);
        }
        
        // Handle exact match like type="read" or device="eth0"
        if (filter.includes('=')) {
            const [field, value] = filter.split('=');
            const cleanValue = value.replace(/"/g, '');
            return labels.includes(`${field}="${cleanValue}"`);
        }
        
        return true;
    }
    
    extractLabelValue(labels, field) {
        const match = labels.match(new RegExp(`${field}="([^"]+)"`));
        return match ? match[1] : '';
    }
    
    updateHistory(chartsConfig, metrics) {
        const timestamp = new Date().toLocaleTimeString();
        
        chartsConfig.forEach((chartConfig, index) => {
            const chartId = `chart-${index}`;
            
            if (!this.historyData[chartId]) {
                this.historyData[chartId] = {
                    timestamps: [],
                    datasets: chartConfig.metrics.map(() => [])
                };
            }
            
            const history = this.historyData[chartId];
            history.timestamps.push(timestamp);
            
            chartConfig.metrics.forEach((metric, metricIndex) => {
                let value = 0;
                
                if (metric.calculation.type === 'rate') {
                    const currentValue = this.calculateRate(metric.calculation.metric, metric.calculation.filter, metrics);
                    const lastKey = `${chartId}-${metricIndex}`;
                    
                    // For rates: if we don't have a baseline yet, store it and push 0
                    // Next update will calculate the actual rate
                    if (!this.lastValues[lastKey]) {
                        this.lastValues[lastKey] = { value: currentValue, time: Date.now() };
                        value = 0; // First measurement, no rate yet
                    } else {
                        const timeDiff = (Date.now() - this.lastValues[lastKey].time) / 1000;
                        const valueDiff = currentValue - this.lastValues[lastKey].value;
                        
                        // Rate = bytes per second
                        if (timeDiff > 0) {
                            value = valueDiff / timeDiff;
                        }
                        
                        this.lastValues[lastKey] = { value: currentValue, time: Date.now() };
                    }
                } else {
                    // Direct calculation (CPU%, Memory%, etc)
                    value = this.calculateMetric(metric.calculation, metrics);
                }
                
                history.datasets[metricIndex].push(value);
            });
            
            // Keep only last N data points
            if (history.timestamps.length > this.maxDataPoints) {
                history.timestamps.shift();
                history.datasets.forEach(dataset => dataset.shift());
            }
        });
    }
    
    updateChart(chartId, chartConfig, metrics) {
        const chart = this.charts[chartId];
        
        if (!chart || !chart.data) {
            return;
        }
        
        const history = this.historyData[chartId];
        if (!history || history.timestamps.length === 0) {
            return;
        }
        
        try {
            // Sync labels
            chart.data.labels = [...history.timestamps];
            
            // Ensure we have the right number of datasets
            const expectedDatasetCount = chart.data.datasets.length;
            
            if (history.datasets.length < expectedDatasetCount) {
                while (history.datasets.length < expectedDatasetCount) {
                    history.datasets.push(new Array(history.timestamps.length).fill(0));
                }
            }
            
            // Update each dataset
            for (let i = 0; i < expectedDatasetCount; i++) {
                const historyData = history.datasets[i] || [];
                const chartDataset = chart.data.datasets[i];
                
                if (!chartDataset) {
                    continue;
                }
                
                // Pad history data if needed
                let dataArray = [...historyData];
                if (dataArray.length < history.timestamps.length) {
                    const padCount = history.timestamps.length - dataArray.length;
                    dataArray = Array(padCount).fill(0).concat(dataArray);
                }
                
                // For dynamic units (network traffic, disk I/O), scale the values
                if (chartConfig.yAxis && chartConfig.yAxis.dynamic) {
                    const allValues = history.datasets.flat();
                    const unit = this.getBestUnit(allValues, chartConfig.yAxis.unit === 'bits');
                    
                    chartDataset.data = dataArray.map(v => {
                        if (!isFinite(v)) return 0;
                        return v / unit.divisor;
                    });
                    
                    if (chartConfig.metrics[i]) {
                        chartDataset.label = `${chartConfig.metrics[i].name} (${unit.unit}/s)`;
                    }
                 } else {
                     // Static units (CPU%, Memory%)
                     chartDataset.data = dataArray.map(v => isFinite(v) ? v : 0);
                 }
             }
            
            // First update: use smooth animation (like OPNsense); subsequent updates: no animation for speed
            if (!this.firstChartUpdateDone[chartId]) {
                chart.update();
                this.firstChartUpdateDone[chartId] = true;
            } else {
                chart.update('none');
            }
        } catch (e) {
            // Silent error handling
        }
    }
    
    updateTable(tableId, tableConfig, metrics) {
        const tbody = document.getElementById(`${tableId}-body`);
        if (!tbody) {
            return;
        }
        
        tbody.innerHTML = '';
        
        // Handle system info table
        if (tableConfig.type === 'system_info') {
            this.updateSystemInfoTable(tbody, tableConfig, metrics);
            return;
        }
        
        // Group metrics by label (e.g., by device or mountpoint)
        const rows = {};
        
        for (const [metricKey, metricConfig] of Object.entries(tableConfig.metrics)) {
            const metricName = metricConfig;
            
            for (const category of Object.values(metrics)) {
                for (const [key, metric] of Object.entries(category)) {
                    if (metric.name === metricName) {
                        if (!tableConfig.filter || this.matchesFilter(metric.labels, tableConfig.filter)) {
                            // Extract identifier (device, mountpoint, etc.)
                            const id = this.extractPrimaryLabel(metric.labels, tableConfig.columns[0].field);
                            
                            if (!rows[id]) rows[id] = {};
                            rows[id][metricKey] = metric.value;
                            rows[id][tableConfig.columns[0].field] = id;
                            
                            // Extract other labels
                            tableConfig.columns.forEach(col => {
                                if (col.field !== tableConfig.columns[0].field) {
                                    const labelValue = this.extractLabelValue(metric.labels, col.field);
                                    if (labelValue) rows[id][col.field] = labelValue;
                                }
                            });
                        }
                    }
                }
            }
        }
        
        // Render rows
        // Compute filesystem used/usage fields if size/avail present
        for (const [id, rowData] of Object.entries(rows)) {
            if ((rowData.size || rowData.size === 0) && (rowData.avail || rowData.avail === 0)) {
                const used = rowData.size - rowData.avail;
                rowData.used = used;
                rowData.usage = rowData.size > 0 ? (used / rowData.size * 100) : 0;
            }

            const tr = document.createElement('tr');
            
            tableConfig.columns.forEach(col => {
                const td = document.createElement('td');
                const value = rowData[col.field] || 0;
                
                if (col.format === 'bytes') {
                    td.textContent = this.formatBytes(value);
                } else if (col.format === 'percentage') {
                    // Calculate percentage if needed
                    const pct = (rowData.usage !== undefined && !isNaN(rowData.usage)) ? rowData.usage.toFixed(1) : '0.0';
                    td.textContent = `${pct}%`;
                } else if (col.format === 'number') {
                    td.textContent = value.toLocaleString();
                } else {
                    td.textContent = value;
                }
                
                tr.appendChild(td);
            });
            
            tbody.appendChild(tr);
        }
        
        if (Object.keys(rows).length === 0) {
            tbody.innerHTML = '<tr><td colspan="' + tableConfig.columns.length + '">No data</td></tr>';
        }
    }
    
    extractPrimaryLabel(labels, field) {
        const match = labels.match(new RegExp(`${field}="([^"]+)"`));
        return match ? match[1] : 'unknown';
    }
    
    updateSystemInfoTable(tbody, tableConfig, metrics) {
        const systemInfo = [];
        
        // Extract system information
        let hostname = 'Unknown', os = 'Unknown', kernel = 'Unknown', cores = 0;
        let totalRam = 0, bootTime = 0;
        
        // Get uname info
        if (metrics.system) {
            for (const metric of Object.values(metrics.system)) {
                if (metric && metric.name === 'node_uname_info' && metric.labels) {
                    const hostnameMatch = metric.labels.match(/nodename="([^"]+)"/);
                    if (hostnameMatch) hostname = hostnameMatch[1];
                    
                    const osMatch = metric.labels.match(/sysname="([^"]+)"/);
                    if (osMatch) os = osMatch[1];
                    
                    const kernelMatch = metric.labels.match(/release="([^"]+)"/);
                    if (kernelMatch) kernel = kernelMatch[1];
                }
            }
        }
        
        // Get CPU cores
        if (metrics.cpu) {
            const cpuCores = new Set();
            for (const metric of Object.values(metrics.cpu)) {
                if (metric && metric.name === 'node_cpu_seconds_total' && metric.labels) {
                    const cpuMatch = metric.labels.match(/cpu="([^"]+)"/);
                    if (cpuMatch) cpuCores.add(cpuMatch[1]);
                }
            }
            cores = cpuCores.size;
        }
        
        // Get total RAM
        if (metrics.memory) {
            for (const metric of Object.values(metrics.memory)) {
                if (metric && metric.name === 'node_memory_MemTotal_bytes') {
                    totalRam = metric.value;
                } else if (metric && metric.name === 'node_memory_size_bytes') {
                    totalRam = metric.value;
                }
            }
        }
        
        // Get boot time / uptime
        if (metrics.system) {
            for (const metric of Object.values(metrics.system)) {
                if (metric && metric.name === 'node_boot_time_seconds') {
                    bootTime = metric.value;
                }
            }
        }
        
        const uptime = bootTime > 0 ? this.formatUptime(Date.now() / 1000 - bootTime) : 'Unknown';
        const totalRamGB = (totalRam / (1024 * 1024 * 1024)).toFixed(2);
        
        // Build info rows
        systemInfo.push({ property: 'Hostname', value: hostname });
        systemInfo.push({ property: 'Operating System', value: os });
        systemInfo.push({ property: 'Kernel Version', value: kernel });
        systemInfo.push({ property: 'CPU Cores', value: cores });
        systemInfo.push({ property: 'Total RAM', value: `${totalRamGB} GB` });
        systemInfo.push({ property: 'Uptime', value: uptime });
        
        // Render rows
        systemInfo.forEach(info => {
            const tr = document.createElement('tr');
            
            const tdProperty = document.createElement('td');
            tdProperty.textContent = info.property;
            tdProperty.style.fontWeight = 'bold';
            tr.appendChild(tdProperty);
            
            const tdValue = document.createElement('td');
            tdValue.textContent = info.value;
            tr.appendChild(tdValue);
            
            tbody.appendChild(tr);
        });
    }
    
    formatUptime(seconds) {
        const days = Math.floor(seconds / 86400);
        const hours = Math.floor((seconds % 86400) / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        
        if (days > 0) {
            return `${days}d ${hours}h ${minutes}m`;
        } else if (hours > 0) {
            return `${hours}h ${minutes}m`;
        } else {
            return `${minutes}m`;
        }
    }
    
    getBestUnit(values, isBits = false) {
        const validValues = values.filter(v => v > 0 && isFinite(v));
        const maxValue = validValues.length > 0 ? Math.max(...validValues) : 0;
        
        if (maxValue === 0 || !isFinite(maxValue)) {
            return { divisor: 1, unit: isBits ? 'bits' : 'Bytes' };
        }
        
        const k = isBits ? 1000 : 1024;
        let sizes, value;
        
        if (isBits) {
            value = maxValue * 8;
            sizes = ['bits', 'Kbits', 'Mbits', 'Gbits', 'Tbits'];
        } else {
            value = maxValue;
            sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        }
        
        const i = Math.max(0, Math.min(sizes.length - 1, Math.floor(Math.log(value) / Math.log(k))));
        const divisor = isBits ? Math.pow(k, i) / 8 : Math.pow(k, i);
        
        return { divisor: divisor || 1, unit: sizes[i] || 'Bytes' };
    }
    
    formatBytes(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }
    
    hexToRgba(hex, alpha) {
        const r = parseInt(hex.slice(1, 3), 16);
        const g = parseInt(hex.slice(3, 5), 16);
        const b = parseInt(hex.slice(5, 7), 16);
        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    }
    
    checkAutoRefresh() {
        const enabled = document.getElementById('auto-refresh').checked;
        
        if (this.autoRefreshInterval) {
            clearInterval(this.autoRefreshInterval);
            this.autoRefreshInterval = null;
        }
        
        if (enabled && this.selectedNode) {
            this.autoRefreshInterval = setInterval(() => {
                this.loadMetrics(this.selectedNode);
            }, 5000);
        }
    }
    
    showEmptyState() {
        document.getElementById('empty-state').style.display = 'flex';
        document.getElementById('node-info').style.display = 'none';
        document.getElementById('metric-cards').innerHTML = '';
        document.getElementById('charts-container').innerHTML = '';
        document.getElementById('tables-container').innerHTML = '';
    }
    
    showError(message) {
        // Could add UI notification here
    }
    
    async loadHistoricalData() {
        if (!this.selectedNode || this.timeRange === 'live') {
            return;
        }
        
        const hours = parseInt(this.timeRange);
        console.log(`📊 Loading historical data for ${this.selectedNode}, last ${hours} hours...`);
        
        try {
            // Lade aggregierte Metriken für alle Chart-Typen
            const metricTypes = ['cpu', 'memory', 'disk', 'network', 'load', 'system'];
            
            // Bestimme Bucket-Größe basierend auf Zeitraum
            let bucketSize = 60; // 1 Minute
            if (hours >= 24) bucketSize = 300; // 5 Minuten
            if (hours >= 168) bucketSize = 1800; // 30 Minuten
            
            const promises = metricTypes.map(type =>
                fetch(`${this.apiBase}?action=aggregated&node=${this.selectedNode}&type=${type}&hours=${hours}&bucket=${bucketSize}`)
                    .then(r => r.json())
            );
            
            const results = await Promise.all(promises);
            
            let totalUpdates = 0;
            // Verarbeite die historischen Daten und aktualisiere Charts
            results.forEach((data, index) => {
                if (data.success && data.data.length > 0) {
                    const metricType = metricTypes[index];
                    this.updateChartsWithHistoricalData(metricType, data.data);
                    totalUpdates += data.data.length;
                } else if (data.success && data.data.length === 0) {
                    console.log(`⚠️ No historical data available for ${metricTypes[index]}`);
                }
            });
            
            console.log(`✅ Historical data loaded: ${totalUpdates} data points processed`);
            
        } catch (error) {
            console.error('❌ Error loading historical data:', error);
            this.showError('Fehler beim Laden der historischen Daten');
        }
    }
    
    updateChartsWithHistoricalData(metricType, aggregatedData) {
        console.log(`Updating ${metricType} charts with ${aggregatedData.length} aggregated rows`);
        
        // Gruppiere Daten nach Metrik-Name
        const metricGroups = {};
        aggregatedData.forEach(row => {
            if (!metricGroups[row.metric_name]) {
                metricGroups[row.metric_name] = [];
            }
            metricGroups[row.metric_name].push({
                timestamp: parseInt(row.bucket_time),
                value: parseFloat(row.avg_value),
                min: parseFloat(row.min_value),
                max: parseFloat(row.max_value)
            });
        });
        
        console.log(`Grouped into ${Object.keys(metricGroups).length} metrics:`, Object.keys(metricGroups));
        
        // Finde passende Charts und aktualisiere sie
        let updatedCharts = 0;
        const chartsToUpdate = new Set();
        
        Object.keys(this.charts).forEach(chartId => {
            const chart = this.charts[chartId];
            const config = this.chartConfigs[chartId];
            
            if (!config || !config.metrics) return;
            
            let chartUpdated = false;
            
            // Prüfe ob dieser Chart Metriken dieses Typs verwendet
            config.metrics.forEach((metricConfig, index) => {
                const metricName = metricConfig.name;
                
                if (metricGroups[metricName]) {
                    const historicalData = metricGroups[metricName];
                    
                    console.log(`Chart ${chartId}: Updating dataset ${index} with ${historicalData.length} points for ${metricName}`);
                    
                    // Ersetze Chart-Daten mit historischen Daten
                    const labels = historicalData.map(d => {
                        const date = new Date(d.timestamp * 1000);
                        return date.toLocaleString('de-DE', {
                            hour: '2-digit',
                            minute: '2-digit',
                            day: '2-digit',
                            month: '2-digit'
                        });
                    });
                    
                    const values = historicalData.map(d => d.value);
                    
                    // Update nur wenn Dataset existiert
                    if (chart.data.datasets[index]) {
                        // Bei erstem Dataset auch Labels setzen
                        if (index === 0) {
                            chart.data.labels = labels;
                        }
                        chart.data.datasets[index].data = values;
                        updatedCharts++;
                        chartUpdated = true;
                        
                        console.log(`  ✓ Dataset ${index}: ${values.length} values`);
                    }
                }
            });
            
            if (chartUpdated) {
                chartsToUpdate.add(chartId);
            }
        });
        
        // Update alle geänderten Charts auf einmal
        chartsToUpdate.forEach(chartId => {
            this.charts[chartId].update('none');
        });
        
        console.log(`✓ Updated ${updatedCharts} datasets in ${chartsToUpdate.size} charts for ${metricType}`);
    }
}

// Initialize dashboard when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new DynamicDashboard();
});
