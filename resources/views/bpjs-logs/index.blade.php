@extends('layouts.main')

@section('title', 'BPJS API Logs')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-12">
    <!-- Header -->
    <div class="glass rounded-3xl p-8 mb-8 space-y-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div>
                <h1 class="text-4xl font-bold tracking-tight text-slate-900 dark:text-white">API Communications</h1>
                <p class="text-slate-500 dark:text-slate-400 mt-2 flex items-center">
                    <i class="fas fa-network-wired mr-2 text-rose-600"></i>
                    Monitor and track BPJS Web Service logs
                </p>
            </div>
            
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('regperiksa.index') }}"
                   class="glass px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-slate-100 dark:hover:bg-slate-800 transition-all flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Patients
                </a>
                <a href="{{ route('taskid.logs') }}"
                   class="bg-indigo-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-500/20 flex items-center">
                    <i class="fas fa-tasks mr-2"></i>Task ID Logs
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="glass p-6 rounded-3xl border-emerald-500/10 bg-emerald-500/5">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500 text-white flex items-center justify-center shadow-lg shadow-emerald-500/20">
                    <i class="fas fa-check-circle text-xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Success (2xx)</p>
                    <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ $logs->where('code', '>=', 200)->where('code', '<', 300)->count() }}</p>
                </div>
            </div>
        </div>

        <div class="glass p-6 rounded-3xl border-rose-500/10 bg-rose-500/5">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 rounded-2xl bg-rose-500 text-white flex items-center justify-center shadow-lg shadow-rose-500/20">
                    <i class="fas fa-times-circle text-xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Errors (4xx/5xx)</p>
                    <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ $logs->where('code', '>=', 400)->count() }}</p>
                </div>
            </div>
        </div>

        <div class="glass p-6 rounded-3xl border-blue-500/10 bg-blue-500/5">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 rounded-2xl bg-blue-500 text-white flex items-center justify-center shadow-lg shadow-blue-500/20">
                    <i class="fas fa-server text-xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Total Requests</p>
                    <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ $logs->count() }}</p>
                </div>
            </div>
        </div>

        <div class="glass p-6 rounded-3xl border-amber-500/10 bg-amber-500/5">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center shadow-lg shadow-amber-500/20">
                    <i class="fas fa-exclamation-triangle text-xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Warnings (3xx)</p>
                    <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ $logs->where('code', '>=', 300)->where('code', '<', 400)->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="glass rounded-3xl shadow-sm overflow-hidden">
        <div class="px-8 py-6 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-white/50 dark:bg-slate-900/50">
            <h2 class="text-xl font-bold tracking-tight">Recent Logs</h2>
            <div class="flex items-center text-xs font-medium text-slate-400">
                <i class="fas fa-sync-alt animate-spin mr-2"></i> Real-time tracking active
            </div>
        </div>

        <div class="overflow-x-auto">
            @if($logs->count() > 0)
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 dark:bg-slate-800/30">
                            <th class="px-8 py-4 text-[10px] font-bold uppercase tracking-widest text-slate-400">ID</th>
                            <th class="px-8 py-4 text-[10px] font-bold uppercase tracking-widest text-slate-400">Status</th>
                            <th class="px-8 py-4 text-[10px] font-bold uppercase tracking-widest text-slate-400">Method</th>
                            <th class="px-8 py-4 text-[10px] font-bold uppercase tracking-widest text-slate-400">URL / Endpoint</th>
                            <th class="px-8 py-4 text-[10px] font-bold uppercase tracking-widest text-slate-400">Payload</th>
                            <th class="px-8 py-4 text-[10px] font-bold uppercase tracking-widest text-slate-400">Timestamp</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($logs as $log)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors group">
                                <td class="px-8 py-6 text-sm font-medium text-slate-400">
                                    {{ $log->id }}
                                </td>
                                <td class="px-8 py-6">
                                    @php
                                        $isSuccess = $log->code >= 200 && $log->code < 300;
                                        $isError = $log->code >= 400;
                                        $isWarning = $log->code >= 300 && $log->code < 400;
                                        
                                        $colorClass = $isSuccess ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400' : 
                                                     ($isError ? 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-400' : 
                                                     'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400');
                                    @endphp
                                    <span class="px-3 py-1 rounded-full text-[10px] font-bold {{ $colorClass }}">
                                        {{ $log->code }}
                                    </span>
                                </td>
                                <td class="px-8 py-6">
                                    <span class="text-xs font-bold text-blue-600 dark:text-blue-400">
                                        {{ $log->method }}
                                    </span>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="max-w-xs truncate text-xs font-mono text-slate-500 group-hover:text-slate-900 dark:group-hover:text-slate-300 transition-colors" title="{{ $log->url }}">
                                        {{ $log->url }}
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <button class="text-[10px] font-bold uppercase tracking-widest text-slate-400 hover:text-blue-600 transition-colors" onclick="alert(JSON.stringify(JSON.parse({!! json_encode($log->request) !!}), null, 4))">
                                        View Data <i class="fas fa-external-link-alt ml-1"></i>
                                    </button>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap text-xs font-semibold text-slate-500">
                                    {{ $log->created_at ? $log->created_at->format('d M H:i:s') : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="px-8 py-20 text-center space-y-4">
                    <i class="fas fa-inbox text-5xl text-slate-200"></i>
                    <h3 class="text-xl font-bold">No Records Found</h3>
                    <p class="text-sm text-slate-500 max-w-xs mx-auto">Communication logs will appear here once the API bridge starts processing requests.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
