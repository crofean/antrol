@extends('layouts.main')

@section('title', 'Pasien BPJS')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-12">
    <!-- Header -->
    <div class="glass rounded-3xl p-8 mb-8 space-y-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div>
                <h1 class="text-4xl font-bold tracking-tight text-slate-900 dark:text-white">Pasien BPJS</h1>
                <p class="text-slate-500 dark:text-slate-400 mt-2 flex items-center">
                    <i class="fas fa-calendar-alt mr-2 text-blue-600"></i>
                    {{ \Carbon\Carbon::parse($filters['date'])->format('l, d F Y') }}
                </p>
            </div>
            
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('regperiksa.index', array_merge($filters, ['date' => \Carbon\Carbon::parse($filters['date'])->subDay()->format('Y-m-d')])) }}"
                   class="glass px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-slate-100 dark:hover:bg-slate-800 transition-all flex items-center">
                    <i class="fas fa-chevron-left mr-2"></i>Prev
                </a>
                <a href="{{ route('regperiksa.index', array_merge($filters, ['date' => \Carbon\Carbon::parse($filters['date'])->addDay()->format('Y-m-d')])) }}"
                   class="glass px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-slate-100 dark:hover:bg-slate-800 transition-all flex items-center">
                    Next<i class="fas fa-chevron-right ml-2"></i>
                </a>
                <a href="{{ route('regperiksa.index') }}"
                   class="bg-blue-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/20 flex items-center">
                    <i class="fas fa-calendar-day mr-2"></i>Today
                </a>
            </div>
        </div>

        <!-- Quick Access -->
        <div class="flex flex-wrap gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
            <a href="{{ route('bpjs-logs.index') }}" class="glass px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors flex items-center">
                <i class="fas fa-history mr-2"></i>BPJS Logs
            </a>
            <a href="{{ route('taskid.logs') }}" class="glass px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors flex items-center">
                <i class="fas fa-tasks mr-2"></i>Task ID Logs
            </a>
            <a href="{{ route('referensi.pendafataran', ['date_from' => $filters['date'], 'date_to' => $filters['date']]) }}" class="glass px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 hover:text-teal-600 dark:hover:text-teal-400 transition-colors flex items-center">
                <i class="fas fa-file-alt mr-2"></i>Referensi MJKN
            </a>
            <a href="{{ route('command.index') }}" class="glass px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 hover:text-amber-600 dark:hover:text-amber-400 transition-colors flex items-center">
                <i class="fas fa-terminal mr-2"></i>Run Command
            </a>
        </div>
    </div>

    <!-- Filters & Statistics Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 mb-8">
        <!-- Filters Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            <div class="glass rounded-3xl p-6 shadow-sm">
                <h3 class="text-lg font-bold mb-6 flex items-center">
                    <i class="fas fa-filter mr-2 text-blue-600"></i> Filters
                </h3>
                <form method="GET" action="{{ route('regperiksa.index') }}" class="space-y-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500 ml-1">Date</label>
                        <input type="date" name="date" value="{{ $filters['date'] }}" class="w-full bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 transition-all outline-none">
                    </div>
                    
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500 ml-1">Rekam Medis</label>
                        <input type="text" name="no_rkm_medis" value="{{ $filters['no_rkm_medis'] ?? '' }}" placeholder="Search RM..." class="w-full bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 transition-all outline-none">
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500 ml-1">Status</label>
                        <select name="status" class="w-full bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 transition-all outline-none">
                            <option value="">All Status</option>
                            <option value="Belum" {{ ($filters['status'] ?? '') == 'Belum' ? 'selected' : '' }}>Belum</option>
                            <option value="Sudah" {{ ($filters['status'] ?? '') == 'Sudah' ? 'selected' : '' }}>Sudah</option>
                            <option value="Batal" {{ ($filters['status'] ?? '') == 'Batal' ? 'selected' : '' }}>Batal</option>
                        </select>
                    </div>

                    <div class="pt-4 flex gap-2">
                        <button type="submit" class="flex-1 bg-slate-900 dark:bg-white dark:text-slate-900 text-white py-2.5 rounded-xl text-sm font-bold hover:opacity-90 transition-all">Search</button>
                        <a href="{{ route('regperiksa.index') }}" class="glass px-4 py-2.5 rounded-xl text-slate-500 hover:text-red-500 transition-colors"><i class="fas fa-undo"></i></a>
                    </div>
                </form>
            </div>

            <!-- Stats Mini -->
            <div class="glass rounded-3xl p-6 shadow-sm space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-slate-500">Total Patients</span>
                    <span class="text-lg font-bold">{{ $statistics['bpjs_patients'] }}</span>
                </div>
                <div class="h-1.5 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                    <div class="h-full bg-blue-600 rounded-full" style="width: 100%"></div>
                </div>
                <div class="grid grid-cols-3 gap-2 pt-2">
                    <div class="text-center">
                        <p class="text-[10px] font-bold uppercase tracking-tighter text-slate-400">Belum</p>
                        <p class="text-sm font-bold text-amber-500">{{ $statistics['status_breakdown']['Belum'] ?? 0 }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-[10px] font-bold uppercase tracking-tighter text-slate-400">Sudah</p>
                        <p class="text-sm font-bold text-emerald-500">{{ $statistics['status_breakdown']['Sudah'] ?? 0 }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-[10px] font-bold uppercase tracking-tighter text-slate-400">Batal</p>
                        <p class="text-sm font-bold text-rose-500">{{ $statistics['status_breakdown']['Batal'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="lg:col-span-3">
            <div class="glass rounded-3xl shadow-sm overflow-hidden">
                <div class="px-8 py-6 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
                    <h2 class="text-xl font-bold tracking-tight">Patient Registry</h2>
                    <div class="text-sm text-slate-500">
                        Showing {{ $patients->firstItem() ?? 0 }} - {{ $patients->lastItem() ?? 0 }} of {{ $patients->total() }}
                    </div>
                </div>

                <div class="overflow-x-auto">
                    @if($patients->count() > 0)
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/50 dark:bg-slate-800/30">
                                    <th class="px-8 py-4 text-[10px] font-bold uppercase tracking-widest text-slate-400">#</th>
                                    <th class="px-8 py-4 text-[10px] font-bold uppercase tracking-widest text-slate-400">Identity</th>
                                    <th class="px-8 py-4 text-[10px] font-bold uppercase tracking-widest text-slate-400">Time</th>
                                    <th class="px-8 py-4 text-[10px] font-bold uppercase tracking-widest text-slate-400">Task Flow</th>
                                    <th class="px-8 py-4 text-[10px] font-bold uppercase tracking-widest text-slate-400">Status</th>
                                    <th class="px-8 py-4 text-[10px] font-bold uppercase tracking-widest text-slate-400">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach($patients as $index => $patient)
                                    <tr class="hover:bg-blue-50/30 dark:hover:bg-blue-900/10 transition-colors group">
                                        <td class="px-8 py-6 text-sm font-medium text-slate-400">
                                            {{ $patients->firstItem() + $index }}
                                        </td>
                                        <td class="px-8 py-6">
                                            <div class="flex flex-col">
                                                <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $patient->pasien->nm_pasien ?? 'N/A' }}</span>
                                                <span class="text-[11px] font-medium text-slate-500 mt-0.5">RM: {{ $patient->no_rkm_medis }} • RAWAT: {{ $patient->no_rawat }}</span>
                                            </div>
                                        </td>
                                        <td class="px-8 py-6">
                                            <div class="flex items-center text-sm font-semibold text-slate-600 dark:text-slate-400">
                                                <i class="far fa-clock mr-2 text-blue-500 opacity-70"></i>
                                                {{ $patient->jam_reg ? $patient->jam_reg->format('H:i') : '--:--' }}
                                            </div>
                                        </td>
                                        <td class="px-8 py-6">
                                            <div class="flex -space-x-2 overflow-hidden">
                                                @php $maxTask = $patient->referensiMobilejknBpjsTaskid->max('taskid') ?? 0; @endphp
                                                @for($i = 1; $i <= 7; $i++)
                                                    @php $hasTask = $patient->referensiMobilejknBpjsTaskid->contains('taskid', $i); @endphp
                                                    <div class="w-7 h-7 rounded-full border-2 border-white dark:border-slate-900 flex items-center justify-center transition-all z-{{ 10-$i }} 
                                                        {{ $hasTask ? 'bg-emerald-500 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-300' }}"
                                                        title="Task {{ $i }}: {{ $hasTask ? 'Completed' : 'Pending' }}">
                                                        <span class="text-[9px] font-bold">{{ $i }}</span>
                                                    </div>
                                                @endfor
                                            </div>
                                        </td>
                                        <td class="px-8 py-6">
                                            @php
                                                $statusClasses = [
                                                    'Belum' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400',
                                                    'Sudah' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400',
                                                    'Batal' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-400',
                                                ];
                                                $class = $statusClasses[$patient->stts] ?? 'bg-slate-100 text-slate-700 dark:bg-slate-500/20 dark:text-slate-400';
                                            @endphp
                                            <span class="px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $class }}">
                                                {{ $patient->stts }}
                                            </span>
                                        </td>
                                        <td class="px-8 py-6">
                                            <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <button onclick="showTaskIdModal('{{ $patient->no_rawat }}', '{{ $patient->referensiMobilejknBpjs->nobooking ?? '' }}', {{ $maxTask }})"
                                                        class="p-2.5 rounded-xl bg-blue-600 text-white hover:bg-blue-700 transition-all shadow-md shadow-blue-500/20"
                                                        title="Sync Task ID">
                                                    <i class="fas fa-sync-alt text-sm"></i>
                                                </button>
                                                <button onclick="showLogModal('{{ $patient->no_rawat }}', null)"
                                                        class="p-2.5 rounded-xl glass hover:bg-slate-100 dark:hover:bg-slate-800 transition-all"
                                                        title="View Logs">
                                                    <i class="fas fa-search text-sm text-slate-500"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="px-8 py-6 bg-slate-50/50 dark:bg-slate-800/30">
                            {{ $patients->links() }}
                        </div>
                    @else
                        <div class="px-8 py-20 text-center space-y-4">
                            <div class="w-20 h-20 bg-slate-100 dark:bg-slate-800 rounded-3xl flex items-center justify-center mx-auto text-slate-300">
                                <i class="fas fa-users text-4xl"></i>
                            </div>
                            <div class="space-y-1">
                                <h3 class="text-xl font-bold">No Patients Found</h3>
                                <p class="text-sm text-slate-500">There are no BPJS patients registered for this period.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals (Upgraded UI) -->
<div id="logModal" class="fixed inset-0 z-[60] hidden group">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="glass w-full max-w-4xl rounded-[40px] shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300">
            <div class="px-10 py-8 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
                <h3 class="text-2xl font-bold tracking-tight">BPJS Log Details</h3>
                <button onclick="closeLogModal()" class="w-10 h-10 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center justify-center transition-colors">
                    <i class="fas fa-times text-slate-400"></i>
                </button>
            </div>
            <div id="logModalContent" class="p-10 max-h-[70vh] overflow-y-auto">
                <!-- Content loaded via AJAX -->
            </div>
            <div class="px-10 py-6 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-3">
                <button onclick="closeLogModal()" class="glass px-6 py-3 rounded-2xl text-sm font-bold hover:bg-slate-100 dark:hover:bg-slate-800 transition-all text-slate-600">Close</button>
            </div>
        </div>
    </div>
</div>

<div id="taskIdModal" class="fixed inset-0 z-[60] hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="glass w-full max-w-3xl rounded-[40px] shadow-2xl overflow-hidden">
            <div class="px-10 py-8 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-blue-600/5">
                <div>
                    <h3 id="taskIdModalTitle" class="text-2xl font-bold tracking-tight text-blue-600 dark:text-blue-400">Task Flow Control</h3>
                    <p class="text-sm text-slate-500 mt-1">Manage BPJS synchronization status</p>
                </div>
                <button onclick="closeTaskIdModal()" class="w-10 h-10 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center justify-center transition-colors">
                    <i class="fas fa-times text-slate-400"></i>
                </button>
            </div>
            <div id="taskIdModalContent" class="p-10 max-h-[60vh] overflow-y-auto">
                <!-- Loading State -->
                <div class="flex flex-col items-center justify-center py-12 space-y-4">
                    <div class="w-12 h-12 border-4 border-blue-600/20 border-t-blue-600 rounded-full animate-spin"></div>
                    <p class="text-sm font-medium text-slate-500">Retrieving patient data...</p>
                </div>
            </div>
            <div class="px-10 py-8 border-t border-slate-200 dark:border-slate-800 flex flex-wrap justify-end gap-3 bg-slate-50/50 dark:bg-slate-900/50">
                <button id="showAntreanFormButton" onclick="showAntreanForm()" class="hidden glass px-6 py-3 rounded-2xl text-sm font-bold text-slate-600 hover:bg-white dark:hover:bg-slate-800 transition-all">
                    Manual Antrean
                </button>
                <button id="taskIdAutoFlowButton" onclick="runTaskIdFlow()" class="hidden bg-amber-500 text-white px-6 py-3 rounded-2xl text-sm font-bold hover:bg-amber-600 transition-all shadow-lg shadow-amber-500/20">
                    Auto Sync (3-5)
                </button>
                <button id="taskIdSendButton" onclick="sendTaskId()" class="bg-blue-600 text-white px-8 py-3 rounded-2xl text-sm font-bold hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/20">
                    Process Task
                </button>
                <button onclick="closeTaskIdModal()" class="glass px-6 py-3 rounded-2xl text-sm font-bold text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800">Cancel</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Constants & Global State
    let currentNoRawat = null;
    let currentKodeBooking = null;
    let currentTaskId = null;
    let taskIdPayload = {};

    // Auto-refresh (Optional, keep if user needs real-time)
    // setTimeout(() => window.location.reload(), 300000);

    // --- Modal Logic ---
    function showLogModal(noRawat, taskId) {
        const modal = document.getElementById('logModal');
        const content = document.getElementById('logModalContent');
        
        content.innerHTML = `
            <div class="flex flex-col items-center justify-center py-20 animate-pulse">
                <div class="w-12 h-12 bg-slate-100 dark:bg-slate-800 rounded-2xl mb-4"></div>
                <div class="h-4 w-48 bg-slate-100 dark:bg-slate-800 rounded-full"></div>
            </div>
        `;
        modal.classList.remove('hidden');

        let url = `/api/bpjs-logs/by-task?no_rawat=${encodeURIComponent(noRawat)}`;
        if (taskId) url += `&task_id=${encodeURIComponent(taskId)}`;

        fetch(url)
            .then(r => r.json())
            .then(res => {
                if (res.success && res.data) {
                    renderLogDetails(res.data, noRawat, taskId);
                } else {
                    renderEmptyLog();
                }
            })
            .catch(err => renderErrorLog(err));
    }

    function renderLogDetails(log, noRawat, taskId) {
        const content = document.getElementById('logModalContent');
        const isSuccess = log.code >= 200 && log.code < 300;
        
        // Simple JSON formatting for display
        let reqView = log.request;
        try { reqView = JSON.stringify(JSON.parse(log.request), null, 4); } catch(e){}
        
        content.innerHTML = `
            <div class="space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="glass p-5 rounded-2xl">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Status Code</p>
                        <p class="text-lg font-bold ${isSuccess ? 'text-emerald-500' : 'text-rose-500'}">${log.code}</p>
                    </div>
                    <div class="glass p-5 rounded-2xl">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Method</p>
                        <p class="text-lg font-bold text-slate-700 dark:text-slate-300">POST</p>
                    </div>
                     <div class="glass p-5 rounded-2xl">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Timestamp</p>
                        <p class="text-sm font-bold text-slate-700 dark:text-slate-300">${new Date(log.created_at).toLocaleString()}</p>
                    </div>
                </div>

                <div class="space-y-3">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 ml-1">Endpoint URL</p>
                    <div class="bg-slate-900 text-slate-300 p-4 rounded-2xl text-xs font-mono break-all border border-slate-800">
                        ${log.url}
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-3">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 ml-1">Request Payload</p>
                        <pre class="bg-slate-50 dark:bg-slate-900 p-5 rounded-2xl text-[11px] font-mono text-slate-600 dark:text-slate-400 border border-slate-100 dark:border-slate-800 overflow-x-auto max-h-[300px]">${reqView}</pre>
                    </div>
                    <div class="space-y-3">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 ml-1">API Response</p>
                        <div class="bg-slate-50 dark:bg-slate-900 p-5 rounded-2xl border border-slate-100 dark:border-slate-800 h-full min-h-[100px]">
                            <p class="text-sm font-medium ${isSuccess ? 'text-emerald-600' : 'text-rose-600'} leading-relaxed">${log.message}</p>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    function renderEmptyLog() {
        document.getElementById('logModalContent').innerHTML = `
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-20 h-20 bg-slate-50 dark:bg-slate-800 rounded-full flex items-center justify-center text-slate-300 mb-6">
                    <i class="fas fa-search text-3xl"></i>
                </div>
                <h4 class="text-xl font-bold">Log Not Found</h4>
                <p class="text-sm text-slate-500 mt-2 max-w-xs">No direct API communication log exists for this specific task and patient.</p>
            </div>
        `;
    }

    function renderErrorLog(err) {
        document.getElementById('logModalContent').innerHTML = `<div class="p-8 text-rose-500 font-bold">${err}</div>`;
    }

    function closeLogModal() { document.getElementById('logModal').classList.add('hidden'); }
    function closeTaskIdModal() { document.getElementById('taskIdModal').classList.add('hidden'); }

    // --- Task Flow Logic ---
    function showTaskIdModal(noRawat, kodeBooking, lastTaskId) {
        currentNoRawat = noRawat;
        currentKodeBooking = kodeBooking;
        currentTaskId = lastTaskId;
        
        const modal = document.getElementById('taskIdModal');
        const content = document.getElementById('taskIdModalContent');
        
        content.innerHTML = `
            <div class="flex flex-col items-center justify-center py-20">
                <div class="w-12 h-12 border-4 border-blue-600/20 border-t-blue-600 rounded-full animate-spin"></div>
                <p class="text-sm font-medium text-slate-500 mt-4">Analyzing task sequence...</p>
            </div>
        `;
        
        modal.classList.remove('hidden');
        document.getElementById('showAntreanFormButton').classList.add('hidden');
        document.getElementById('taskIdAutoFlowButton').classList.add('hidden');
        
        fetch(`/api/regperiksa/patient?no_rawat=${noRawat}`)
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    processTaskData(res);
                } else {
                    renderErrorTask("Could not load registration data.");
                }
            })
            .catch(e => renderErrorTask(e));
    }

    function processTaskData(res) {
        const patient = res.data;
        const taskList = res.task_list || [];
        const task = res.task;
        
        // Show auto-flow if task 3 not done or 4/5 pending
        if (!taskList.find(t => t.taskid == 3)) {
            document.getElementById('taskIdAutoFlowButton').classList.remove('hidden');
        } else if (!taskList.find(t => t.taskid == 5)) {
             document.getElementById('taskIdAutoFlowButton').classList.remove('hidden');
        }

        // Logic to determine next action
        if (patient.stts === 'Batal') {
            setupTaskForm(99, "Pasien Batal", patient, patient.tgl_registrasi + ' ' + (patient.jam_reg || '00:00:00'));
        } else if (!taskList.find(t => t.taskid == 3) && patient.bridging_sep) {
             setupTaskForm(3, "Pasien Kedatangan (Check-in)", patient, patient.tgl_registrasi + ' ' + (patient.jam_reg || '00:00:00'));
        } else if (taskList.find(t => t.taskid == 3) && !taskList.find(t => t.taskid == 4)) {
            setupTaskForm(4, "Mulai Layanan Perawat", patient, task?.examination?.tgl_perawatan + ' ' + (task?.examination?.jam_rawat || '00:00:00'));
        } else if (taskList.find(t => t.taskid == 4) && !taskList.find(t => t.taskid == 5)) {
            setupTaskForm(5, "Mulai Layanan Dokter", patient, task?.doctor?.tgl_perawatan + ' ' + (task?.doctor?.jam_rawat || '00:00:00'));
        } else if (taskList.find(t => t.taskid == 5) && !taskList.find(t => t.taskid == 6)) {
            setupTaskForm(6, "Selesai Layanan Dokter", patient, task?.prescription?.tgl_perawatan + ' ' + (task?.prescription?.jam || '00:00:00'));
        } else if (taskList.find(t => t.taskid == 6) && !taskList.find(t => t.taskid == 7)) {
            setupTaskForm(7, "Selesai Penyerahan Obat", patient, task?.prescription?.tgl_penyerahan + ' ' + (task?.prescription?.jam_penyerahan || '00:00:00'));
        } else if (!patient.bridging_sep && !currentKodeBooking) {
            setupAddAntreanFlow(patient);
        } else {
            document.getElementById('taskIdModalContent').innerHTML = `<div class="p-8 text-center text-emerald-600 font-bold">All standard tasks are completed for this patient.</div>`;
            document.getElementById('taskIdSendButton').classList.add('hidden');
        }
    }

    function setupTaskForm(id, name, patient, time) {
        taskIdPayload = {
            kodebooking: patient.referensi_mobilejkn_bpjs?.nobooking || patient.no_rawat,
            taskid: id,
            waktu: time
        };

        // Render Form
        document.getElementById('taskIdModalContent').innerHTML = `
            <div class="space-y-8">
                <div class="glass p-6 rounded-3xl bg-blue-600/5 border-blue-600/10 flex items-center gap-6">
                    <div class="w-16 h-16 rounded-2xl bg-blue-600 flex items-center justify-center text-white text-2xl font-bold shadow-lg shadow-blue-600/30">
                        ${id}
                    </div>
                    <div>
                        <h4 class="text-xl font-bold tracking-tight">${name}</h4>
                        <p class="text-sm text-slate-500 font-medium">Syncing data for: ${patient.pasien.nm_pasien}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold uppercase tracking-widest text-slate-400 ml-1">Event Timestamp</label>
                        <input type="datetime-local" id="task_waktu" value="${formatToLocal(time)}" 
                               class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 font-semibold text-slate-700 dark:text-slate-300 focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold uppercase tracking-widest text-slate-400 ml-1">Booking Code</label>
                        <div class="w-full bg-slate-100 dark:bg-slate-800/50 rounded-2xl px-5 py-3 font-mono text-xs text-slate-500 border border-slate-200 dark:border-slate-800">
                             ${taskIdPayload.kodebooking}
                        </div>
                    </div>
                </div>

                <div class="bg-amber-50 dark:bg-amber-900/10 p-5 rounded-2xl border border-amber-100 dark:border-amber-900/20">
                    <p class="text-xs text-amber-700 dark:text-amber-400 leading-relaxed">
                        <i class="fas fa-info-circle mr-2"></i> Ensure the timestamp matches hospital records to avoid BPJS validation errors.
                    </p>
                </div>
            </div>
        `;

        const btn = document.getElementById('taskIdSendButton');
        btn.classList.remove('hidden');
        btn.textContent = `Send Task ${id}`;
        btn.onclick = () => {
            taskIdPayload.waktu = fromLocal(document.getElementById('task_waktu').value);
            executeTaskRequest(taskIdPayload);
        };
    }

    function setupAddAntreanFlow(patient) {
        document.getElementById('taskIdModalContent').innerHTML = `
            <div class="text-center py-10 space-y-6">
                <div class="w-20 h-20 bg-amber-100 dark:bg-amber-500/10 rounded-full flex items-center justify-center mx-auto text-amber-600">
                    <i class="fas fa-exclamation-triangle text-3xl"></i>
                </div>
                <div class="space-y-2">
                    <h4 class="text-xl font-bold">Incomplete Data</h4>
                    <p class="text-sm text-slate-500 max-w-sm mx-auto">This patient is not yet connected to the BPJS Antrean system. You must add the appointment first.</p>
                </div>
            </div>
        `;
        const btn = document.getElementById('showAntreanFormButton');
        btn.classList.remove('hidden');
        document.getElementById('taskIdSendButton').classList.add('hidden');
    }

    function executeTaskRequest(payload) {
        const btn = document.getElementById('taskIdSendButton');
        btn.disabled = true;
        btn.innerHTML = `<i class="fas fa-circle-notch animate-spin mr-2"></i> Syncing...`;

        fetch('/api/mobilejkn/update-task-id', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                renderTaskSuccess(payload.taskid, res);
            } else {
                renderTaskError(res);
            }
        })
        .catch(e => renderTaskError({ message: e }));
    }

    async function runTaskIdFlow() {
        const btn = document.getElementById('taskIdAutoFlowButton');
        const mainBtn = document.getElementById('taskIdSendButton');
        const content = document.getElementById('taskIdModalContent');
        
        btn.disabled = true;
        mainBtn.disabled = true;
        
        content.innerHTML = `<div class="space-y-4">`;
        
        const tasks = [3,4,5];
        for (const tid of tasks) {
            content.innerHTML += `<div id="flow-tid-${tid}" class="glass p-4 rounded-xl flex items-center justify-between">
                <span class="font-bold">Task ID ${tid}</span>
                <span class="text-xs text-slate-400">Processing...</span>
            </div>`;
            
            try {
                const r = await fetch('/api/mobilejkn/update-task-id', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ kodebooking: currentKodeBooking || currentNoRawat, taskid: tid })
                });
                const res = await r.json();
                
                const box = document.getElementById(`flow-tid-${tid}`);
                if (res.success) {
                    box.classList.add('bg-emerald-50', 'dark:bg-emerald-500/10', 'border-emerald-200', 'dark:border-emerald-500/20');
                    box.lastElementChild.textContent = 'Success';
                    box.lastElementChild.className = 'text-xs font-bold text-emerald-500';
                } else {
                    box.classList.add('bg-amber-50', 'dark:bg-amber-500/10', 'border-amber-200', 'dark:border-amber-500/20');
                    box.lastElementChild.textContent = res.message || 'Error';
                }
            } catch(e) { /* silent fail */ }
        }
        
        content.innerHTML += `</div>`;
        mainBtn.disabled = false;
        mainBtn.textContent = 'Done - Close';
        mainBtn.onclick = () => window.location.reload();
    }

    function renderTaskSuccess(tid, res) {
         document.getElementById('taskIdModalContent').innerHTML = `
            <div class="text-center py-20 animate-in fade-in slide-in-from-bottom-5 duration-500">
                <div class="w-24 h-24 bg-emerald-500 rounded-[35px] flex items-center justify-center mx-auto text-white text-4xl shadow-2xl shadow-emerald-500/30 mb-8">
                    <i class="fas fa-check"></i>
                </div>
                <h4 class="text-3xl font-bold tracking-tight">Sync Successful!</h4>
                <p class="text-slate-500 mt-3 font-medium">Task ID ${tid} has been updated manually.</p>
                <div class="mt-8 bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 p-4 rounded-2xl max-w-sm mx-auto overflow-hidden">
                    <p class="text-[10px] uppercase font-bold text-slate-400 mb-1">API Message</p>
                    <p class="text-xs font-mono text-slate-600 dark:text-slate-400">${res.message || 'Operation successful'}</p>
                </div>
            </div>
        `;
        const btn = document.getElementById('taskIdSendButton');
        btn.textContent = 'Refresh List';
        btn.onclick = () => window.location.reload();
        btn.disabled = false;
    }

    function renderTaskError(res) {
        alert("Sync Error: " + (res.message || 'Unknown error'));
        const btn = document.getElementById('taskIdSendButton');
        btn.disabled = false;
        btn.textContent = 'Try Again';
    }

    function renderErrorTask(err) {
        document.getElementById('taskIdModalContent').innerHTML = `<div class="p-10 text-rose-500 font-bold">Fatal: ${err}</div>`;
    }

    // --- Utilities ---
    function formatToLocal(str) {
        if (!str) return '';
        if (str.includes(' ')) {
             return str.replace(' ', 'T').substring(0, 16);
        }
        const d = new Date(str);
        if (isNaN(d)) return '';
        return d.toISOString().slice(0, 16);
    }
    
    function fromLocal(val) {
        if (!val) return '';
        return val.replace('T', ' ') + ':00';
    }

    function showAntreanForm() {
        // Fallback for manual antrean add
        const payload = {
            kodebooking: currentKodeBooking || currentNoRawat,
            norm: currentNoRawat
        };
        
        const btn = document.getElementById('showAntreanFormButton');
        btn.disabled = true;
        btn.innerHTML = `<i class="fas fa-circle-notch animate-spin mr-2"></i> Adding...`;

        fetch(`/api/antrian`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ no_rawat: currentNoRawat })
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                alert("Appointment added! You can now send task IDs.");
                window.location.reload();
            } else {
                alert("Error: " + (res.message || 'Failed to add antrean'));
                btn.disabled = false;
                btn.textContent = 'Retry Manual Antrean';
            }
        });
    }

</script>
@endpush
