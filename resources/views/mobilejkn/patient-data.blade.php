@extends('layouts.main')

@section('title', 'Patient Lookup')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-12">
    <!-- Header -->
    <div class="glass rounded-3xl p-8 mb-8 space-y-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div>
                <h1 class="text-4xl font-bold tracking-tight text-slate-900 dark:text-white">Patient Intelligence</h1>
                <p class="text-slate-500 dark:text-slate-400 mt-2 flex items-center">
                    <i class="fas fa-search-plus mr-2 text-indigo-600"></i>
                    Deep dive into patient record and BPJS task sequence
                </p>
            </div>
            
            <a href="{{ route('regperiksa.index') }}"
               class="glass px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-slate-100 dark:hover:bg-slate-800 transition-all flex items-center">
                <i class="fas fa-arrow-left mr-2"></i>Back to Patients
            </a>
        </div>
    </div>

    <!-- Main Logic -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Search Input -->
        <div class="lg:col-span-1">
            <div class="glass rounded-3xl p-6 shadow-sm space-y-6">
                <div class="space-y-4">
                    <label class="text-[10px] font-bold uppercase tracking-widest text-slate-400 ml-1">Reg No / Booking</label>
                    <form id="patientDataForm" class="flex flex-col gap-3">
                        <input type="text" id="regNo" placeholder="e.g. 2024/01/0001" 
                               class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 font-semibold focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                        <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-2xl font-bold text-sm shadow-lg shadow-indigo-600/20 hover:opacity-90 transition-all">
                            Retrieve Data
                        </button>
                    </form>
                </div>
                
                <div id="errorMessage" class="hidden p-4 rounded-xl bg-rose-50 border border-rose-100 text-rose-600 text-xs font-medium"></div>
            </div>
        </div>

        <!-- Result Display -->
        <div class="lg:col-span-3">
             <div id="patientDataResults" class="hidden space-y-8 animate-in fade-in slide-in-from-right-5 duration-500">
                <!-- Patient Info Card -->
                <div class="glass rounded-3xl p-8 border-indigo-500/10 bg-indigo-500/5 grid grid-cols-1 md:grid-cols-2 gap-12">
                     <div class="space-y-6">
                         <div>
                             <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Full Name</p>
                             <p class="text-2xl font-black text-slate-900 dark:text-white" id="patientName">---</p>
                         </div>
                         <div class="flex gap-12">
                             <div>
                                 <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Med-Rec</p>
                                 <p class="font-bold text-slate-700 dark:text-slate-300" id="patientMRN">---</p>
                             </div>
                             <div>
                                 <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Reg No</p>
                                 <p class="font-bold text-slate-700 dark:text-slate-300" id="registrationNo">---</p>
                             </div>
                         </div>
                     </div>
                     <div class="space-y-6">
                         <div class="flex items-start gap-4 p-4 glass rounded-2xl border-white/50">
                             <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-indigo-600 shadow-sm">
                                 <i class="fas fa-user-md"></i>
                             </div>
                             <div>
                                 <p class="text-[9px] font-bold uppercase tracking-widest text-slate-400">Attending Physician</p>
                                 <p class="text-sm font-bold text-slate-800 dark:text-slate-200" id="doctorName">---</p>
                             </div>
                         </div>
                         <div class="grid grid-cols-2 gap-4">
                             <div class="p-4 glass rounded-2xl border-white/50">
                                 <p class="text-[9px] font-bold uppercase tracking-widest text-slate-400">Clinic</p>
                                 <p class="text-sm font-bold text-slate-800 dark:text-slate-200" id="polyName">---</p>
                             </div>
                             <div class="p-4 glass rounded-2xl border-white/50">
                                 <p class="text-[9px] font-bold uppercase tracking-widest text-slate-400">Visit Date</p>
                                 <p class="text-sm font-bold text-slate-800 dark:text-slate-200" id="visitDate">---</p>
                             </div>
                         </div>
                     </div>
                </div>

                <!-- BPJS & Task Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                     <div class="lg:col-span-1 space-y-6">
                          <div class="glass rounded-3xl p-6 border-blue-500/10 bg-blue-500/5">
                            <h4 class="text-sm font-black uppercase tracking-widest mb-4 flex items-center">
                                <i class="fas fa-shield-alt mr-2 text-blue-500"></i> BPJS Linkage
                            </h4>
                            <div class="space-y-4">
                                <div>
                                    <p class="text-[9px] font-bold uppercase text-slate-400">Booking Code</p>
                                    <p class="text-sm font-mono font-black text-blue-600" id="bookingCode">---</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-bold uppercase text-slate-400">Card Number</p>
                                    <p class="text-sm font-bold text-slate-700 dark:text-slate-300" id="bpjsNumber">---</p>
                                </div>
                            </div>
                          </div>
                          
                          <button id="updateAllTasks" class="w-full py-4 rounded-3xl bg-emerald-500 text-white font-black uppercase tracking-widest text-[10px] shadow-lg shadow-emerald-500/20 hover:scale-[1.02] active:scale-95 transition-all">
                              Auto-Complete Sequences
                          </button>
                     </div>

                     <div class="lg:col-span-2">
                        <div class="glass rounded-3xl overflow-hidden border border-slate-200 dark:border-slate-800">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-50 dark:bg-slate-800/50 font-bold text-slate-400 uppercase tracking-widest">
                                    <tr>
                                        <th class="px-6 py-4">ID</th>
                                        <th class="px-6 py-4">Process</th>
                                        <th class="px-6 py-4">Status / Local Sync</th>
                                        <th class="px-6 py-4 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="taskTable" class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                                    <!-- JS Inject -->
                                </tbody>
                            </table>
                        </div>
                     </div>
                </div>
             </div>
             
             <!-- Welcome/Empty State -->
             <div id="initialState" class="flex flex-col items-center justify-center py-40 space-y-6 opacity-30 select-none">
                 <div class="w-32 h-32 rounded-[40px] bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                     <i class="fas fa-fingerprint text-6xl"></i>
                 </div>
                 <div class="text-center">
                    <h3 class="text-2xl font-black italic tracking-tighter">AWAITING INPUT</h3>
                    <p class="text-sm font-bold uppercase tracking-widest">Enter registration number to scan</p>
                 </div>
             </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const taskDescriptions = {
        '3': 'Check-in (Kedatangan)',
        '4': 'Nurse Service',
        '5': 'Physician Service',
        '6': 'End of Care',
        '7': 'Pharmacy Output'
    };

    $(document).ready(() => {
        $('#patientDataForm').on('submit', e => {
            e.preventDefault();
            const q = $('#regNo').val().trim();
            if(q) fetchPatient(q);
        });

        $('#updateAllTasks').on('click', () => {
            const bc = $('#bookingCode').text();
            if(bc) updateMissing(bc);
        });
    });

    function fetchPatient(no) {
        $('#initialState').addClass('hidden');
        $('#patientDataResults').addClass('opacity-50 pointer-events-none');
        
        axios.get(`/api/mobilejkn/get-patient-data/${no}`)
            .then(res => {
                if(res.data.status) renderPatient(res.data.data);
                else throw new Error(res.data.message);
            })
            .catch(e => {
                alert(e.message || 'Lookup failed');
                $('#initialState').removeClass('hidden');
            })
            .finally(() => {
                $('#patientDataResults').removeClass('opacity-50 pointer-events-none');
            });
    }

    function renderPatient(data) {
        $('#patientName').text(data.registration.nm_pasien);
        $('#patientMRN').text(data.registration.no_rkm_medis);
        $('#registrationNo').text(data.registration.no_rawat);
        $('#doctorName').text(data.doctor?.nm_dokter || 'NOT ASSIGNED');
        $('#polyName').text(data.registration.nm_poli);
        $('#visitDate').text(data.registration.tgl_registrasi);
        $('#bookingCode').text(data.kodebooking || 'N/A');
        $('#bpjsNumber').text(data.referral?.no_kartu || 'N/A');

        const table = $('#taskTable').empty();
        [3,4,5,6,7].forEach(tid => {
            const ts = data.task_timestamps[tid];
            const row = $(`<tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-all">
                <td class="px-6 py-5 font-black text-indigo-600">${tid}</td>
                <td class="px-6 py-5 font-bold text-slate-700 dark:text-slate-300">${taskDescriptions[tid]}</td>
                <td class="px-6 py-5">
                    ${ts ? `<span class="flex items-center text-emerald-500 font-bold"><i class="fas fa-check-circle mr-2 opacity-50"></i> ${new Date(parseInt(ts)).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'})}</span>` : `<span class="text-rose-400/50 italic">MISSING</span>`}
                </td>
                <td class="px-6 py-5 text-right">
                    <button class="px-4 py-1.5 rounded-xl border border-slate-200 dark:border-slate-800 text-[9px] font-black uppercase tracking-widest hover:bg-slate-900 hover:text-white dark:hover:bg-white dark:hover:text-slate-900 transition-all ${ts ? 'opacity-30' : ''}" 
                            onclick="updateSingle('${data.kodebooking}', ${tid})">
                        ${ts ? 'Force Update' : 'Push Sync'}
                    </button>
                </td>
            </tr>`);
            table.append(row);
        });

        $('#patientDataResults').removeClass('hidden');
    }

    function updateSingle(bc, tid) {
        if(!confirm(`Push Task ID ${tid} for ${bc}?`)) return;
        axios.post('/api/mobilejkn/update-task-id-now', { kodebooking: bc, taskid: tid })
            .then(() => fetchPatient($('#regNo').val()))
            .catch(e => alert('Sync error: ' + e.message));
    }

    function updateMissing(bc) {
         const missing = [];
         $('#taskTable tr').each(function() {
             if($(this).find('td:nth-child(3)').text().includes('MISSING')) {
                 missing.push($(this).find('td:first').text());
             }
         });
         
         if(missing.length == 0) return alert('Trajectory complete.');
         if(!confirm(`Batch update sequence: ${missing.join(' → ')}?`)) return;

         axios.post('/api/mobilejkn/batch-update-task-ids', { kodebooking: bc, taskids: missing })
            .then(res => {
                alert(`Pipeline successful. ${res.data.data.updated} tasks updated.`);
                fetchPatient($('#regNo').val());
            })
            .catch(e => alert('Batch error'));
    }
</script>
@endpush
