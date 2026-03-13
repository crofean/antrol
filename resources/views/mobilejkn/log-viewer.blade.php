@extends('layouts.main')

@section('title', 'Real-time Log Viewer')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-12">
    <!-- Header -->
    <div class="glass rounded-3xl p-8 mb-8 space-y-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div>
                <h1 class="text-4xl font-bold tracking-tight text-slate-900 dark:text-white">Real-time Logs</h1>
                <p class="text-slate-500 dark:text-slate-400 mt-2 flex items-center">
                    <i class="fas fa-stream mr-2 text-blue-600"></i>
                    Live Laravel application logs
                </p>
            </div>
            
            <div class="flex items-center gap-3">
                <span id="connectionStatus" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-amber-50 dark:bg-amber-900/10 text-amber-700 dark:text-amber-300 text-sm font-semibold">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    Connecting...
                </span>
                <a href="{{ route('command.index') }}"
                   class="glass px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-slate-100 dark:hover:bg-slate-800 transition-all flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Batch Processor
                </a>
            </div>
        </div>
    </div>

    <!-- Logs Display -->
    <div class="glass rounded-3xl shadow-2xl overflow-hidden">
        <div class="px-8 py-6 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-slate-900">
            <div class="flex items-center gap-4">
                <h2 class="text-xl font-bold text-white flex items-center">
                    <i class="fas fa-file-lines mr-3 text-blue-400"></i>
                    Laravel Logs
                </h2>
                <span id="logCount" class="text-sm text-slate-400 bg-slate-800 px-3 py-1 rounded-full">0 lines</span>
            </div>
            <div class="flex items-center gap-3">
                <button id="pauseBtn" class="px-4 py-1.5 rounded-lg bg-amber-500 text-white text-[10px] font-bold uppercase transition-all hover:bg-amber-600">
                    Pause
                </button>
                <button id="clearBtn" class="px-4 py-1.5 rounded-lg bg-slate-700 text-white text-[10px] font-bold uppercase transition-all hover:bg-slate-600">
                    Clear
                </button>
                <button id="downloadBtn" class="px-4 py-1.5 rounded-lg bg-emerald-500 text-white text-[10px] font-bold uppercase transition-all hover:bg-emerald-600">
                    Download
                </button>
            </div>
        </div>
        
        <div id="logsContainer" class="h-[600px] bg-slate-900 p-8 font-mono text-[11px] leading-relaxed overflow-y-auto whitespace-pre-wrap break-words">
            <div class="text-slate-500 italic flex flex-col items-center justify-center h-full space-y-4">
                <i class="fas fa-spinner text-4xl opacity-10 animate-spin"></i>
                <span>Loading logs...</span>
            </div>
        </div>
    </div>

    <!-- Stats Footer -->
    <div class="grid grid-cols-4 gap-4 mt-8">
        <div class="glass rounded-2xl p-4">
            <div class="text-slate-500 text-sm font-semibold mb-1">Total Lines</div>
            <div id="totalLines" class="text-3xl font-bold text-slate-900 dark:text-white">0</div>
        </div>
        <div class="glass rounded-2xl p-4">
            <div class="text-slate-500 text-sm font-semibold mb-1">Errors</div>
            <div id="errorCount" class="text-3xl font-bold text-rose-600">0</div>
        </div>
        <div class="glass rounded-2xl p-4">
            <div class="text-slate-500 text-sm font-semibold mb-1">Warnings</div>
            <div id="warningCount" class="text-3xl font-bold text-amber-600">0</div>
        </div>
        <div class="glass rounded-2xl p-4">
            <div class="text-slate-500 text-sm font-semibold mb-1">Status</div>
            <div id="streamStatus" class="text-3xl font-bold text-emerald-600">Active</div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let isPaused = false;
    let logLines = [];
    let logsContainer = document.getElementById('logsContainer');
    let eventSource = null;

    // Initialize with recent logs
    function initializeLogs() {
        fetch('{{ route("logs.recent", 50) }}')
            .then(r => r.json())
            .then(data => {
                if (data.logs && data.logs.length > 0) {
                    logLines = data.logs;
                    renderLogs();
                }
                startStreamingLogs();
            })
            .catch(err => {
                console.error('Failed to load recent logs:', err);
                startStreamingLogs();
            });
    }

    // Start SSE streaming
    function startStreamingLogs() {
        if (eventSource) {
            eventSource.close();
        }

        eventSource = new EventSource('{{ route("logs.stream") }}');
        
        eventSource.addEventListener('log', (event) => {
            if (isPaused) return;

            const data = JSON.parse(event.data);
            logLines.push(data);

            // Keep only last 1000 lines in memory
            if (logLines.length > 1000) {
                logLines.shift();
            }

            renderLogs();
        });

        eventSource.addEventListener('keep-alive', (event) => {
            updateConnectionStatus(true);
        });

        eventSource.addEventListener('error', (event) => {
            console.error('Stream error:', event);
            updateConnectionStatus(false);
            setTimeout(startStreamingLogs, 3000); // Reconnect after 3 seconds
        });

        updateConnectionStatus(true);
    }

    // Render logs in container
    function renderLogs() {
        const fragment = document.createDocumentFragment();
        
        logLines.forEach((log, index) => {
            const div = document.createElement('div');
            
            let color = 'text-slate-400';
            let icon = '';

            if (log.type === 'error') {
                color = 'text-rose-500';
                icon = '✗ ';
            } else if (log.type === 'warning') {
                color = 'text-amber-400';
                icon = '⚠ ';
            } else if (log.type === 'debug') {
                color = 'text-blue-400';
                icon = '◆ ';
            } else {
                color = 'text-emerald-500';
                icon = '◈ ';
            }

            div.className = `${color} py-0.5`;
            div.textContent = icon + log.line;
            fragment.appendChild(div);
        });

        logsContainer.innerHTML = '';
        logsContainer.appendChild(fragment);
        logsContainer.scrollTop = logsContainer.scrollHeight;

        // Update stats
        updateStats();
    }

    // Update statistics
    function updateStats() {
        const totalLines = logLines.length;
        const errorCount = logLines.filter(l => l.type === 'error').length;
        const warningCount = logLines.filter(l => l.type === 'warning').length;

        document.getElementById('totalLines').textContent = totalLines;
        document.getElementById('errorCount').textContent = errorCount;
        document.getElementById('warningCount').textContent = warningCount;
        document.getElementById('logCount').textContent = `${totalLines} lines`;
    }

    // Update connection status
    function updateConnectionStatus(connected) {
        const status = document.getElementById('connectionStatus');
        if (connected) {
            status.innerHTML = '<span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Connected';
            status.className = 'flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-50 dark:bg-emerald-900/10 text-emerald-700 dark:text-emerald-300 text-sm font-semibold';
            document.getElementById('streamStatus').textContent = 'Active';
            document.getElementById('streamStatus').className = 'text-3xl font-bold text-emerald-600';
        } else {
            status.innerHTML = '<span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span> Disconnected';
            status.className = 'flex items-center gap-2 px-4 py-2 rounded-xl bg-rose-50 dark:bg-rose-900/10 text-rose-700 dark:text-rose-300 text-sm font-semibold';
            document.getElementById('streamStatus').textContent = 'Inactive';
            document.getElementById('streamStatus').className = 'text-3xl font-bold text-rose-600';
        }
    }

    // Pause/Resume
    document.getElementById('pauseBtn').addEventListener('click', () => {
        isPaused = !isPaused;
        const btn = document.getElementById('pauseBtn');
        if (isPaused) {
            btn.textContent = 'Resume';
            btn.classList.remove('bg-amber-500', 'hover:bg-amber-600');
            btn.classList.add('bg-emerald-500', 'hover:bg-emerald-600');
        } else {
            btn.textContent = 'Pause';
            btn.classList.remove('bg-emerald-500', 'hover:bg-emerald-600');
            btn.classList.add('bg-amber-500', 'hover:bg-amber-600');
        }
    });

    // Clear logs
    document.getElementById('clearBtn').addEventListener('click', () => {
        logLines = [];
        logsContainer.innerHTML = '<div class="text-slate-500 italic text-center py-8">Logs cleared</div>';
        updateStats();
    });

    // Download logs
    document.getElementById('downloadBtn').addEventListener('click', () => {
        const logContent = logLines.map(l => l.line).join('\n');
        const blob = new Blob([logContent], { type: 'text/plain' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `laravel-logs-${new Date().toISOString().split('T')[0]}.log`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
    });

    // Initialize on page load
    initializeLogs();

    // Cleanup on page unload
    window.addEventListener('beforeunload', () => {
        if (eventSource) eventSource.close();
    });
</script>
@endpush
