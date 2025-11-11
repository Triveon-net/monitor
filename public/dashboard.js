// Dashboard JavaScript with Chart.js Integration

class MonitoringDashboard {
    constructor() {
        this.apiBase = '/resources/system/api/node_exporter.php';
        this.charts = {};
        this.selectedNode = null;
        this.autoRefreshInterval = null;
        this.historyData = {
            cpu: [],
            memory: [],
            network: { rx: [], tx: [] },
            disk: { read: [], write: [] },
            timestamps: []
        };
        this.maxDataPoints = 20;
        
        this.init();
    }
    
    // Format bytes to appropriate unit (bits or bytes based)
    formatBytes(bytes, isBits = false, decimals = 2) {
        if (bytes === 0) return '0 ' + (isBits ? 'bits' : 'Bytes');
        
        const k = isBits ? 1000 : 1024; // Use 1000 for bits, 1024 for bytes
        const dm = decimals < 0 ? 0 : decimals;
        
        let sizes, value;
        if (isBits) {
            // Convert bytes to bits
            value = bytes * 8;
            sizes = ['bits', 'Kbits', 'Mbits', 'Gbits', 'Tbits'];
        } else {
            value = bytes;
            sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        }
        
        const i = Math.floor(Math.log(value) / Math.log(k));
        return parseFloat((value / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }
    
    // Get the best unit for a dataset (so all values use the same unit)
    getBestUnit(values, isBits = false) {
        const maxValue = Math.max(...values.filter(v => v > 0));
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
        
        const i = Math.floor(Math.log(value) / Math.log(k));
        const divisor = isBits ? Math.pow(k, i) / 8 : Math.pow(k, i);
        
        return { divisor, unit: sizes[i] };
    }
    
    async init() {
        await this.loadInstances();
        this.setupEventListeners();
        this.initCharts();
        this.checkAutoRefresh();
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
        const selector = document.getElementById('node-selector');
        selector.innerHTML = '<option value="">Wähle einen Node...</option>';
        
        instances.forEach(instance => {
            if (instance.enabled) {
                const option = document.createElement('option');
                option.value = instance.id;
                option.textContent = `${instance.title} (${instance.host}:${instance.port})`;
                selector.appendChild(option);
            }
        });
    }
    
    setupEventListeners() {
        document.getElementById('node-selector').addEventListener('change', (e) => {
            this.selectNode(e.target.value);
        });
        
        document.getElementById('refresh-btn').addEventListener('click', () => {
            if (this.selectedNode) {
                this.loadMetrics(this.selectedNode);
            }
        });
        
        document.getElementById('auto-refresh').addEventListener('change', (e) => {
            this.checkAutoRefresh();
        });
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
            }, 1000); // 1 second
        }
    }
    
    selectNode(nodeId) {
        if (!nodeId) {
            this.showEmptyState();
            return;
        }
        
        this.selectedNode = nodeId;
        this.historyData = {
            cpu: [],
            memory: [],
            network: { rx: [], tx: [] },
            disk: { read: [], write: [] },
            timestamps: []
        };
        this.lastNetworkValues = null;
        this.lastDiskValues = null;
        
        this.loadMetrics(nodeId);
        this.checkAutoRefresh();
    }
    
    async loadMetrics(nodeId) {
        try {
            const response = await fetch(`${this.apiBase}?action=metrics&node=${nodeId}`);
            
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            
            const text = await response.text();
            console.log('Raw response:', text.substring(0, 500));
            
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('JSON parse error:', e);
                this.showError('Invalid JSON response from API. Check console for details.');
                return;
            }
            
            console.log('Parsed data:', data);
            console.log('Data keys:', Object.keys(data));
            
            if (data.success) {
                this.hideEmptyState();
                console.log('Data structure:', {
                    hasData: !!data.data,
                    hasMetrics: !!(data.data && data.data.metrics),
                    hasInstance: !!(data.data && data.data.instance),
                    dataKeys: data.data ? Object.keys(data.data) : []
                });
                
                if (data.data && data.data.metrics) {
                    console.log('Metrics:', {
                        cpu: Object.keys(data.data.metrics.cpu).length,
                        memory: Object.keys(data.data.metrics.memory).length,
                        disk: Object.keys(data.data.metrics.disk).length,
                        network: Object.keys(data.data.metrics.network).length
                    });
                    this.updateDashboard(data.data);
                } else {
                    this.showError('API returned success but no metrics data');
                }
            } else {
                this.showError('Failed to load metrics: ' + (data.error || 'Unknown error'));
            }
        } catch (error) {
            this.showError('Error loading metrics: ' + error.message);
            console.error('Load error:', error);
        }
    }
    
    updateDashboard(data) {
        console.log('Update dashboard with data:', data);
        
        if (!data || !data.metrics) {
            console.error('Invalid data structure:', data);
            this.showError('Invalid data received from API');
            return;
        }
        
        if (data.instance) {
            this.updateNodeInfo(data.instance);
        }
        
        this.updateMetricCards(data.metrics);
        this.updateHistory(data.metrics);
        this.updateCharts();
        this.updateTables(data.metrics);
    }
    
    updateNodeInfo(instance) {
        if (!instance) {
            console.warn('No instance info provided');
            return;
        }
        
        const info = document.getElementById('node-info');
        if (info) {
            info.style.display = 'block';
        }
        
        const titleEl = document.getElementById('node-title');
        const hostEl = document.getElementById('node-host');
        const statusEl = document.getElementById('node-status');
        
        if (titleEl && instance.title) {
            titleEl.textContent = instance.title;
        }
        if (hostEl && instance.host && instance.port) {
            hostEl.textContent = `${instance.host}:${instance.port}`;
        }
        if (statusEl) {
            statusEl.style.color = '#7eb26d';
        }
    }
    
    updateMetricCards(metrics) {
        // CPU Usage
        const cpuUsage = this.calculateCPUUsage(metrics.cpu);
        console.log('CPU Usage:', cpuUsage, 'from', Object.keys(metrics.cpu).length, 'metrics');
        document.getElementById('cpu-value').textContent = `${cpuUsage.toFixed(1)}%`;
        this.updateGauge('cpu-gauge', cpuUsage);
        
        // Memory Usage
        const memoryUsage = this.calculateMemoryUsage(metrics.memory);
        console.log('Memory Usage:', memoryUsage, 'from', Object.keys(metrics.memory).length, 'metrics');
        document.getElementById('memory-value').textContent = `${memoryUsage.toFixed(1)}%`;
        this.updateGauge('memory-gauge', memoryUsage);
        
        // Disk Usage
        const diskUsage = this.calculateDiskUsage(metrics.disk);
        console.log('Disk Usage:', diskUsage, 'from', Object.keys(metrics.disk).length, 'metrics');
        document.getElementById('disk-value').textContent = `${diskUsage.toFixed(1)}%`;
        this.updateGauge('disk-gauge', diskUsage);
        
        // System Load
        const load = this.getSystemLoad(metrics.cpu);
        console.log('System Load:', load);
        document.getElementById('load-value').textContent = load.load1;
        document.getElementById('load-subtext').textContent = `${load.load1} / ${load.load5} / ${load.load15}`;
    }
    
    calculateCPUUsage(cpuMetrics) {
        // Calculate CPU usage from node_cpu_seconds_total
        let totalIdle = 0;
        let totalActive = 0;
        let count = 0;
        
        for (const [key, value] of Object.entries(cpuMetrics)) {
            if (key.includes('node_cpu_seconds_total') && value.name === 'node_cpu_seconds_total') {
                const labels = value.labels || '';
                if (labels.includes('mode="idle"') || labels.includes('mode="iowait"')) {
                    totalIdle += value.value;
                } else if (labels.includes('mode=')) {
                    totalActive += value.value;
                }
                if (labels.includes('mode=')) count++;
            }
        }
        
        const total = totalIdle + totalActive;
        return total > 0 ? ((totalActive / total) * 100) : 0;
    }
    
    calculateMemoryUsage(memoryMetrics) {
        let total = 0;
        let free = 0;
        let available = 0;
        
        for (const [key, value] of Object.entries(memoryMetrics)) {
            const name = value.name || key;
            
            // Try different memory metric names (Linux/FreeBSD)
            if (name === 'node_memory_MemTotal_bytes' || name === 'node_memory_size_bytes') {
                total = value.value;
            } else if (name === 'node_memory_MemAvailable_bytes') {
                available = value.value;
            } else if (name === 'node_memory_MemFree_bytes' || name === 'node_memory_free_bytes') {
                free = value.value;
            }
        }
        
        // Use available if present, otherwise use free
        const usedMemory = available > 0 ? (total - available) : (total - free);
        return total > 0 ? ((usedMemory / total) * 100) : 0;
    }
    
    calculateDiskUsage(diskMetrics) {
        let total = 0;
        let free = 0;
        
        for (const [key, value] of Object.entries(diskMetrics)) {
            const name = value.name || '';
            const labels = value.labels || '';
            
            // Skip tmpfs and other virtual filesystems
            if (labels.includes('tmpfs') || labels.includes('devfs')) {
                continue;
            }
            
            if (name === 'node_filesystem_size_bytes') {
                total += value.value;
            } else if (name === 'node_filesystem_free_bytes') {
                free += value.value;
            }
        }
        
        return total > 0 ? (((total - free) / total) * 100) : 0;
    }
    
    getSystemLoad(cpuMetrics) {
        const defaults = { load1: '--', load5: '--', load15: '--' };
        
        for (const [key, value] of Object.entries(cpuMetrics)) {
            const name = value.name || key;
            
            if (name === 'node_load1') defaults.load1 = value.value.toFixed(2);
            if (name === 'node_load5') defaults.load5 = value.value.toFixed(2);
            if (name === 'node_load15') defaults.load15 = value.value.toFixed(2);
        }
        
        return defaults;
    }
    
    updateHistory(metrics) {
        const now = new Date();
        const timeLabel = now.toLocaleTimeString();
        
        // Add timestamp
        this.historyData.timestamps.push(timeLabel);
        
        // Add CPU
        this.historyData.cpu.push(this.calculateCPUUsage(metrics.cpu));
        
        // Add Memory
        this.historyData.memory.push(this.calculateMemoryUsage(metrics.memory));
        
        // Add Network - calculate delta if we have previous values
        let rxBytes = 0, txBytes = 0;
        for (const [key, value] of Object.entries(metrics.network)) {
            const name = value.name || '';
            if (name === 'node_network_receive_bytes_total') {
                rxBytes += value.value;
            }
            if (name === 'node_network_transmit_bytes_total') {
                txBytes += value.value;
            }
        }
        
        // Store current values for delta calculation
        if (!this.lastNetworkValues) {
            this.lastNetworkValues = { rx: rxBytes, tx: txBytes, time: Date.now() };
            this.historyData.network.rx.push(0);
            this.historyData.network.tx.push(0);
        } else {
            const timeDiff = (Date.now() - this.lastNetworkValues.time) / 1000; // seconds
            const rxDiff = (rxBytes - this.lastNetworkValues.rx) / timeDiff; // bytes per second
            const txDiff = (txBytes - this.lastNetworkValues.tx) / timeDiff;
            
            this.historyData.network.rx.push(rxDiff / 1024 / 1024); // MB/s
            this.historyData.network.tx.push(txDiff / 1024 / 1024); // MB/s
            
            this.lastNetworkValues = { rx: rxBytes, tx: txBytes, time: Date.now() };
        }
        
        // Add Disk I/O
        let readBytes = 0, writeBytes = 0;
        for (const [key, value] of Object.entries(metrics.disk)) {
            const name = value.name || '';
            const labels = value.labels || '';
            
            if (name === 'node_devstat_bytes_total' && labels.includes('type="read"')) {
                readBytes += value.value;
            }
            if (name === 'node_devstat_bytes_total' && labels.includes('type="write"')) {
                writeBytes += value.value;
            }
        }
        
        // Calculate disk I/O delta
        if (!this.lastDiskValues) {
            this.lastDiskValues = { read: readBytes, write: writeBytes, time: Date.now() };
            this.historyData.disk.read.push(0);
            this.historyData.disk.write.push(0);
        } else {
            const timeDiff = (Date.now() - this.lastDiskValues.time) / 1000;
            const readDiff = (readBytes - this.lastDiskValues.read) / timeDiff;
            const writeDiff = (writeBytes - this.lastDiskValues.write) / timeDiff;
            
            this.historyData.disk.read.push(readDiff / 1024 / 1024); // MB/s
            this.historyData.disk.write.push(writeDiff / 1024 / 1024);
            
            this.lastDiskValues = { read: readBytes, write: writeBytes, time: Date.now() };
        }
        
        // Keep only last N data points
        if (this.historyData.timestamps.length > this.maxDataPoints) {
            this.historyData.timestamps.shift();
            this.historyData.cpu.shift();
            this.historyData.memory.shift();
            this.historyData.network.rx.shift();
            this.historyData.network.tx.shift();
            this.historyData.disk.read.shift();
            this.historyData.disk.write.shift();
        }
    }
    
    initCharts() {
        const chartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: { color: '#d8d9da' }
                }
            },
            scales: {
                x: {
                    ticks: { color: '#9fa0a3' },
                    grid: { color: '#2d2d2d' }
                },
                y: {
                    ticks: { color: '#9fa0a3' },
                    grid: { color: '#2d2d2d' }
                }
            }
        };
        
        // CPU Chart
        const cpuCtx = document.getElementById('cpu-chart').getContext('2d');
        this.charts.cpu = new Chart(cpuCtx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'CPU Usage (%)',
                    data: [],
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: { ...chartOptions, scales: { ...chartOptions.scales, y: { ...chartOptions.scales.y, max: 100 } } }
        });
        
        // Memory Chart
        const memCtx = document.getElementById('memory-chart').getContext('2d');
        this.charts.memory = new Chart(memCtx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Memory Usage (%)',
                    data: [],
                    borderColor: '#f5576c',
                    backgroundColor: 'rgba(245, 87, 108, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: { ...chartOptions, scales: { ...chartOptions.scales, y: { ...chartOptions.scales.y, max: 100 } } }
        });
        
        // Network Chart
        const netCtx = document.getElementById('network-chart').getContext('2d');
        this.charts.network = new Chart(netCtx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [
                    {
                        label: 'RX',
                        data: [],
                        borderColor: '#7eb26d',
                        backgroundColor: 'rgba(126, 178, 109, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'TX',
                        data: [],
                        borderColor: '#ff9830',
                        backgroundColor: 'rgba(255, 152, 48, 0.1)',
                        tension: 0.4
                    }
                ]
            },
            options: {
                ...chartOptions,
                plugins: {
                    ...chartOptions.plugins,
                    tooltip: {
                        callbacks: {
                            label: (context) => {
                                const label = context.dataset.label || '';
                                const value = context.parsed.y;
                                return `${label}: ${value.toFixed(2)}`;
                            }
                        }
                    }
                }
            }
        });
        
        // Disk Chart
        const diskCtx = document.getElementById('disk-chart').getContext('2d');
        this.charts.disk = new Chart(diskCtx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [
                    {
                        label: 'Read',
                        data: [],
                        borderColor: '#33b5e5',
                        backgroundColor: 'rgba(51, 181, 229, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Write',
                        data: [],
                        borderColor: '#9954bb',
                        backgroundColor: 'rgba(153, 84, 187, 0.1)',
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                ...chartOptions,
                plugins: {
                    ...chartOptions.plugins,
                    tooltip: {
                        callbacks: {
                            label: (context) => {
                                const label = context.dataset.label || '';
                                const value = context.parsed.y;
                                return `${label}: ${value.toFixed(2)}`;
                            }
                        }
                    }
                }
            }
        });
    }
    
    updateCharts() {
        // CPU Chart
        this.charts.cpu.data.labels = this.historyData.timestamps;
        this.charts.cpu.data.datasets[0].data = this.historyData.cpu;
        this.charts.cpu.update('none'); // No animation for smoother updates
        
        // Memory Chart
        this.charts.memory.data.labels = this.historyData.timestamps;
        this.charts.memory.data.datasets[0].data = this.historyData.memory;
        this.charts.memory.update('none');
        
        // Network Chart - dynamic unit
        const allNetworkValues = [...this.historyData.network.rx, ...this.historyData.network.tx];
        const netUnit = this.getBestUnit(allNetworkValues, true);
        
        this.charts.network.data.labels = this.historyData.timestamps;
        this.charts.network.data.datasets[0].data = this.historyData.network.rx.map(v => v / netUnit.divisor);
        this.charts.network.data.datasets[1].data = this.historyData.network.tx.map(v => v / netUnit.divisor);
        this.charts.network.data.datasets[0].label = `RX (${netUnit.unit}/s)`;
        this.charts.network.data.datasets[1].label = `TX (${netUnit.unit}/s)`;
        
        // Update Y-axis to show numbers without unit
        this.charts.network.options.scales.y.ticks.callback = (value) => value.toFixed(2);
        this.charts.network.options.plugins.tooltip.callbacks.label = (context) => {
            const label = context.dataset.label || '';
            const value = context.parsed.y;
            return `${label.split(' ')[0]}: ${value.toFixed(2)} ${netUnit.unit}/s`;
        };
        
        this.charts.network.update('none');
        
        // Disk Chart - dynamic unit
        const allDiskValues = [...this.historyData.disk.read, ...this.historyData.disk.write];
        const diskUnit = this.getBestUnit(allDiskValues, false);
        
        this.charts.disk.data.labels = this.historyData.timestamps;
        this.charts.disk.data.datasets[0].data = this.historyData.disk.read.map(v => v / diskUnit.divisor);
        this.charts.disk.data.datasets[1].data = this.historyData.disk.write.map(v => v / diskUnit.divisor);
        this.charts.disk.data.datasets[0].label = `Read (${diskUnit.unit}/s)`;
        this.charts.disk.data.datasets[1].label = `Write (${diskUnit.unit}/s)`;
        
        // Update Y-axis to show numbers without unit
        this.charts.disk.options.scales.y.ticks.callback = (value) => value.toFixed(2);
        this.charts.disk.options.plugins.tooltip.callbacks.label = (context) => {
            const label = context.dataset.label || '';
            const value = context.parsed.y;
            return `${label.split(' ')[0]}: ${value.toFixed(2)} ${diskUnit.unit}/s`;
        };
        
        this.charts.disk.update('none');
    }
    
    updateGauge(canvasId, value) {
        const canvas = document.getElementById(canvasId);
        const ctx = canvas.getContext('2d');
        
        // Create chart if it doesn't exist
        if (!this.charts[canvasId]) {
            this.charts[canvasId] = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: [value, 100 - value],
                        backgroundColor: [
                            value > 80 ? '#e24d42' : value > 60 ? '#ff9830' : '#7eb26d',
                            '#2d2d2d'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '75%',
                    animation: false, // Disable animation
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: false }
                    }
                }
            });
        } else {
            // Update existing chart data without animation
            this.charts[canvasId].data.datasets[0].data = [value, 100 - value];
            this.charts[canvasId].data.datasets[0].backgroundColor = [
                value > 80 ? '#e24d42' : value > 60 ? '#ff9830' : '#7eb26d',
                '#2d2d2d'
            ];
            this.charts[canvasId].update('none'); // Update without animation
        }
    }
    
    updateTables(metrics) {
        this.updateFilesystemTable(metrics.disk);
        this.updateNetworkTable(metrics.network);
    }
    
    updateFilesystemTable(diskMetrics) {
        const tbody = document.querySelector('#filesystem-table tbody');
        tbody.innerHTML = '';
        
        const filesystems = {};
        
        for (const [key, value] of Object.entries(diskMetrics)) {
            const name = value.name || '';
            const labels = value.labels || '';
            
            // Skip tmpfs and devfs
            if (labels.includes('tmpfs') || labels.includes('devfs')) {
                continue;
            }
            
            const mountMatch = labels.match(/mountpoint="([^"]+)"/);
            if (mountMatch) {
                const mount = mountMatch[1];
                if (!filesystems[mount]) filesystems[mount] = {};
                
                if (name === 'node_filesystem_size_bytes') {
                    filesystems[mount].size = value.value;
                }
                if (name === 'node_filesystem_free_bytes') {
                    filesystems[mount].free = value.value;
                }
                if (name === 'node_filesystem_avail_bytes') {
                    filesystems[mount].avail = value.value;
                }
            }
        }
        
        for (const [mount, data] of Object.entries(filesystems)) {
            if (data.size) {
                const used = data.size - (data.free || 0);
                const usagePercent = ((used / data.size) * 100).toFixed(1);
                
                const row = tbody.insertRow();
                row.innerHTML = `
                    <td><strong>${mount}</strong></td>
                    <td>${this.formatBytes(data.size)}</td>
                    <td>${this.formatBytes(used)}</td>
                    <td>${this.formatBytes(data.avail || data.free)}</td>
                    <td>
                        <span class="status-badge ${usagePercent > 80 ? 'status-warning' : 'status-online'}">
                            ${usagePercent}%
                        </span>
                    </td>
                `;
            }
        }
        
        if (tbody.children.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="no-data">Keine Daten verfügbar</td></tr>';
        }
    }
    
    updateNetworkTable(networkMetrics) {
        const tbody = document.querySelector('#network-table tbody');
        tbody.innerHTML = '';
        
        const interfaces = {};
        
        for (const [key, value] of Object.entries(networkMetrics)) {
            const name = value.name || '';
            const labels = value.labels || '';
            
            const deviceMatch = labels.match(/device="([^"]+)"/);
            if (deviceMatch) {
                const iface = deviceMatch[1];
                if (!interfaces[iface]) {
                    interfaces[iface] = { rx: 0, tx: 0, rxErrors: 0, txErrors: 0 };
                }
                
                if (name === 'node_network_receive_bytes_total') {
                    interfaces[iface].rx = value.value;
                }
                if (name === 'node_network_transmit_bytes_total') {
                    interfaces[iface].tx = value.value;
                }
                if (name === 'node_network_receive_errs_total') {
                    interfaces[iface].rxErrors = value.value;
                }
                if (name === 'node_network_transmit_errs_total') {
                    interfaces[iface].txErrors = value.value;
                }
            }
        }
        
        for (const [iface, data] of Object.entries(interfaces)) {
            // Skip loopback interfaces
            if (iface === 'lo' || iface === 'lo0') {
                continue;
            }
            
            const totalErrors = data.rxErrors + data.txErrors;
            const hasTraffic = data.rx > 0 || data.tx > 0;
            
            const row = tbody.insertRow();
            row.innerHTML = `
                <td><strong>${iface}</strong></td>
                <td>${this.formatBytes(data.rx)}</td>
                <td>${this.formatBytes(data.tx)}</td>
                <td>${totalErrors}</td>
                <td><span class="status-badge ${hasTraffic ? 'status-online' : 'status-warning'}">
                    ${hasTraffic ? 'UP' : 'DOWN'}
                </span></td>
            `;
        }
        
        if (tbody.children.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="no-data">Keine Daten verfügbar</td></tr>';
        }
    }
    
    formatBytes(bytes) {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    showEmptyState() {
        document.getElementById('node-info').style.display = 'none';
        document.querySelector('.metrics-grid').style.display = 'none';
        document.querySelector('.charts-grid').style.display = 'none';
        document.querySelector('.tables-grid').style.display = 'none';
    }
    
    hideEmptyState() {
        document.getElementById('node-info').style.display = 'block';
        document.querySelector('.metrics-grid').style.display = 'grid';
        document.querySelector('.charts-grid').style.display = 'grid';
        document.querySelector('.tables-grid').style.display = 'grid';
    }
    
    showError(message) {
        console.error('Error:', message);
        
        // Show error in UI instead of alert
        const metricsGrid = document.querySelector('.metrics-grid');
        if (metricsGrid) {
            const errorDiv = document.createElement('div');
            errorDiv.style.cssText = 'grid-column: 1/-1; padding: 20px; background: rgba(226, 77, 66, 0.2); border: 1px solid #e24d42; border-radius: 8px; color: #e24d42;';
            errorDiv.innerHTML = `<strong><i class="fas fa-exclamation-triangle"></i> Error:</strong> ${message}`;
            metricsGrid.insertBefore(errorDiv, metricsGrid.firstChild);
            
            // Remove error after 5 seconds
            setTimeout(() => errorDiv.remove(), 5000);
        }
    }
}

// Initialize dashboard when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new MonitoringDashboard();
});
