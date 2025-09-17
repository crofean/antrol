<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pasien BPJS - {{ \Carbon\Carbon::parse($filters['date'])->format('d M Y') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Pasien BPJS</h1>
                    <p class="text-gray-600 mt-1">{{ \Carbon\Carbon::parse($filters['date'])->format('l, d F Y') }}</p>
                </div>
                <div class="flex space-x-4">
                    <a href="{{ route('regperiksa.index', array_merge($filters, ['date' => \Carbon\Carbon::parse($filters['date'])->subDay()->format('Y-m-d')])) }}"
                       class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-chevron-left mr-2"></i>Previous Day
                    </a>
                    <a href="{{ route('regperiksa.index', array_merge($filters, ['date' => \Carbon\Carbon::parse($filters['date'])->addDay()->format('Y-m-d')])) }}"
                       class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition duration-200">
                        Next Day<i class="fas fa-chevron-right ml-2"></i>
                    </a>
                    <a href="{{ route('regperiksa.index') }}"
                       class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-calendar-day mr-2"></i>Today
                    </a>
                    <a href="{{ route('bpjs-logs.index') }}" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-history mr-2"></i>BPJS Logs
                    </a>
                    <a href="{{ route('taskid.logs') }}" class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-tasks mr-2"></i>Task ID Logs
                    </a>
                </div>
            </div>

            <!-- Filters -->
            <form method="GET" action="{{ route('regperiksa.index') }}" class="bg-gray-50 p-4 rounded-lg">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                        <input type="date" name="date" id="date" value="{{ $filters['date'] }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="no_rkm_medis" class="block text-sm font-medium text-gray-700 mb-1">Rekam Medis</label>
                        <input type="text" name="no_rkm_medis" id="no_rkm_medis" value="{{ $filters['no_rkm_medis'] ?? '' }}"
                               placeholder="Search by rekam medis..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="no_rawat" class="block text-sm font-medium text-gray-700 mb-1">No Rawat</label>
                        <input type="text" name="no_rawat" id="no_rawat" value="{{ $filters['no_rawat'] ?? '' }}"
                               placeholder="Search by no rawat..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="no_kartu" class="block text-sm font-medium text-gray-700 mb-1">No Kartu</label>
                        <input type="text" name="no_kartu" id="no_kartu" value="{{ $filters['no_kartu'] ?? '' }}"
                               placeholder="Search by no kartu..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="no_sep" class="block text-sm font-medium text-gray-700 mb-1">SEP</label>
                        <input type="text" name="no_sep" id="no_sep" value="{{ $filters['no_sep'] ?? '' }}"
                               placeholder="Search by SEP..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="kd_poli" class="block text-sm font-medium text-gray-700 mb-1">Poli</label>
                        <input type="text" name="kd_poli" id="kd_poli" value="{{ $filters['kd_poli'] ?? '' }}"
                               placeholder="Search by poli..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" id="status"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Status</option>
                            <option value="Belum" {{ ($filters['status'] ?? '') == 'Belum' ? 'selected' : '' }}>Belum</option>
                            <option value="Sudah" {{ ($filters['status'] ?? '') == 'Sudah' ? 'selected' : '' }}>Sudah</option>
                            <option value="Batal" {{ ($filters['status'] ?? '') == 'Batal' ? 'selected' : '' }}>Batal</option>
                        </select>
                    </div>
                    <div>
                        <label for="per_page" class="block text-sm font-medium text-gray-700 mb-1">Per Page</label>
                        <select name="per_page" id="per_page"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                            <option value="15" {{ $perPage == 15 ? 'selected' : '' }}>15</option>
                            <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-between items-center mt-4">
                    <div class="flex space-x-2">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition duration-200">
                            <i class="fas fa-search mr-2"></i>Search
                        </button>
                        <a href="{{ route('regperiksa.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md transition duration-200">
                            <i class="fas fa-times mr-2"></i>Clear
                        </a>
                    </div>
                    <div class="text-sm text-gray-600">
                        Showing {{ $patients->firstItem() ?? 0 }} to {{ $patients->lastItem() ?? 0 }} of {{ $patients->total() }} results
                    </div>
                </div>
            </form>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-users text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total BPJS Patients</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $statistics['bpjs_patients'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-clock text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Belum</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $statistics['status_breakdown']['Belum'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                        <i class="fas fa-check-circle text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Sudah</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $statistics['status_breakdown']['Sudah'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-red-100 text-red-600">
                        <i class="fas fa-times-circle text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Batal</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $statistics['status_breakdown']['Batal'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Patients Table -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">Patient List</h2>
            </div>

            <div class="overflow-x-auto">
                @if($patients->count() > 0)
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registration</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rekam Medis</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No Kartu</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Task IDs & Times</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Doctor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Polyclinic</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SEP</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($patients as $index => $patient)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $patients->firstItem() + $index }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $patient->no_rawat }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $patient->jam_reg ? $patient->jam_reg->format('H:i') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $patient->pasien->nm_pasien ?? 'N/A' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $patient->no_rkm_medis }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $patient->referensiMobilejknBpjs->nomorkartu ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if($patient->referensiMobilejknBpjsTaskid->count() > 0)
                                            <div class="flex flex-col space-y-1">
                                                @foreach($patient->referensiMobilejknBpjsTaskid->sortBy('taskid') as $task)
                                                    <div class="flex items-center space-x-2">
                                                        <span class="font-medium">{{ $task->taskid }}</span>
                                                        <span class="text-xs text-gray-500">{{ $task->waktu ? $task->waktu->format('d/m H:i') : '-' }}</span>
                                                        <button onclick="showLogModal('{{ $patient->no_rawat }}', '{{ $task->taskid }}')"
                                                                class="text-xs bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded transition duration-200"
                                                                title="View BPJS Log">
                                                            <i class="fas fa-search"></i>
                                                        </button>
                                                    </div>
                                                @endforeach
                                                <button onclick="showTaskIdModal('{{ $patient->no_rawat }}', '{{ $patient->referensiMobilejknBpjs->kodebooking ?? '' }}', {{ $patient->referensiMobilejknBpjsTaskid->max('taskid') }})"
                                                        class="mt-2 text-xs bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded transition duration-200 flex items-center justify-center"
                                                        title="Send Next Task ID">
                                                    <i class="fas fa-paper-plane mr-1"></i> Send Next Task ID
                                                </button>
                                            </div>
                                        @elseif($patient->referensiMobilejknBpjs)
                                            <div class="flex flex-col space-y-2">
                                                <div class="flex items-center space-x-2">
                                                    <span class="text-gray-500">No tasks</span>
                                                    <button onclick="showLogModal('{{ $patient->no_rawat }}', null)"
                                                            class="text-xs bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded transition duration-200"
                                                            title="View BPJS Logs">
                                                        <i class="fas fa-search"></i>
                                                    </button>
                                                </div>
                                                <button onclick="showTaskIdModal('{{ $patient->no_rawat }}', '{{ $patient->referensiMobilejknBpjs->kodebooking ?? '' }}', 0)"
                                                        class="text-xs bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded transition duration-200 flex items-center justify-center"
                                                        title="Start Task ID Flow">
                                                    <i class="fas fa-play mr-1"></i> Start Task ID Flow
                                                </button>
                                            </div>
                                        @else
                                            <span>-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $patient->kd_dokter }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $patient->kd_poli }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($patient->stts == 'Belum')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-clock mr-1"></i>{{ $patient->stts }}
                                            </span>
                                        @elseif($patient->stts == 'Sudah')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                <i class="fas fa-check mr-1"></i>{{ $patient->stts }}
                                            </span>
                                        @elseif($patient->stts == 'Batal')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                <i class="fas fa-times mr-1"></i>{{ $patient->stts }}
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                {{ $patient->stts }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if($patient->bridgingSep)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                                <i class="fas fa-file-medical mr-1"></i>{{ $patient->bridgingSep->no_sep }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Showing <span class="font-medium">{{ $patients->firstItem() }}</span> to <span class="font-medium">{{ $patients->lastItem() }}</span> of <span class="font-medium">{{ $patients->total() }}</span> results
                            </div>
                            <div class="flex space-x-1">
                                @if ($patients->hasPages())
                                    {{-- Previous Page Link --}}
                                    @if ($patients->onFirstPage())
                                        <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-not-allowed rounded-l-md">
                                            <i class="fas fa-chevron-left mr-1"></i>Previous
                                        </span>
                                    @else
                                        <a href="{{ $patients->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50">
                                            <i class="fas fa-chevron-left mr-1"></i>Previous
                                        </a>
                                    @endif

                                    {{-- Pagination Elements --}}
                                    @foreach ($patients->getUrlRange(1, $patients->lastPage()) as $page => $url)
                                        @if ($page == $patients->currentPage())
                                            <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-blue-600">{{ $page }}</span>
                                        @else
                                            <a href="{{ $url }}" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50">{{ $page }}</a>
                                        @endif
                                    @endforeach

                                    {{-- Next Page Link --}}
                                    @if ($patients->hasMorePages())
                                        <a href="{{ $patients->nextPageUrl() }}" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-r-md hover:bg-gray-50">
                                            Next<i class="fas fa-chevron-right ml-1"></i>
                                        </a>
                                    @else
                                        <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-not-allowed rounded-r-md">
                                            Next<i class="fas fa-chevron-right ml-1"></i>
                                        </span>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    <div class="px-6 py-12 text-center">
                        <div class="text-gray-400 mb-4">
                            <i class="fas fa-users text-6xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No Patients Found</h3>
                        <p class="text-gray-500 mb-4">
                            @if(request()->hasAny(['no_rkm_medis', 'no_rawat', 'no_kartu', 'no_sep', 'kd_poli', 'status', 'kd_dokter']))
                                No patients match your search criteria for {{ \Carbon\Carbon::parse($filters['date'])->format('d M Y') }}
                            @else
                                There are no BPJS patients registered for {{ \Carbon\Carbon::parse($filters['date'])->format('d M Y') }}
                            @endif
                        </p>
                        @if(request()->hasAny(['no_rkm_medis', 'no_rawat', 'no_kartu', 'no_sep', 'kd_poli', 'status', 'kd_dokter']))
                            <a href="{{ route('regperiksa.index', ['date' => $filters['date']]) }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition duration-200">
                                <i class="fas fa-times mr-2"></i>Clear Filters
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-gray-500 text-sm">
            <p>&copy; 2025 RSAM Antrol System. All rights reserved.</p>
        </div>
    </div>

    <script>
        // Auto refresh every 5 minutes
        setTimeout(function() {
            window.location.reload();
        }, 300000);
        
        // Global variables for Task ID handling
        let currentNoRawat = null;
        let currentKodeBooking = null;
        let currentTaskId = null;
        let taskIdPayload = {};

        // Modal functionality
        function showLogModal(noRawat, taskId) {
            // Show loading state
            document.getElementById('logModalContent').innerHTML = `
                <div class="flex items-center justify-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    <span class="ml-2 text-gray-600">Loading log data...</span>
                </div>
            `;
            document.getElementById('logModal').classList.remove('hidden');

            // Build query parameters
            let queryParams = `no_rawat=${encodeURIComponent(noRawat)}`;
            if (taskId && taskId !== 'null' && taskId !== 'undefined') {
                queryParams += `&task_id=${encodeURIComponent(taskId)}`;
            }

            // Fetch log data
            fetch(`/api/bpjs-logs/by-task?${queryParams}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        displayLogData(data.data, noRawat, taskId);
                    } else {
                        document.getElementById('logModalContent').innerHTML = `
                            <div class="text-center py-8">
                                <i class="fas fa-info-circle text-4xl text-gray-300 mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No Log Found</h3>
                                <p class="text-gray-500">No BPJS log found for ${taskId ? 'Task ID ' + taskId + ' and ' : ''}No Rawat ${noRawat}</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error fetching log:', error);
                    document.getElementById('logModalContent').innerHTML = `
                        <div class="text-center py-8">
                            <i class="fas fa-exclamation-triangle text-4xl text-red-300 mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Error Loading Log</h3>
                            <p class="text-gray-500">Failed to load BPJS log data. Please try again.</p>
                        </div>
                    `;
                });
        }

        function displayLogData(log, noRawat, taskId) {
            const statusColor = log.code >= 200 && log.code < 300 ? 'text-green-600' :
                               log.code >= 400 ? 'text-red-600' : 'text-yellow-600';

            // Try to extract relevant info from request
            let requestData = {};
            try {
                requestData = JSON.parse(log.request);
            } catch (e) {
                requestData = { raw: log.request };
            }

            // Determine operation type
            const operationType = log.url.includes('updatewaktu') ? 'Update Task' :
                                 log.url.includes('add') ? 'Add Appointment' :
                                 log.url.includes('batal') ? 'Cancel Appointment' :
                                 'Other Operation';

            // Update modal title
            const modalTitle = taskId ? `BPJS Log Details - Task ${taskId}` : 'BPJS Log Details';
            document.querySelector('#logModal h3').textContent = modalTitle;

            document.getElementById('logModalContent').innerHTML = `
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">No Rawat</label>
                            <p class="mt-1 text-sm text-gray-900">${noRawat}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Task ID</label>
                            <p class="mt-1 text-sm text-gray-900">${taskId || 'N/A'}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">HTTP Status</label>
                            <p class="mt-1 text-sm ${statusColor} font-medium">${log.code}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Operation</label>
                            <p class="mt-1 text-sm text-gray-900">${operationType}</p>
                        </div>
                    </div>

                    ${requestData.kodebooking ? `
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Booking Code</label>
                            <p class="mt-1 text-sm text-gray-900">${requestData.kodebooking}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Queue Number</label>
                            <p class="mt-1 text-sm text-gray-900">${requestData.nomorantrean || 'N/A'}</p>
                        </div>
                    </div>
                    ` : ''}

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">URL</label>
                        <code class="block bg-gray-100 p-2 rounded text-xs break-all">${log.url}</code>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Request Data</label>
                        <pre class="bg-gray-100 p-3 rounded text-xs overflow-x-auto max-h-32">${formatJson(log.request)}</pre>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Response</label>
                        <div class="bg-gray-100 p-3 rounded">
                            <p class="text-sm ${log.code >= 200 && log.code < 300 ? 'text-green-700' : 'text-red-700'} font-medium">
                                ${log.message}
                            </p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Timestamp</label>
                        <p class="mt-1 text-sm text-gray-900">${new Date(log.created_at).toLocaleString()}</p>
                    </div>
                </div>
            `;
        }

        function closeLogModal() {
            document.getElementById('logModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('logModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeLogModal();
            }
        });

        // Task ID Modal functionality
        function showTaskIdModal(noRawat, kodeBooking, lastTaskId) {
            // Store current data
            currentNoRawat = noRawat;
            currentKodeBooking = kodeBooking;
            currentTaskId = lastTaskId;
            
            // Show loading state
            document.getElementById('taskIdModalContent').innerHTML = `
                <div class="flex items-center justify-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    <span class="ml-2 text-gray-600">Loading data...</span>
                </div>
            `;
            
            // Show the modal
            document.getElementById('taskIdModal').classList.remove('hidden');
            
            // Determine next task ID
            let nextTaskId = determineNextTaskId(lastTaskId);
            
            // Update modal title
            document.getElementById('taskIdModalTitle').textContent = `Send Task ID ${nextTaskId}`;
            
            // Fetch necessary data for the task ID
            fetchTaskIdData(noRawat, kodeBooking, nextTaskId);
        }
        
        function determineNextTaskId(lastTaskId) {
            // Logic to determine the next task ID based on the last one
            if (lastTaskId === 0 || !lastTaskId) {
                // No task IDs yet, start with add antrean or task ID 3
                return 3;
            } else if (lastTaskId < 7) {
                // Return the next task ID in sequence
                return lastTaskId + 1;
            } else {
                // Task ID 7 is the last one
                return 99; // Special case for task ID 99 (Batal)
            }
        }
        
        function fetchTaskIdData(noRawat, kodeBooking, taskId) {
            // Default request payload
            taskIdPayload = {
                kodebooking: kodeBooking,
                taskid: taskId,
                waktu: new Date().toISOString().slice(0, 19).replace('T', ' ')
            };
            
            // If kodeBooking is empty and taskId is 3, we need to add antrean first
            if ((!kodeBooking || kodeBooking === '') && taskId === 3) {
                fetchAddAntreanData(noRawat);
                return;
            }
            
            // For each task ID, get the appropriate data
            switch(taskId) {
                case 3: // Pasien datang
                    fetchPatientData(noRawat);
                    break;
                case 4: // Mulai layanan perawat/poli
                    fetchNursingData(noRawat);
                    break;
                case 5: // Mulai layanan dokter
                    fetchDoctorData(noRawat);
                    break;
                case 6: // Selesai layanan dokter
                    fetchDoctorEndData(noRawat);
                    break;
                case 7: // Selesai layanan obat
                    fetchMedicationData(noRawat);
                    break;
                case 99: // Batal
                    displayCancelTaskData();
                    break;
                default:
                    displayGenericTaskData(taskId);
            }
        }
        
        function fetchAddAntreanData(noRawat) {
            fetch(`/api/regperiksa/patient?no_rawat=${noRawat}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        const patient = data.data;
                        
                        // Build the add antrean request payload
                        const addAntreanPayload = {
                            kodebooking: generateBookingCode(),
                            jenispasien: "JKN",
                            nomorkartu: patient.no_peserta || "",
                            nik: patient.pasien?.no_ktp || "",
                            nohp: patient.pasien?.no_tlp || "",
                            kodepoli: patient.kd_poli || "",
                            namapoli: patient.poliklinik?.nm_poli || "",
                            pasienbaru: 0,
                            norm: patient.no_rkm_medis || "",
                            tanggalperiksa: patient.tgl_registrasi || "",
                            kodedokter: patient.kd_dokter || "",
                            namadokter: patient.dokter?.nm_dokter || "",
                            jampraktek: "08:00-16:00",
                            jeniskunjungan: 1,
                            nomorreferensi: patient.no_rujukan || "",
                            nomorantrean: patient.no_reg || "",
                            angkaantrean: parseInt(patient.no_reg) || 1,
                            estimasidilayani: estimateServiceTime(patient.jam_reg),
                            sisakuotajkn: 5,
                            kuotajkn: 30,
                            sisakuotanonjkn: 5,
                            kuotanonjkn: 30,
                            keterangan: "Peserta harap 30 menit sebelum dilayani"
                        };
                        
                        displayAddAntreanForm(addAntreanPayload, noRawat);
                    } else {
                        displayErrorContent("Failed to load patient data");
                    }
                })
                .catch(error => {
                    console.error('Error fetching patient data:', error);
                    displayErrorContent("Error loading patient data");
                });
        }
        
        function fetchPatientData(noRawat) {
            fetch(`/api/regperiksa/patient/${noRawat}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        const patient = data.data;
                        
                        // Update the task ID payload with patient arrival time
                        taskIdPayload.waktu = patient.jam_reg || taskIdPayload.waktu;
                        
                        displayTaskIdForm(3, "Pasien check-in / kedatangan", taskIdPayload, patient);
                    } else {
                        displayErrorContent("Failed to load patient data");
                    }
                })
                .catch(error => {
                    console.error('Error fetching patient data:', error);
                    displayErrorContent("Error loading patient data");
                });
        }
        
        function fetchNursingData(noRawat) {
            fetch(`/api/regperiksa/patient/${noRawat}?include=pemeriksaan`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        const patient = data.data;
                        const pemeriksaan = patient.pemeriksaan;
                        
                        // Update the task ID payload with nurse start time
                        if (pemeriksaan && pemeriksaan.jam_rawat) {
                            taskIdPayload.waktu = pemeriksaan.jam_rawat;
                        }
                        
                        displayTaskIdForm(4, "Mulai layanan perawat/poli", taskIdPayload, patient);
                    } else {
                        displayErrorContent("Failed to load patient examination data");
                    }
                })
                .catch(error => {
                    console.error('Error fetching patient examination data:', error);
                    displayErrorContent("Error loading patient examination data");
                });
        }
        
        function fetchDoctorData(noRawat) {
            fetch(`/api/regperiksa/patient/${noRawat}?include=pemeriksaan`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        const patient = data.data;
                        const pemeriksaan = patient.pemeriksaan;
                        
                        // Update the task ID payload with doctor start time
                        if (pemeriksaan && pemeriksaan.jam_rawat) {
                            taskIdPayload.waktu = pemeriksaan.jam_rawat;
                        }
                        
                        displayTaskIdForm(5, "Mulai layanan dokter", taskIdPayload, patient);
                    } else {
                        displayErrorContent("Failed to load patient examination data");
                    }
                })
                .catch(error => {
                    console.error('Error fetching patient examination data:', error);
                    displayErrorContent("Error loading patient examination data");
                });
        }
        
        function fetchDoctorEndData(noRawat) {
            fetch(`/api/regperiksa/patient/${noRawat}?include=resep`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        const patient = data.data;
                        const resep = patient.resep;
                        
                        // Update the task ID payload with doctor end time
                        if (resep && resep.jam) {
                            taskIdPayload.waktu = resep.jam;
                        }
                        
                        displayTaskIdForm(6, "Selesai layanan dokter", taskIdPayload, patient);
                    } else {
                        displayErrorContent("Failed to load patient prescription data");
                    }
                })
                .catch(error => {
                    console.error('Error fetching patient prescription data:', error);
                    displayErrorContent("Error loading patient prescription data");
                });
        }
        
        function fetchMedicationData(noRawat) {
            fetch(`/api/regperiksa/patient/${noRawat}?include=resep`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        const patient = data.data;
                        const resep = patient.resep;
                        
                        // Update the task ID payload with medication end time
                        if (resep && resep.jam_penyerahan) {
                            taskIdPayload.waktu = resep.jam_penyerahan;
                        }
                        
                        displayTaskIdForm(7, "Selesai layanan obat", taskIdPayload, patient);
                    } else {
                        displayErrorContent("Failed to load patient medication data");
                    }
                })
                .catch(error => {
                    console.error('Error fetching patient medication data:', error);
                    displayErrorContent("Error loading patient medication data");
                });
        }
        
        function displayCancelTaskData() {
            displayTaskIdForm(99, "Batal", taskIdPayload, null);
        }
        
        function displayGenericTaskData(taskId) {
            displayTaskIdForm(taskId, `Task ID ${taskId}`, taskIdPayload, null);
        }
        
        function displayAddAntreanForm(payload, noRawat) {
            document.getElementById('taskIdModalTitle').textContent = "Add Antrean";
            
            document.getElementById('taskIdModalContent').innerHTML = `
                <div class="space-y-4">
                    <p class="text-sm text-gray-700">No registration data found in BPJS Mobile JKN. You need to add an appointment first before sending task IDs.</p>
                    
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4">
                        <div class="flex">
                            <div class="ml-3">
                                <p class="text-sm text-blue-700">
                                    <strong>Patient:</strong> ${payload.norm} - ${payload.nik}
                                </p>
                                <p class="text-sm text-blue-700">
                                    <strong>Poli:</strong> ${payload.kodepoli} - ${payload.namapoli}
                                </p>
                                <p class="text-sm text-blue-700">
                                    <strong>Doctor:</strong> ${payload.kodedokter} - ${payload.namadokter}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">API Request</label>
                        <pre class="bg-gray-100 p-3 rounded text-xs overflow-x-auto max-h-64">${formatJson(JSON.stringify(payload))}</pre>
                    </div>
                </div>
            `;
            
            // Update send button text and action
            document.getElementById('taskIdSendButton').textContent = "Add Antrean";
            document.getElementById('taskIdSendButton').onclick = function() {
                sendAddAntrean(payload, noRawat);
            };
        }
        
        function displayTaskIdForm(taskId, taskName, payload, patientData) {
            let patientInfo = '';
            
            if (patientData) {
                patientInfo = `
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-4">
                        <div class="flex">
                            <div class="ml-3">
                                <p class="text-sm text-blue-700">
                                    <strong>Patient:</strong> ${patientData.no_rkm_medis} - ${patientData.pasien?.nm_pasien || 'N/A'}
                                </p>
                                <p class="text-sm text-blue-700">
                                    <strong>Registration:</strong> ${patientData.no_rawat}
                                </p>
                                ${patientData.kd_poli ? `<p class="text-sm text-blue-700">
                                    <strong>Poli:</strong> ${patientData.kd_poli} - ${patientData.poliklinik?.nm_poli || 'N/A'}
                                </p>` : ''}
                                ${patientData.kd_dokter ? `<p class="text-sm text-blue-700">
                                    <strong>Doctor:</strong> ${patientData.kd_dokter} - ${patientData.dokter?.nm_dokter || 'N/A'}
                                </p>` : ''}
                            </div>
                        </div>
                    </div>
                `;
            }
            
            document.getElementById('taskIdModalContent').innerHTML = `
                <div class="space-y-4">
                    ${patientInfo}
                    
                    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    You are about to send <strong>Task ID ${taskId}</strong> (${taskName}).
                                </p>
                                <p class="text-sm text-yellow-700">
                                    Please verify the data before proceeding.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="waktu" class="block text-sm font-medium text-gray-700 mb-1">Timestamp</label>
                            <input type="datetime-local" id="waktu" name="waktu" 
                                value="${formatDatetimeForInput(payload.waktu)}"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">API Request</label>
                        <pre class="bg-gray-100 p-3 rounded text-xs overflow-x-auto max-h-64">${formatJson(JSON.stringify(payload))}</pre>
                    </div>
                </div>
            `;
            
            // Update send button text and action
            document.getElementById('taskIdSendButton').textContent = "Send Task ID";
            document.getElementById('taskIdSendButton').onclick = function() {
                const waktuInput = document.getElementById('waktu').value;
                if (waktuInput) {
                    payload.waktu = formatInputForDatetime(waktuInput);
                }
                sendTaskIdRequest(payload);
            };
        }
        
        function displayErrorContent(errorMessage) {
            document.getElementById('taskIdModalContent').innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-exclamation-triangle text-4xl text-red-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Error</h3>
                    <p class="text-gray-500">${errorMessage}</p>
                </div>
            `;
        }
        
        function sendAddAntrean(payload, noRawat) {
            // Show loading
            document.getElementById('taskIdSendButton').disabled = true;
            document.getElementById('taskIdSendButton').innerHTML = `
                <div class="flex items-center">
                    <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                    Processing...
                </div>
            `;
            
            fetch('/api/mobilejkn/add-antrean', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    payload: payload,
                    no_rawat: noRawat
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Success - now we can send task ID 3
                    const taskIdPayload = {
                        kodebooking: payload.kodebooking,
                        taskid: 3,
                        waktu: payload.tanggalperiksa + ' ' + payload.estimasidilayani
                    };
                    
                    // Update modal content
                    document.getElementById('taskIdModalContent').innerHTML += `
                        <div class="mt-4 bg-green-50 border-l-4 border-green-500 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-green-700">
                                        <strong>Success!</strong> Appointment added successfully.
                                    </p>
                                    <p class="text-sm text-green-700">
                                        Now sending Task ID 3...
                                    </p>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    // Send Task ID 3
                    sendTaskIdRequest(taskIdPayload);
                } else {
                    // Error
                    document.getElementById('taskIdModalContent').innerHTML += `
                        <div class="mt-4 bg-red-50 border-l-4 border-red-500 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-red-700">
                                        <strong>Error!</strong> ${data.message || 'Failed to add appointment'}
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">API Response</label>
                            <pre class="bg-gray-100 p-3 rounded text-xs overflow-x-auto max-h-64">${formatJson(JSON.stringify(data.response || {}))}</pre>
                        </div>
                    `;
                    
                    // Re-enable button
                    document.getElementById('taskIdSendButton').disabled = false;
                    document.getElementById('taskIdSendButton').textContent = "Try Again";
                }
            })
            .catch(error => {
                console.error('Error sending add antrean request:', error);
                
                // Error
                document.getElementById('taskIdModalContent').innerHTML += `
                    <div class="mt-4 bg-red-50 border-l-4 border-red-500 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700">
                                    <strong>Error!</strong> ${error.message || 'Network error occurred'}
                                </p>
                            </div>
                        </div>
                    </div>
                `;
                
                // Re-enable button
                document.getElementById('taskIdSendButton').disabled = false;
                document.getElementById('taskIdSendButton').textContent = "Try Again";
            });
        }
        
        function sendTaskIdRequest(payload) {
            // Show loading
            document.getElementById('taskIdSendButton').disabled = true;
            document.getElementById('taskIdSendButton').innerHTML = `
                <div class="flex items-center">
                    <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                    Sending...
                </div>
            `;
            
            fetch('/api/mobilejkn/update-task-id', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Success
                    document.getElementById('taskIdModalContent').innerHTML += `
                        <div class="mt-4 bg-green-50 border-l-4 border-green-500 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-green-700">
                                        <strong>Success!</strong> Task ID ${payload.taskid} sent successfully.
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">API Response</label>
                            <pre class="bg-gray-100 p-3 rounded text-xs overflow-x-auto max-h-64">${formatJson(JSON.stringify(data))}</pre>
                        </div>
                    `;
                    
                    // Change button to "Close" and reload page on click
                    document.getElementById('taskIdSendButton').disabled = false;
                    document.getElementById('taskIdSendButton').textContent = "Close and Refresh";
                    document.getElementById('taskIdSendButton').onclick = function() {
                        window.location.reload();
                    };
                } else {
                    // Error
                    document.getElementById('taskIdModalContent').innerHTML += `
                        <div class="mt-4 bg-red-50 border-l-4 border-red-500 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-red-700">
                                        <strong>Error!</strong> ${data.message || 'Failed to send task ID'}
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">API Response</label>
                            <pre class="bg-gray-100 p-3 rounded text-xs overflow-x-auto max-h-64">${formatJson(JSON.stringify(data))}</pre>
                        </div>
                    `;
                    
                    // Re-enable button
                    document.getElementById('taskIdSendButton').disabled = false;
                    document.getElementById('taskIdSendButton').textContent = "Try Again";
                }
            })
            .catch(error => {
                console.error('Error sending task ID request:', error);
                
                // Error
                document.getElementById('taskIdModalContent').innerHTML += `
                    <div class="mt-4 bg-red-50 border-l-4 border-red-500 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700">
                                    <strong>Error!</strong> ${error.message || 'Network error occurred'}
                                </p>
                            </div>
                        </div>
                    </div>
                `;
                
                // Re-enable button
                document.getElementById('taskIdSendButton').disabled = false;
                document.getElementById('taskIdSendButton').textContent = "Try Again";
            });
        }
        
        function closeTaskIdModal() {
            document.getElementById('taskIdModal').classList.add('hidden');
        }
        
        // Helper functions
        function generateBookingCode() {
            const timestamp = new Date().getTime().toString();
            const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
            return 'BK' + timestamp.substring(timestamp.length - 6) + random;
        }
        
        function estimateServiceTime(registrationTime) {
            if (!registrationTime) {
                return new Date().toTimeString().substring(0, 5);
            }
            
            // Assume the registration time is in HH:mm format
            return registrationTime.substring(0, 5);
        }
        
        function formatJson(jsonString) {
            try {
                const parsed = JSON.parse(jsonString);
                return JSON.stringify(parsed, null, 2);
            } catch (e) {
                return jsonString;
            }
        }
        
        function formatDatetimeForInput(datetimeStr) {
            if (!datetimeStr) return '';
            
            try {
                // Check if the string is in MySQL datetime format (YYYY-MM-DD HH:mm:ss)
                if (datetimeStr.includes(' ')) {
                    const [date, time] = datetimeStr.split(' ');
                    return `${date}T${time.substring(0, 5)}`;
                }
                
                // If it's already in ISO format or similar
                const date = new Date(datetimeStr);
                return date.toISOString().slice(0, 16);
            } catch (e) {
                console.error('Error formatting datetime:', e);
                return '';
            }
        }
        
        function formatInputForDatetime(inputValue) {
            if (!inputValue) return '';
            
            try {
                // Convert from HTML datetime-local input format (YYYY-MM-DDTHH:mm)
                // to MySQL format (YYYY-MM-DD HH:mm:ss)
                return inputValue.replace('T', ' ') + ':00';
            } catch (e) {
                console.error('Error formatting input datetime:', e);
                return '';
            }
        }
    </script>

    <!-- BPJS Log Modal -->
    <div id="logModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">BPJS Log Details</h3>
                <button onclick="closeLogModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div id="logModalContent" class="max-h-96 overflow-y-auto">
                <!-- Content will be loaded here -->
            </div>

            <div class="flex justify-end mt-4">
                <button onclick="closeLogModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md transition duration-200">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Task ID Modal -->
    <div id="taskIdModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 id="taskIdModalTitle" class="text-lg font-medium text-gray-900">Send Task ID</h3>
                <button onclick="closeTaskIdModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div id="taskIdModalContent" class="max-h-96 overflow-y-auto">
                <!-- Content will be loaded here -->
                <div class="flex justify-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                </div>
            </div>

            <div class="flex justify-end mt-4 space-x-2">
                <button id="taskIdSendButton" onclick="sendTaskId()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md transition duration-200">
                    Send Task ID
                </button>
                <button onclick="closeTaskIdModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md transition duration-200">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</body>
</html>
