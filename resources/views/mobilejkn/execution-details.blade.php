<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Execution Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .status-badge {
            @apply px-3 py-1 rounded-full text-xs font-semibold;
        }
        .status-pending {
            @apply bg-gray-100 text-gray-800;
        }
        .status-processing {
            @apply bg-blue-100 text-blue-800;
        }
        .status-completed {
            @apply bg-green-100 text-green-800;
        }
        .status-failed {
            @apply bg-red-100 text-red-800;
        }
        .status-skipped {
            @apply bg-yellow-100 text-yellow-800;
        }
        .pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: .5;
            }
        }
        .scroll-smooth-container {
            scroll-behavior: smooth;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <div class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Task Execution Details</h1>
                        <p class="text-sm text-gray-500 mt-1">Job ID: <code class="bg-gray-100 px-2 py-1 rounded">{{ $jobId }}</code></p>
                    </div>
                    <div class="flex gap-2">
                        <button id="pauseBtn" onclick="togglePause()" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                            Pause
                        </button>
                        <button id="refreshBtn" onclick="refreshData()" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                            Refresh
                        </button>
                        <a href="javascript:history.back()" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                            Back
                        </a>
                    </div>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-4 gap-4 mt-6">
                    <div class="bg-gray-50 p-4 rounded">
                        <div class="text-sm text-gray-600">Total Bookings</div>
                        <div class="text-2xl font-bold text-gray-900" id="statsTotal">-</div>
                    </div>
                    <div class="bg-green-50 p-4 rounded">
                        <div class="text-sm text-green-600">Completed</div>
                        <div class="text-2xl font-bold text-green-900" id="statsCompleted">0</div>
                    </div>
                    <div class="bg-red-50 p-4 rounded">
                        <div class="text-sm text-red-600">Failed</div>
                        <div class="text-2xl font-bold text-red-900" id="statsFailed">0</div>
                    </div>
                    <div class="bg-blue-50 p-4 rounded">
                        <div class="text-sm text-blue-600">Pending</div>
                        <div class="text-2xl font-bold text-blue-900" id="statsPending">-</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <!-- Connection Status -->
            <div class="mb-4 flex items-center gap-2">
                <div class="flex items-center gap-2" id="connectionStatus">
                    <div class="w-3 h-3 bg-red-500 rounded-full pulse"></div>
                    <span class="text-sm text-red-600">Connecting...</span>
                </div>
            </div>

            <!-- Bookings Container -->
            <div id="bookingsContainer" class="space-y-4 scroll-smooth-container">
                <div class="text-center py-8">
                    <div class="inline-block">
                        <div class="spin-loader inline-block"></div>
                    </div>
                    <p class="text-gray-500 mt-4">Loading execution details...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        const jobId = '{{ $jobId }}';
        let isPaused = false;
        let eventSource = null;
        let totalTaskIds = [];
        let currentData = {};

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // First load initial data
            refreshData();
            // Then start streaming
            startStreaming();
        });

        function startStreaming() {
            if (eventSource) eventSource.close();
            
            eventSource = new EventSource(`/stream-execution/${jobId}`);
            
            eventSource.addEventListener('execution', function(e) {
                if (isPaused) return;
                
                const data = JSON.parse(e.data);
                currentData = data;
                updateUI();
            });

            eventSource.addEventListener('keep-alive', function() {
                updateConnectionStatus(true);
            });

            eventSource.addEventListener('error', function() {
                updateConnectionStatus(false);
                eventSource.close();
                // Reconnect after 3 seconds
                setTimeout(startStreaming, 3000);
            });

            updateConnectionStatus(true);
        }

        function updateConnectionStatus(connected) {
            const statusEl = document.getElementById('connectionStatus');
            if (connected) {
                statusEl.innerHTML = `
                    <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                    <span class="text-sm text-green-600">Connected</span>
                `;
            } else {
                statusEl.innerHTML = `
                    <div class="w-3 h-3 bg-red-500 rounded-full pulse"></div>
                    <span class="text-sm text-red-600">Disconnected</span>
                `;
            }
        }

        function refreshData() {
            fetch(`/execution-details/${jobId}`)
                .then(r => r.json())
                .then(data => {
                    currentData = data;
                    updateUI();
                })
                .catch(err => console.error('Error loading data:', err));
        }

        function updateUI() {
            if (!currentData || !currentData.bookings) return;

            // Update stats
            const bookings = Object.values(currentData.bookings || {});
            const completed = bookings.filter(b => b.overall_status === 'completed').length;
            const failed = bookings.filter(b => b.overall_status === 'failed').length;
            const pending = bookings.filter(b => b.overall_status === 'pending').length;

            document.getElementById('statsTotal').textContent = bookings.length;
            document.getElementById('statsCompleted').textContent = completed;
            document.getElementById('statsFailed').textContent = failed;
            document.getElementById('statsPending').textContent = pending;

            // Create task ID list if not exists
            if (currentData.summary && currentData.summary.task_ids) {
                totalTaskIds = currentData.summary.task_ids;
            }

            // Render bookings
            const container = document.getElementById('bookingsContainer');
            
            const bookingsHtml = bookings.map(booking => 
                renderBooking(booking, totalTaskIds)
            ).join('');

            if (bookingsHtml) {
                container.innerHTML = bookingsHtml;
            } else {
                container.innerHTML = `
                    <div class="text-center py-8 bg-white rounded-lg border border-gray-200">
                        <p class="text-gray-500">No booking data available yet.</p>
                    </div>
                `;
            }
        }

        function renderBooking(booking, taskIds) {
            const statusClass = {
                'completed': 'text-green-900 bg-green-50 border-green-200',
                'failed': 'text-red-900 bg-red-50 border-red-200',
                'pending': 'text-gray-900 bg-gray-50 border-gray-200',
                'processing': 'text-blue-900 bg-blue-50 border-blue-200'
            }[booking.overall_status] || 'text-gray-900 bg-gray-50 border-gray-200';

            const steps = booking.steps || {};
            
            return `
                <div class="bg-white border ${statusClass} rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                    <!-- Header -->
                    <div class="border-b bg-white p-4 flex justify-between items-center">
                        <div>
                            <div class="font-semibold text-lg">Booking: <code class="bg-gray-100 px-2 py-1 rounded">${booking.no_rawat}</code></div>
                            <div class="text-sm text-gray-600 mt-1">
                                Created: ${new Date(booking.created_at).toLocaleString()}
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="status-badge status-${booking.overall_status}">
                                ${booking.overall_status.toUpperCase()}
                            </span>
                            ${booking.failure_reason ? `
                                <div class="text-sm text-red-600 mt-2">
                                    ${booking.failure_reason}
                                </div>
                            ` : ''}
                        </div>
                    </div>

                    <!-- Task Steps Timeline -->
                    <div class="p-4">
                        <div class="space-y-3">
                            ${taskIds.map((taskId, idx) => {
                                const step = steps[taskId];
                                const isLast = idx === taskIds.length - 1;
                                const isFirst = idx === 0;
                                
                                if (!step) {
                                    return `
                                        <div class="flex gap-4">
                                            <div class="flex flex-col items-center">
                                                <div class="w-8 h-8 rounded-full border-2 border-gray-300 flex items-center justify-center text-xs font-semibold text-gray-500">
                                                    ${taskId}
                                                </div>
                                                ${!isLast ? '<div class="w-0.5 h-8 bg-gray-300 mt-1"></div>' : ''}
                                            </div>
                                            <div class="flex-1 py-1">
                                                <div class="text-sm text-gray-500">Task ID ${taskId}: Pending</div>
                                            </div>
                                        </div>
                                    `;
                                }
                                
                                const statusColors = {
                                    'completed': 'bg-green-500 border-green-500',
                                    'failed': 'bg-red-500 border-red-500',
                                    'processing': 'bg-blue-500 border-blue-500',
                                    'skipped': 'bg-yellow-500 border-yellow-500',
                                    'pending': 'bg-gray-300 border-gray-300'
                                };
                                
                                const bgColor = statusColors[step.status] || 'bg-gray-300 border-gray-300';
                                
                                return `
                                    <div class="flex gap-4">
                                        <div class="flex flex-col items-center">
                                            <div class="w-8 h-8 rounded-full border-2 ${bgColor} flex items-center justify-center text-xs font-semibold text-white">
                                                ${step.status === 'completed' ? '✓' : 
                                                  step.status === 'failed' ? '✕' : 
                                                  step.status === 'processing' ? '...' : taskId}
                                            </div>
                                            ${!isLast ? '<div class="w-0.5 h-12 ' + (step.status === 'completed' ? 'bg-green-500' : step.status === 'failed' ? 'bg-red-500' : 'bg-gray-300') + ' mt-1"></div>' : ''}
                                        </div>
                                        <div class="flex-1 py-1">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <div class="font-semibold text-sm">
                                                        Task ID ${step.task_id}
                                                        <span class="status-badge status-${step.status} ml-2">
                                                            ${step.status.toUpperCase()}
                                                        </span>
                                                    </div>
                                                    <div class="text-xs text-gray-600 mt-1">
                                                        ${new Date(step.timestamp).toLocaleString()}
                                                    </div>
                                                    ${step.duration ? `
                                                        <div class="text-xs text-gray-500">
                                                            Duration: ${step.duration}s
                                                        </div>
                                                    ` : ''}
                                                </div>
                                            </div>
                                            ${step.response ? `
                                                <div class="bg-gray-100 p-2 rounded mt-2 text-xs font-mono" style="max-height: 100px; overflow-y: auto;">
                                                    <div class="text-gray-700 whitespace-pre-wrap word-break">${escapeHtml(step.response.substring(0, 200))}</div>
                                                    ${step.response.length > 200 ? '<div class="text-gray-500 mt-1">...</div>' : ''}
                                                </div>
                                            ` : ''}
                                        </div>
                                    </div>
                                `;
                            }).join('')}
                        </div>
                    </div>

                    <!-- Footer -->
                    ${booking.completed_at || booking.failed_at ? `
                        <div class="border-t bg-gray-50 px-4 py-2 text-xs text-gray-600">
                            ${booking.completed_at ? `Completed: ${new Date(booking.completed_at).toLocaleString()}` : ''}
                            ${booking.failed_at ? `Failed: ${new Date(booking.failed_at).toLocaleString()}` : ''}
                        </div>
                    ` : ''}
                </div>
            `;
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function togglePause() {
            isPaused = !isPaused;
            const btn = document.getElementById('pauseBtn');
            btn.textContent = isPaused ? 'Resume' : 'Pause';
            btn.classList.toggle('bg-yellow-500');
            btn.classList.toggle('hover:bg-yellow-600');
            btn.classList.toggle('bg-blue-500');
            btn.classList.toggle('hover:bg-blue-600');
        }

        // Auto-update every 2 seconds if not paused and streaming fails
        setInterval(() => {
            if (!isPaused && (!eventSource || eventSource.readyState !== 1)) {
                refreshData();
            }
        }, 2000);
    </script>
</body>
</html>
