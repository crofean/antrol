@extends('layouts.main')

@section('title', 'Run Command')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-12">
    <!-- Header -->
    <div class="glass rounded-3xl p-8 mb-8 space-y-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div>
                <h1 class="text-4xl font-bold tracking-tight text-slate-900 dark:text-white">Batch Processor</h1>
                <p class="text-slate-500 dark:text-slate-400 mt-2 flex items-center">
                    <i class="fas fa-terminal mr-2 text-amber-600"></i>
                    Execute automated Task ID sequences for specific date ranges
                </p>
            </div>
            
            <a href="{{ route('regperiksa.index') }}"
               class="glass px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-slate-100 dark:hover:bg-slate-800 transition-all flex items-center">
                <i class="fas fa-arrow-left mr-2"></i>Back to Patients
            </a>
        </div>
    </div>

    <!-- Main Tool -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-1">
            <div class="glass rounded-3xl p-8 shadow-sm space-y-8">
                <h3 class="text-xl font-bold flex items-center">
                    <i class="fas fa-cog mr-3 text-amber-500"></i> Configuration
                </h3>

                <!-- Queue Alert -->
                <div id="queueHelperAlert" class="hidden p-4 rounded-2xl bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-900/20">
                    <div class="flex gap-3">
                        <i class="fas fa-exclamation-triangle text-amber-600 mt-1"></i>
                        <div class="text-[11px] text-amber-800 dark:text-amber-400 font-medium">
                            <strong>Queue workers might not be running.</strong> 
                            Run <code class="bg-amber-100 dark:bg-amber-900/40 px-1.5 py-0.5 rounded">php artisan queue:work</code> in terminal.
                        </div>
                    </div>
                </div>

                <form id="commandForm" class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold uppercase tracking-widest text-slate-400 ml-1">Date From</label>
                        <input type="date" id="date_from" name="date_from" value="{{ date('Y-m-d') }}"
                               class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 font-semibold text-slate-700 focus:ring-2 focus:ring-amber-500 outline-none transition-all">
                    </div>
                    
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold uppercase tracking-widest text-slate-400 ml-1">Date To</label>
                        <input type="date" id="date_to" name="date_to" value="{{ date('Y-m-d') }}"
                               class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 font-semibold text-slate-700 focus:ring-2 focus:ring-amber-500 outline-none transition-all">
                    </div>

                    <div class="flex items-center gap-3 p-4 glass rounded-2xl">
                        <div class="relative inline-flex h-6 w-11 items-center rounded-full bg-slate-200 dark:bg-slate-800 transition-colors pointer-events-none">
                            <input type="checkbox" id="dry_run" name="dry_run" checked class="sr-only peer pointer-events-auto">
                            <div class="peer-checked:bg-amber-600 absolute inset-0 rounded-full transition-colors"></div>
                            <span class="absolute left-1 h-4 w-4 rounded-full bg-white transition-transform peer-checked:translate-x-5 shadow-sm"></span>
                        </div>
                        <label for="dry_run" class="text-xs font-bold text-slate-500 cursor-pointer">Dry Run Mode</label>
                    </div>

                    <button type="submit" class="w-full bg-slate-900 dark:bg-white dark:text-slate-900 text-white py-4 rounded-2xl font-black uppercase tracking-widest text-xs hover:opacity-90 shadow-xl transition-all">
                        Execute Sequence
                    </button>
                </form>
            </div>
        </div>

        <!-- Terminal Output -->
        <div class="lg:col-span-2">
            <div class="glass h-[600px] rounded-[40px] shadow-2xl flex flex-col overflow-hidden border-slate-900/5 dark:border-white/5">
                <div class="px-8 py-6 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-slate-900">
                    <div class="flex items-center gap-3">
                        <div id="statusIndicator" class="w-2.5 h-2.5 rounded-full bg-slate-700"></div>
                        <span id="statusText" class="text-[10px] font-black uppercase tracking-widest text-slate-400">Terminal Offline</span>
                    </div>
                    <button id="stopButton" class="hidden px-4 py-1.5 rounded-lg bg-rose-500 text-white text-[10px] font-bold uppercase transition-all hover:bg-rose-600">
                        Kill Process
                    </button>
                </div>
                
                <div id="outputArea" class="flex-grow bg-slate-900 p-8 font-mono text-[11px] leading-relaxed text-emerald-500/90 overflow-y-auto whitespace-pre-wrap selection:bg-emerald-500/20">
                    <div class="text-slate-500 italic flex flex-col items-center justify-center h-full space-y-4">
                        <i class="fas fa-terminal text-4xl opacity-10"></i>
                        <span>Waiting for command execution...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let jobId = null;
    let outputInterval = null;
    
    document.getElementById('commandForm').onsubmit = (e) => {
        e.preventDefault();
        
        const payload = {
            date_from: document.getElementById('date_from').value,
            date_to: document.getElementById('date_to').value,
            dry_run: document.getElementById('dry_run').checked
        };
        
        const output = document.getElementById('outputArea');
        output.innerHTML = `<span class="text-white font-bold animate-pulse">Initializing pipeline...</span>\n\n`;
        
        document.getElementById('stopButton').classList.remove('hidden');
        updateStatus('running');

        fetch('{{ route("command.run") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(data => {
            jobId = data.job_id;
            outputInterval = setInterval(fetchOutput, 1000);
            if (data.queue_info?.message) output.innerHTML += `<span class="text-blue-400">[info]</span> ${data.queue_info.message}\n`;
        })
        .catch(err => {
            output.innerHTML += `<span class="text-rose-500">[error]</span> Failed to start sync engine.\n`;
            updateStatus('failed');
        });
    };

    function fetchOutput() {
        if (!jobId) return;
        
        fetch(`{{ route('command.output', ['jobId' => ':jobId']) }}`.replace(':jobId', jobId))
            .then(r => r.json())
            .then(data => {
                const area = document.getElementById('outputArea');
                
                // Colorize the output
                if (data.output) {
                    area.textContent = data.output.join('').replace(/✓/g, '✔').replace(/✗/g, '✘');
                    area.scrollTop = area.scrollHeight;
                }

                if (data.status === 'completed') {
                    updateStatus('completed');
                    clearInterval(outputInterval);
                    document.getElementById('stopButton').classList.add('hidden');
                } else if (data.status === 'failed') {
                    updateStatus('failed');
                    clearInterval(outputInterval);
                } else if (data.status === 'stopped') {
                    updateStatus('stopped');
                    clearInterval(outputInterval);
                }

                if (data.queue_status?.queue_status === 'no_workers') {
                    document.getElementById('queueHelperAlert').classList.remove('hidden');
                }
            });
    }

    function updateStatus(stts) {
        const ind = document.getElementById('statusIndicator');
        const txt = document.getElementById('statusText');
        const colors = {
            running: 'bg-amber-500 animate-pulse',
            completed: 'bg-emerald-500',
            failed: 'bg-rose-500',
            stopped: 'bg-slate-500'
        };
        const labels = {
            running: 'Processing Batch',
            completed: 'Sync Finished',
            failed: 'Process Halted',
            stopped: 'Process Killed'
        };
        
        ind.className = `w-2.5 h-2.5 rounded-full ${colors[stts]}`;
        txt.textContent = labels[stts];
    }

    document.getElementById('stopButton').onclick = () => {
        if (!jobId) return;
        fetch(`{{ route('command.stop') }}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ job_id: jobId })
        });
    };
</script>
@endpush
