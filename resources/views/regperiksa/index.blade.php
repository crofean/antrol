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
                                            </div>
                                        @elseif($patient->referensiMobilejknBpjs)
                                            <div class="flex items-center space-x-2">
                                                <span class="text-gray-500">No tasks</span>
                                                <button onclick="showLogModal('{{ $patient->no_rawat }}', null)"
                                                        class="text-xs bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded transition duration-200"
                                                        title="View BPJS Logs">
                                                    <i class="fas fa-search"></i>
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

        function formatJson(jsonString) {
            try {
                const parsed = JSON.parse(jsonString);
                return JSON.stringify(parsed, null, 2);
            } catch (e) {
                return jsonString;
            }
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
</body>
</html>
