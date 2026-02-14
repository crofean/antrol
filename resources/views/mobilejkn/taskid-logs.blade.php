@extends('mobilejkn.layout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Mobile JKN Task ID Logs</h1>
                <p class="text-gray-600 mt-1">Monitor and track BPJS Task ID updates and antrean additions</p>
            </div>
            <div class="flex space-x-4">
                <a href="{{ route('regperiksa.index') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Patients
                </a>
                <a href="{{ route('command.index') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md transition duration-200">
                    <i class="fas fa-play mr-2"></i>Run Command
                </a>
                <a href="{{ route('bpjs-logs.index') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md transition duration-200">
                    <i class="fas fa-list mr-2"></i>All BPJS Logs
                </a>
                <a href="{{ route('referensi.pendafataran') }}" class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-md transition duration-200">
                    <i class="fas fa-file-alt mr-2"></i>Referensi MJKN
                </a>
            </div>
        </div>

            <!-- Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Task ID Updates</h3>
                    <div class="grid grid-cols-3 gap-2">
                        <div class="bg-green-50 p-3 rounded-lg text-center">
                            <p class="text-sm font-medium text-gray-600">Success</p>
                            <p class="text-xl font-bold text-gray-800">{{ $successCount }}</p>
                        </div>
                        <div class="bg-red-50 p-3 rounded-lg text-center">
                            <p class="text-sm font-medium text-gray-600">Failed</p>
                            <p class="text-xl font-bold text-gray-800">{{ $errorCount }}</p>
                        </div>
                        <div class="bg-blue-50 p-3 rounded-lg text-center">
                            <p class="text-sm font-medium text-gray-600">Total</p>
                            <p class="text-xl font-bold text-gray-800">{{ $totalCount }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Antrean Additions</h3>
                    <div class="grid grid-cols-3 gap-2">
                        <div class="bg-green-50 p-3 rounded-lg text-center">
                            <p class="text-sm font-medium text-gray-600">Success</p>
                            <p class="text-xl font-bold text-gray-800">{{ $antreanSuccessCount }}</p>
                        </div>
                        <div class="bg-red-50 p-3 rounded-lg text-center">
                            <p class="text-sm font-medium text-gray-600">Failed</p>
                            <p class="text-xl font-bold text-gray-800">{{ $antreanErrorCount }}</p>
                        </div>
                        <div class="bg-blue-50 p-3 rounded-lg text-center">
                            <p class="text-sm font-medium text-gray-600">Total</p>
                            <p class="text-xl font-bold text-gray-800">{{ $antreanTotalCount }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-yellow-50 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Date Filter</h3>
                    <div class="flex space-x-2">
                        <div class="w-1/2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                            <input type="date" id="startDate" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        </div>
                        <div class="w-1/2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                            <input type="date" id="endDate" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        </div>
                    </div>
                    <div class="mt-2 text-center">
                        <button id="filterBtn" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-md transition duration-200">
                            <i class="fas fa-filter mr-2"></i>Apply Filter
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="bg-white rounded-lg shadow-md p-4 mb-6">
            <div class="flex border-b border-gray-200">
                <button class="tab-btn active py-2 px-4 text-blue-600 border-b-2 border-blue-600 font-medium" data-tab="taskid">
                    Task ID Updates
                </button>
                <button class="tab-btn py-2 px-4 text-gray-600 hover:text-gray-800 font-medium" data-tab="antrean">
                    Antrean Additions
                </button>
            </div>
        </div>

        <!-- Task ID Logs Table -->
        <div id="taskid-tab" class="tab-content bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800">Task ID Update Logs</h2>
                <div class="flex items-center space-x-2">
                    <label for="taskid-per-page" class="text-sm text-gray-600">Rows:</label>
                    <select id="taskid-per-page" class="text-sm border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Booking Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Task ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Response</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="taskid-logs-body" class="bg-white divide-y divide-gray-200">
                        <!-- Task ID logs will be loaded here via JavaScript -->
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">Loading logs...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div id="taskid-pagination" class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                <!-- Pagination will be added here via JavaScript -->
                <div class="text-sm text-gray-700">
                    Showing <span id="taskid-showing-start">0</span> to <span id="taskid-showing-end">0</span> of <span id="taskid-total-items">0</span> results
                </div>
                <div class="flex space-x-2">
                    <button id="taskid-prev-page" class="px-3 py-1 border border-gray-300 rounded-md text-sm disabled:opacity-50">
                        Previous
                    </button>
                    <button id="taskid-next-page" class="px-3 py-1 border border-gray-300 rounded-md text-sm disabled:opacity-50">
                        Next
                    </button>
                </div>
            </div>
        </div>

        <!-- Antrean Logs Table -->
        <div id="antrean-tab" class="tab-content bg-white rounded-lg shadow-md hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800">Antrean Addition Logs</h2>
                <div class="flex items-center space-x-2">
                    <label for="antrean-per-page" class="text-sm text-gray-600">Rows:</label>
                    <select id="antrean-per-page" class="text-sm border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Booking Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Response</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="antrean-logs-body" class="bg-white divide-y divide-gray-200">
                        <!-- Antrean logs will be loaded here via JavaScript -->
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">Loading logs...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div id="antrean-pagination" class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                <!-- Pagination will be added here via JavaScript -->
                <div class="text-sm text-gray-700">
                    Showing <span id="antrean-showing-start">0</span> to <span id="antrean-showing-end">0</span> of <span id="antrean-total-items">0</span> results
                </div>
                <div class="flex space-x-2">
                    <button id="antrean-prev-page" class="px-3 py-1 border border-gray-300 rounded-md text-sm disabled:opacity-50">
                        Previous
                    </button>
                    <button id="antrean-next-page" class="px-3 py-1 border border-gray-300 rounded-md text-sm disabled:opacity-50">
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Log Details Modal -->
    <div id="logModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg max-w-5xl w-full mx-4">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-800">Log Details</h3>
                <button id="closeModal" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-2">Request</h4>
                        <pre id="modalRequest" class="bg-gray-100 p-4 rounded-md overflow-x-auto text-sm"></pre>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-2">Response</h4>
                        <pre id="modalResponse" class="bg-gray-100 p-4 rounded-md overflow-x-auto text-sm"></pre>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p><span class="font-semibold">Time:</span> <span id="modalTime"></span></p>
                        <p><span class="font-semibold">URL:</span> <span id="modalUrl" class="break-all"></span></p>
                        <p><span class="font-semibold">Method:</span> <span id="modalMethod"></span></p>
                    </div>
                    <div>
                        <p><span class="font-semibold">Status Code:</span> <span id="modalCode"></span></p>
                        <p><span class="font-semibold">Booking Code:</span> <span id="modalBookingCode"></span></p>
                        <p id="modalTaskIdContainer"><span class="font-semibold">Task ID:</span> <span id="modalTaskId"></span></p>
                    </div>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 flex justify-end">
                <button id="closeModalBtn" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md transition duration-200">Close</button>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        const state = {
            taskId: {
                page: 1,
                perPage: 25,
                startDate: null,
                endDate: null
            },
            antrean: {
                page: 1,
                perPage: 25,
                startDate: null,
                endDate: null
            }
        };

        // Tab navigation
        document.addEventListener('DOMContentLoaded', function() {
            const tabBtns = document.querySelectorAll('.tab-btn');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const tabId = this.dataset.tab;
                    
                    // Toggle active class for tabs
                    tabBtns.forEach(btn => btn.classList.remove('active', 'text-blue-600', 'border-b-2', 'border-blue-600'));
                    tabBtns.forEach(btn => btn.classList.add('text-gray-600'));
                    this.classList.add('active', 'text-blue-600', 'border-b-2', 'border-blue-600');
                    
                    // Show/hide tab content
                    tabContents.forEach(content => {
                        content.classList.add('hidden');
                        if (content.id === tabId + '-tab') {
                            content.classList.remove('hidden');
                        }
                    });
                });
            });

            // Initialize with task ID logs
            loadTaskIdLogs();
            setupEventListeners();
        });

        function setupEventListeners() {
            // Per page change events
            document.getElementById('taskid-per-page').addEventListener('change', function() {
                state.taskId.perPage = this.value;
                state.taskId.page = 1;
                loadTaskIdLogs();
            });

            document.getElementById('antrean-per-page').addEventListener('change', function() {
                state.antrean.perPage = this.value;
                state.antrean.page = 1;
                loadAntreanLogs();
            });

            // Pagination events
            document.getElementById('taskid-prev-page').addEventListener('click', function() {
                if (state.taskId.page > 1) {
                    state.taskId.page--;
                    loadTaskIdLogs();
                }
            });

            document.getElementById('taskid-next-page').addEventListener('click', function() {
                state.taskId.page++;
                loadTaskIdLogs();
            });

            document.getElementById('antrean-prev-page').addEventListener('click', function() {
                if (state.antrean.page > 1) {
                    state.antrean.page--;
                    loadAntreanLogs();
                }
            });

            document.getElementById('antrean-next-page').addEventListener('click', function() {
                state.antrean.page++;
                loadAntreanLogs();
            });

            // Date filter events
            document.getElementById('filterBtn').addEventListener('click', function() {
                state.taskId.startDate = document.getElementById('startDate').value;
                state.taskId.endDate = document.getElementById('endDate').value;
                state.taskId.page = 1;
                
                state.antrean.startDate = document.getElementById('startDate').value;
                state.antrean.endDate = document.getElementById('endDate').value;
                state.antrean.page = 1;
                
                // Reload current tab
                if (!document.getElementById('antrean-tab').classList.contains('hidden')) {
                    loadAntreanLogs();
                } else {
                    loadTaskIdLogs();
                }
            });

            // Modal close events
            document.getElementById('closeModal').addEventListener('click', closeModal);
            document.getElementById('closeModalBtn').addEventListener('click', closeModal);
            
            // Tab click events
            document.querySelector('[data-tab="antrean"]').addEventListener('click', function() {
                if (document.getElementById('antrean-logs-body').innerHTML.includes('Loading logs...')) {
                    loadAntreanLogs();
                }
            });
        }

        function loadTaskIdLogs() {
            const tbody = document.getElementById('taskid-logs-body');
            tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500"><i class="fas fa-spinner fa-spin mr-2"></i>Loading logs...</td></tr>';
            
            let url = '/api/mobilejkn/task-id-logs?page=' + state.taskId.page + '&perPage=' + state.taskId.perPage;
            
            if (state.taskId.startDate && state.taskId.endDate) {
                url = '/api/mobilejkn/filtered-task-id-logs?startDate=' + state.taskId.startDate + 
                      '&endDate=' + state.taskId.endDate + 
                      '&page=' + state.taskId.page + 
                      '&perPage=' + state.taskId.perPage;
            }
            
            axios.get(url)
                .then(response => {
                    const data = response.data;
                    renderTaskIdLogs(data);
                    updateTaskIdPagination(data);
                })
                .catch(error => {
                    tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-red-500"><i class="fas fa-exclamation-triangle mr-2"></i>Error loading logs</td></tr>';
                    console.error('Error loading task ID logs:', error);
                });
        }

        function loadAntreanLogs() {
            const tbody = document.getElementById('antrean-logs-body');
            tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500"><i class="fas fa-spinner fa-spin mr-2"></i>Loading logs...</td></tr>';
            
            let url = '/api/mobilejkn/antrean-logs?page=' + state.antrean.page + '&perPage=' + state.antrean.perPage;
            
            // TODO: Add filtered antrean logs API endpoint
            
            axios.get(url)
                .then(response => {
                    const data = response.data;
                    renderAntreanLogs(data);
                    updateAntreanPagination(data);
                })
                .catch(error => {
                    tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-red-500"><i class="fas fa-exclamation-triangle mr-2"></i>Error loading logs</td></tr>';
                    console.error('Error loading antrean logs:', error);
                });
        }

        function renderTaskIdLogs(data) {
            const tbody = document.getElementById('taskid-logs-body');
            tbody.innerHTML = '';
            
            if (data.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">No logs found</td></tr>';
                return;
            }
            
            data.data.forEach(log => {
                const requestObj = tryParseJson(log.request);
                let kodebooking = '';
                let taskid = '';
                
                if (requestObj) {
                    kodebooking = requestObj.kodebooking || '';
                    taskid = requestObj.taskid || '';
                }
                
                const responseObj = tryParseJson(log.message);
                let responseMessage = '';
                
                if (responseObj && responseObj.metadata) {
                    responseMessage = responseObj.metadata.message || '';
                }
                
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';
                
                // Time column
                const timeCell = document.createElement('td');
                timeCell.className = 'px-6 py-4 whitespace-nowrap text-sm text-gray-500';
                const date = new Date(log.created_at);
                timeCell.textContent = date.toLocaleString();
                row.appendChild(timeCell);
                
                // Status column
                const statusCell = document.createElement('td');
                statusCell.className = 'px-6 py-4 whitespace-nowrap';
                let statusClass = '';
                if (log.code >= 200 && log.code < 300) {
                    statusClass = 'bg-green-100 text-green-800';
                } else if (log.code >= 400 && log.code < 500) {
                    statusClass = 'bg-yellow-100 text-yellow-800';
                } else if (log.code >= 500) {
                    statusClass = 'bg-red-100 text-red-800';
                }
                statusCell.innerHTML = `<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">${log.code}</span>`;
                row.appendChild(statusCell);
                
                // Booking Code column
                const bookingCodeCell = document.createElement('td');
                bookingCodeCell.className = 'px-6 py-4 whitespace-nowrap text-sm text-gray-900';
                bookingCodeCell.textContent = kodebooking;
                row.appendChild(bookingCodeCell);
                
                // Task ID column
                const taskIdCell = document.createElement('td');
                taskIdCell.className = 'px-6 py-4 whitespace-nowrap text-sm text-gray-900';
                taskIdCell.textContent = taskid;
                row.appendChild(taskIdCell);
                
                // Response column
                const responseCell = document.createElement('td');
                responseCell.className = 'px-6 py-4 whitespace-nowrap text-sm text-gray-500';
                responseCell.textContent = responseMessage;
                row.appendChild(responseCell);
                
                // Actions column
                const actionsCell = document.createElement('td');
                actionsCell.className = 'px-6 py-4 whitespace-nowrap text-right text-sm font-medium';
                const viewBtn = document.createElement('button');
                viewBtn.className = 'text-blue-600 hover:text-blue-900 mr-3';
                viewBtn.innerHTML = '<i class="fas fa-eye mr-1"></i>View';
                viewBtn.addEventListener('click', () => showLogModal(log, 'task'));
                actionsCell.appendChild(viewBtn);
                row.appendChild(actionsCell);
                
                tbody.appendChild(row);
            });
        }

        function renderAntreanLogs(data) {
            const tbody = document.getElementById('antrean-logs-body');
            tbody.innerHTML = '';
            
            if (data.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">No logs found</td></tr>';
                return;
            }
            
            data.data.forEach(log => {
                const requestObj = tryParseJson(log.request);
                let kodebooking = '';
                let patientName = '';
                
                if (requestObj) {
                    kodebooking = requestObj.kodebooking || '';
                    patientName = requestObj.namapoli ? requestObj.namapoli + ' - ' + (requestObj.namadokter || '') : '';
                }
                
                const responseObj = tryParseJson(log.message);
                let responseMessage = '';
                
                if (responseObj && responseObj.metadata) {
                    responseMessage = responseObj.metadata.message || '';
                }
                
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';
                
                // Time column
                const timeCell = document.createElement('td');
                timeCell.className = 'px-6 py-4 whitespace-nowrap text-sm text-gray-500';
                const date = new Date(log.created_at);
                timeCell.textContent = date.toLocaleString();
                row.appendChild(timeCell);
                
                // Status column
                const statusCell = document.createElement('td');
                statusCell.className = 'px-6 py-4 whitespace-nowrap';
                let statusClass = '';
                if (log.code >= 200 && log.code < 300) {
                    statusClass = 'bg-green-100 text-green-800';
                } else if (log.code >= 400 && log.code < 500) {
                    statusClass = 'bg-yellow-100 text-yellow-800';
                } else if (log.code >= 500) {
                    statusClass = 'bg-red-100 text-red-800';
                }
                statusCell.innerHTML = `<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">${log.code}</span>`;
                row.appendChild(statusCell);
                
                // Booking Code column
                const bookingCodeCell = document.createElement('td');
                bookingCodeCell.className = 'px-6 py-4 whitespace-nowrap text-sm text-gray-900';
                bookingCodeCell.textContent = kodebooking;
                row.appendChild(bookingCodeCell);
                
                // Patient Name column
                const patientNameCell = document.createElement('td');
                patientNameCell.className = 'px-6 py-4 whitespace-nowrap text-sm text-gray-900';
                patientNameCell.textContent = patientName;
                row.appendChild(patientNameCell);
                
                // Response column
                const responseCell = document.createElement('td');
                responseCell.className = 'px-6 py-4 whitespace-nowrap text-sm text-gray-500';
                responseCell.textContent = responseMessage;
                row.appendChild(responseCell);
                
                // Actions column
                const actionsCell = document.createElement('td');
                actionsCell.className = 'px-6 py-4 whitespace-nowrap text-right text-sm font-medium';
                const viewBtn = document.createElement('button');
                viewBtn.className = 'text-blue-600 hover:text-blue-900 mr-3';
                viewBtn.innerHTML = '<i class="fas fa-eye mr-1"></i>View';
                viewBtn.addEventListener('click', () => showLogModal(log, 'antrean'));
                actionsCell.appendChild(viewBtn);
                row.appendChild(actionsCell);
                
                tbody.appendChild(row);
            });
        }

        function updateTaskIdPagination(data) {
            const prevBtn = document.getElementById('taskid-prev-page');
            const nextBtn = document.getElementById('taskid-next-page');
            const showingStart = document.getElementById('taskid-showing-start');
            const showingEnd = document.getElementById('taskid-showing-end');
            const totalItems = document.getElementById('taskid-total-items');
            
            prevBtn.disabled = data.current_page <= 1;
            nextBtn.disabled = data.current_page >= data.last_page;
            
            showingStart.textContent = data.from || 0;
            showingEnd.textContent = data.to || 0;
            totalItems.textContent = data.total || 0;
        }

        function updateAntreanPagination(data) {
            const prevBtn = document.getElementById('antrean-prev-page');
            const nextBtn = document.getElementById('antrean-next-page');
            const showingStart = document.getElementById('antrean-showing-start');
            const showingEnd = document.getElementById('antrean-showing-end');
            const totalItems = document.getElementById('antrean-total-items');
            
            prevBtn.disabled = data.current_page <= 1;
            nextBtn.disabled = data.current_page >= data.last_page;
            
            showingStart.textContent = data.from || 0;
            showingEnd.textContent = data.to || 0;
            totalItems.textContent = data.total || 0;
        }

        function showLogModal(log, type) {
            const requestObj = tryParseJson(log.request);
            const responseObj = tryParseJson(log.message);
            
            document.getElementById('modalRequest').textContent = formatJson(log.request);
            document.getElementById('modalResponse').textContent = formatJson(log.message);
            document.getElementById('modalTime').textContent = new Date(log.created_at).toLocaleString();
            document.getElementById('modalUrl').textContent = log.url;
            document.getElementById('modalMethod').textContent = log.method;
            document.getElementById('modalCode').textContent = log.code;
            
            let kodebooking = '';
            if (requestObj) {
                kodebooking = requestObj.kodebooking || '';
            }
            document.getElementById('modalBookingCode').textContent = kodebooking;
            
            if (type === 'task' && requestObj && requestObj.taskid) {
                document.getElementById('modalTaskIdContainer').style.display = 'block';
                document.getElementById('modalTaskId').textContent = requestObj.taskid;
            } else {
                document.getElementById('modalTaskIdContainer').style.display = 'none';
            }
            
            document.getElementById('logModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('logModal').classList.add('hidden');
        }

        function tryParseJson(str) {
            try {
                return JSON.parse(str);
            } catch (e) {
                return null;
            }
        }

        function formatJson(str) {
            try {
                return JSON.stringify(JSON.parse(str), null, 2);
            } catch (e) {
                return str;
            }
        }
    </script>
@endsection
