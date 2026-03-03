@extends('layouts.main')

@section('title', 'Referensi MJKN')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-12">
    <!-- Header -->
    <div class="glass rounded-3xl p-8 mb-8 space-y-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div>
                <h1 class="text-4xl font-bold tracking-tight text-slate-900 dark:text-white">Referensi MJKN</h1>
                <p class="text-slate-500 dark:text-slate-400 mt-2 flex items-center">
                    <i class="fas fa-mobile-screen-button mr-2 text-teal-600"></i>
                    Sync Mobile JKN data with hospital system
                </p>
            </div>
            
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('regperiksa.index') }}"
                   class="glass px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-slate-100 dark:hover:bg-slate-800 transition-all flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Patients
                </a>
                <button onclick="openStatusUpdateModal()" class="bg-teal-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-teal-700 transition-all shadow-lg shadow-teal-500/20 flex items-center">
                    <i class="fas fa-sync-alt mr-2"></i>Bulk Update Status
                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 mb-8">
        <!-- Sidebar Filters -->
        <div class="lg:col-span-1 space-y-6">
            <div class="glass rounded-3xl p-6 shadow-sm">
                <h3 class="text-lg font-bold mb-6 flex items-center">
                    <i class="fas fa-filter mr-2 text-teal-600"></i> Local Filter
                </h3>
                <form method="GET" action="{{ route('referensi.pendafataran') }}" class="space-y-4">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold uppercase tracking-widest text-slate-500 ml-1">Date From</label>
                        <input type="date" name="date_from" value="{{ $request->date_from ?? date('Y-m-d') }}" class="w-full bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-teal-500 transition-all outline-none">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold uppercase tracking-widest text-slate-500 ml-1">Date To</label>
                        <input type="date" name="date_to" value="{{ $request->date_to ?? date('Y-m-d') }}" class="w-full bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-teal-500 transition-all outline-none">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold uppercase tracking-widest text-slate-500 ml-1">No Rawat</label>
                        <input type="text" name="no_rawat" value="{{ $request->no_rawat ?? '' }}" placeholder="Search No Rawat..." class="w-full bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-teal-500 transition-all outline-none">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold uppercase tracking-widest text-slate-500 ml-1">No Booking</label>
                        <input type="text" name="no_booking" value="{{ $request->no_booking ?? '' }}" placeholder="Search Booking Code..." class="w-full bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-teal-500 transition-all outline-none">
                    </div>
                    <div class="pt-4 flex gap-2">
                        <button type="submit" class="flex-1 bg-teal-600 text-white py-2.5 rounded-xl text-sm font-bold hover:bg-teal-700 transition-all">Filter</button>
                        <a href="{{ route('referensi.pendafataran') }}" class="glass px-4 py-2.5 rounded-xl text-slate-500 hover:text-red-500 transition-colors"><i class="fas fa-undo"></i></a>
                    </div>
                </form>
            </div>

            <!-- Stats Mini -->
            <div class="glass rounded-3xl p-6 shadow-sm space-y-4 bg-teal-500/5 border-teal-500/10">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-slate-500 font-bold uppercase tracking-widest text-[9px]">Total Sync</span>
                    <span class="text-xl font-extrabold text-teal-600">{{ $totalReferensi }}</span>
                </div>
                <div class="flex justify-between items-center pt-2">
                    <span class="text-sm font-medium text-slate-500 font-bold uppercase tracking-widest text-[9px]">Today</span>
                    <span class="text-xl font-extrabold text-teal-600">{{ $todayReferensi }}</span>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="lg:col-span-3">
             <div class="glass rounded-3xl shadow-sm overflow-hidden">
                <div class="px-8 py-6 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
                    <h2 class="text-xl font-bold tracking-tight">Sync Registry</h2>
                    <div class="text-sm text-slate-500">
                        {{ $referensis->total() }} results found
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 dark:bg-slate-800/30">
                                <th class="px-8 py-4 text-[10px] font-bold uppercase tracking-widest text-slate-400">Booking Code</th>
                                <th class="px-8 py-4 text-[10px] font-bold uppercase tracking-widest text-slate-400">Identity</th>
                                <th class="px-8 py-4 text-[10px] font-bold uppercase tracking-widest text-slate-400">Date</th>
                                <th class="px-8 py-4 text-[10px] font-bold uppercase tracking-widest text-slate-400">Status</th>
                                <th class="px-8 py-4 text-[10px] font-bold uppercase tracking-widest text-slate-400">Task Flow</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($referensis as $referensi)
                                <tr class="hover:bg-teal-50/20 dark:hover:bg-teal-900/10 transition-colors">
                                    <td class="px-8 py-6">
                                        <span class="inline-block bg-slate-100 dark:bg-slate-900 px-3 py-1.5 rounded-lg text-xs font-mono font-bold text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-800">
                                            {{ $referensi->nobooking }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-slate-900 dark:text-white capitalize">{{ strtolower($referensi->regPeriksa->pasien->nm_pasien ?? 'Unknown') }}</span>
                                            <span class="text-[11px] font-medium text-slate-500 mt-0.5">RM: {{ $referensi->regPeriksa->no_rkm_medis ?? '-' }} • RM: {{ $referensi->nomorkartu }}</span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="flex items-center text-xs font-semibold text-slate-600 dark:text-slate-400">
                                            <i class="far fa-calendar-alt mr-2 text-teal-500 opacity-70"></i>
                                            {{ \Carbon\Carbon::parse($referensi->tanggalperiksa)->format('d M Y') }}
                                        </div>
                                    </td>
                                    <td class="px-8 py-6">
                                        @php
                                            $isCheckin = $referensi->status == '1' || $referensi->status == 'Checkin';
                                            $isBatal = $referensi->status == '0' || $referensi->status == 'Batal';
                                        @endphp
                                        <span class="px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wider 
                                            {{ $isCheckin ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400' : 
                                               ($isBatal ? 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-400' : 
                                               'bg-slate-100 text-slate-700 dark:bg-slate-500/20 dark:text-slate-400') }}">
                                            {{ $isCheckin ? 'Checkin' : ($isBatal ? 'Batal' : ( $referensi->status ?: 'Pending' )) }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($referensi->referensiMobilejknBpjsTaskid->sortBy('taskid') as $task)
                                                <div class="w-6 h-6 rounded-lg bg-teal-500/10 border border-teal-500/20 flex items-center justify-center text-[10px] font-bold text-teal-600">
                                                    {{ $task->taskid }}
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-20 text-center">
                                        <div class="w-20 h-20 bg-slate-100 dark:bg-slate-800 rounded-3xl flex items-center justify-center mx-auto text-slate-300 mb-6">
                                            <i class="fas fa-inbox text-3xl"></i>
                                        </div>
                                        <h3 class="text-xl font-bold">No Sync Records</h3>
                                        <p class="text-sm text-slate-500">MJKN sync records will appear here.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="px-8 py-6 bg-slate-50/50 dark:bg-slate-800/30">
                        {{ $referensis->links() }}
                    </div>
                </div>
             </div>
        </div>
    </div>
</div>

<!-- Bulk Update Modal -->
<div id="statusUpdateModal" class="fixed inset-0 z-[60] hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="glass w-full max-w-5xl rounded-[40px] shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <div class="px-10 py-8 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-teal-600/5">
                <div>
                    <h3 class="text-2xl font-bold tracking-tight text-teal-600 dark:text-teal-400">Sync Status Control</h3>
                    <p class="text-sm text-slate-500 mt-1">Cross-reference with patient registry</p>
                </div>
                <button onclick="closeStatusUpdateModal()" class="w-10 h-10 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 border-none transition-colors">
                    <i class="fas fa-times text-slate-400"></i>
                </button>
            </div>
            
            <div class="p-10 overflow-y-auto flex-grow">
                 <div class="bg-blue-50 dark:bg-blue-900/10 border-l-4 border-blue-600 p-6 rounded-2xl mb-8">
                    <div class="flex items-start gap-4">
                        <i class="fas fa-info-circle text-blue-600 mt-1"></i>
                        <p class="text-sm text-blue-700 dark:text-blue-400 font-medium leading-relaxed">
                            Akan mengupdate status untuk <strong>{{ count($referensis) }}</strong> data. 
                            Sistem akan mensesuaikan status Mobile JKN dengan status kunjungan rumah sakit yang riil.
                        </p>
                    </div>
                </div>

                <div class="glass rounded-3xl overflow-hidden border border-slate-200 dark:border-slate-800">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-800/50 font-bold text-slate-400 uppercase tracking-widest">
                            <tr>
                                <th class="px-6 py-4">Booking Code</th>
                                <th class="px-6 py-4">Patient Name</th>
                                <th class="px-6 py-4">MJKN Status</th>
                                <th class="px-6 py-4">RS Status</th>
                                <th class="px-6 py-4 text-right">Proposed Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($referensis as $referensi)
                                @php
                                    $regStts = $referensi->regPeriksa->stts ?? 'N/A';
                                    $proposed = 'Check-in';
                                    $proposedClass = 'text-emerald-500';
                                    if ($regStts == 'Sudah') {
                                        $proposed = 'Check-in';
                                        $proposedClass = 'text-emerald-500';
                                    } elseif (in_array($regStts, ['Batal', 'Belum'])) {
                                        $proposed = 'Cancel';
                                        $proposedClass = 'text-rose-500';
                                    } else {
                                        $proposed = 'None';
                                        $proposedClass = 'text-slate-400';
                                    }
                                @endphp
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20">
                                    <td class="px-6 py-4 font-mono font-bold">{{ $referensi->nobooking }}</td>
                                    <td class="px-6 py-4 font-bold text-slate-900 dark:text-white">{{ $referensi->regPeriksa->pasien->nm_pasien ?? 'N/A' }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 font-bold text-[9px] uppercase border border-slate-200 dark:border-slate-800">
                                            {{ $referensi->status ?: 'Pending' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="font-bold {{ $regStts == 'Sudah' ? 'text-emerald-500' : 'text-amber-500' }}">{{ $regStts }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right font-black uppercase tracking-widest {{ $proposedClass }} italic">
                                        {{ $proposed }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="px-10 py-8 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-3 bg-slate-50/50 dark:bg-slate-900/50">
                <button onclick="closeStatusUpdateModal()" class="glass px-6 py-3 rounded-2xl text-sm font-bold text-slate-500">Close</button>
                <button onclick="updateStatus()" class="bg-teal-600 text-white px-8 py-3 rounded-2xl text-sm font-bold shadow-lg shadow-teal-500/20 hover:bg-teal-700 transition-all">
                    Confirm & Sync All
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openStatusUpdateModal() { document.getElementById('statusUpdateModal').classList.remove('hidden'); }
    function closeStatusUpdateModal() { document.getElementById('statusUpdateModal').classList.add('hidden'); }

    function updateStatus() {
        const btn = event.currentTarget;
        const originalText = btn.innerHTML;
        
        btn.disabled = true;
        btn.innerHTML = `<i class="fas fa-circle-notch animate-spin mr-2"></i> Syncing...`;

        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        
        // Collect current filters
        const searchParams = new URLSearchParams(window.location.search);
        searchParams.forEach((value, key) => formData.append(key, value));

        // Collect booking list
        const bookings = Array.from(document.querySelectorAll('#statusUpdateModal tbody tr')).map(row => {
            return row.querySelector('td').textContent.trim();
        });
        formData.append('no_booking_list', JSON.stringify(bookings));

        fetch('{{ route("referensi.pendafataran") }}', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert(`Sync Complete!\nCheckins: ${data.checkin_count}\nCancellations: ${data.cancelled_count}`);
                window.location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(e => alert('Fatal error during sync'))
        .finally(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }
</script>
@endpush