@extends('mobilejkn.layout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Referensi Pendafataran MJKN</h1>
                <p class="text-gray-600 mt-1">Data referensi pendaftaran Mobile JKN BPJS</p>
            </div>
            <div class="flex space-x-4">
                <a href="{{ route('regperiksa.index') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Patients
                </a>
                <a href="{{ route('taskid.logs') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md transition duration-200">
                    <i class="fas fa-tasks mr-2"></i>Task ID Logs
                </a>
                <a href="{{ route('bpjs-logs.index') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md transition duration-200">
                    <i class="fas fa-list mr-2"></i>BPJS Logs
                </a>
            </div>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-{{ $filteredCount !== null ? '3' : '2' }} gap-4 mb-6">
            <div class="bg-blue-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Total Referensi</h3>
                <p class="text-2xl font-bold text-gray-800">{{ $totalReferensi }}</p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Referensi Hari Ini</h3>
                <p class="text-2xl font-bold text-gray-800">{{ $todayReferensi }}</p>
            </div>
            @if($filteredCount !== null)
            <div class="bg-purple-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Hasil Filter</h3>
                <p class="text-2xl font-bold text-gray-800">{{ $filteredCount }}</p>
            </div>
            @endif
        </div>

        <!-- Filters -->
        <form method="GET" action="{{ route('referensi.pendafataran') }}" class="bg-gray-50 p-4 rounded-lg mb-6">
            <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Dari</label>
                    <input type="date" name="date_from" id="date_from" value="{{ $request->date_from ?? date('Y-m-d') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Sampai</label>
                    <input type="date" name="date_to" id="date_to" value="{{ $request->date_to ?? date('Y-m-d') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="no_rawat" class="block text-sm font-medium text-gray-700 mb-1">No Rawat</label>
                    <input type="text" name="no_rawat" id="no_rawat" value="{{ $request->no_rawat ?? '' }}"
                           placeholder="Masukkan No Rawat"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="no_booking" class="block text-sm font-medium text-gray-700 mb-1">No Booking</label>
                    <input type="text" name="no_booking" id="no_booking" value="{{ $request->no_booking ?? '' }}"
                           placeholder="Masukkan No Booking"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" id="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Status</option>
                        <option value="1" {{ $request->status == '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ $request->status == '0' ? 'selected' : '' }}>Non-Aktif</option>
                        <option value="belum" {{ $request->status == 'belum' ? 'selected' : '' }}>Belum</option>
                    </select>
                </div>
                <div class="flex items-end space-x-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition duration-200">
                        <i class="fas fa-search mr-2"></i>Filter
                    </button>
                    <a href="{{ route('referensi.pendafataran') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md transition duration-200">
                        <i class="fas fa-times mr-2"></i>Reset
                    </a>
                </div>
            </div>
        </form>

        <!-- Bulk Actions -->
        @if($referensis->total() > 0)
        <div class="flex justify-between items-center mb-4">
            <div class="text-sm text-gray-600">
                @if($filteredCount !== null)
                    {{ $filteredCount }} data terfilter dari {{ $totalReferensi }} total
                @else
                    {{ $referensis->total() }} data ditampilkan
                @endif
            </div>
            <button onclick="openStatusUpdateModal()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md transition duration-200">
                <i class="fas fa-sync-alt mr-2"></i>Update Status
            </button>
        </div>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dateFromInput = document.getElementById('date_from');
            const dateToInput = document.getElementById('date_to');

            dateFromInput.addEventListener('change', function() {
                if (!dateToInput.value || dateToInput.value < this.value) {
                    dateToInput.value = this.value;
                }
            });

            dateToInput.addEventListener('change', function() {
                if (this.value < dateFromInput.value) {
                    this.value = dateFromInput.value;
                }
            });
        });
    </script>

    <!-- Referensi Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800">
                    Daftar Referensi Pendafataran
                    <span class="text-sm font-normal text-gray-600 ml-2">
                        ({{ $referensis->total() }} hasil{{ $referensis->hasPages() ? ' - Halaman ' . $referensis->currentPage() . ' dari ' . $referensis->lastPage() : '' }})
                    </span>
                </h2>
                @if($request->hasAny(['date_from', 'date_to', 'no_rawat', 'no_booking', 'status']))
                <div class="text-sm text-gray-600">
                    <i class="fas fa-filter mr-1"></i>
                    Filter aktif:
                    @if($request->date_from || $request->date_to)
                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs mr-1">
                            Tanggal:
                            @if($request->date_from && $request->date_to)
                                {{ \Carbon\Carbon::parse($request->date_from)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($request->date_to)->format('d/m/Y') }}
                            @elseif($request->date_from)
                                Dari {{ \Carbon\Carbon::parse($request->date_from)->format('d/m/Y') }}
                            @elseif($request->date_to)
                                Sampai {{ \Carbon\Carbon::parse($request->date_to)->format('d/m/Y') }}
                            @endif
                        </span>
                    @endif
                    @if($request->no_rawat)<span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs mr-1">No Rawat: {{ $request->no_rawat }}</span>@endif
                    @if($request->no_booking)<span class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs mr-1">No Booking: {{ $request->no_booking }}</span>@endif
                    @if($request->status)
                        <span class="bg-orange-100 text-orange-800 px-2 py-1 rounded text-xs">
                            Status:
                            @if($request->status == '1') Aktif
                            @elseif($request->status == '0') Non-Aktif
                            @elseif($request->status == 'belum') Belum
                            @else {{ $request->status }}
                            @endif
                        </span>
                    @endif
                </div>
                @endif
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No Booking</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No Rawat</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pasien</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No Kartu</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Periksa</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Poli</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dokter</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Task ID</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($referensis as $referensi)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $referensi->nobooking }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $referensi->no_rawat }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($referensi->regPeriksa && $referensi->regPeriksa->pasien)
                                {{ $referensi->regPeriksa->pasien->nm_pasien }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $referensi->nomorkartu }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($referensi->tanggalperiksa)->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $referensi->kodepoli }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $referensi->kodedokter }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                @if($referensi->status == '1') bg-green-100 text-green-800
                                @elseif($referensi->status == '0') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800 @endif">
                                @if($referensi->status == '1') Aktif
                                @elseif($referensi->status == '0') Non-Aktif
                                @else {{ $referensi->status }} @endif
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($referensi->referensiMobilejknBpjsTaskid->count() > 0)
                                <div class="flex flex-wrap gap-1">
                                    @foreach($referensi->referensiMobilejknBpjsTaskid->sortBy('taskid') as $task)
                                        <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">
                                            {{ $task->taskid }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-4 text-center text-gray-500">
                            Tidak ada data referensi ditemukan
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($referensis->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $referensis->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Status Update Modal -->
<div id="statusUpdateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white max-h-screen overflow-y-auto">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Update Status Referensi</h3>
                <button onclick="closeStatusUpdateModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="mb-4">
                <p class="text-sm text-gray-600 mb-4">
                    Akan mengupdate status untuk <strong>{{ count($referensis) }}</strong> data yang ditampilkan.
                    Status akan diupdate berdasarkan status Reg Periksa:
                </p>
                <ul class="text-sm text-gray-600 mb-4 space-y-1">
                    <li><i class="fas fa-check-circle text-green-500 mr-2"></i>Jika Reg Periksa status "Sudah" → Status akan diupdate ke Check-in</li>
                    <li><i class="fas fa-times-circle text-red-500 mr-2"></i>Jika Reg Periksa status "Batal" atau "Belum" → Status akan dibatalkan</li>
                </ul>
            </div>

            <!-- Data Preview -->
            <div class="mb-6">
                <h4 class="text-md font-semibold text-gray-800 mb-3">Data yang akan diupdate:</h4>
                <div class="bg-gray-50 rounded-lg p-4 max-h-96 overflow-y-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">No Booking</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Pasien</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status Saat Ini</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Reg Periksa Status</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Aksi yang Akan Dilakukan</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($referensis as $referensi)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $referensi->nobooking }}
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                    @if($referensi->regPeriksa && $referensi->regPeriksa->pasien)
                                        {{ $referensi->regPeriksa->pasien->nm_pasien }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @if($referensi->status == '1') bg-green-100 text-green-800
                                        @elseif($referensi->status == '0') bg-yellow-100 text-yellow-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        @if($referensi->status == '1') Aktif
                                        @elseif($referensi->status == '0') Non-Aktif
                                        @else {{ $referensi->status }} @endif
                                    </span>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">
                                    @if($referensi->regPeriksa)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @if($referensi->regPeriksa->stts == 'Sudah') bg-green-100 text-green-800
                                            @elseif($referensi->regPeriksa->stts == 'Batal') bg-red-100 text-red-800
                                            @elseif($referensi->regPeriksa->stts == 'Belum') bg-yellow-100 text-yellow-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ $referensi->regPeriksa->stts }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm">
                                    @if($referensi->regPeriksa)
                                        @if($referensi->regPeriksa->stts == 'Sudah')
                                            <span class="text-green-600 font-medium">
                                                <i class="fas fa-check-circle mr-1"></i>Check-in
                                            </span>
                                        @elseif(in_array($referensi->regPeriksa->stts, ['Batal', 'Belum']))
                                            <span class="text-red-600 font-medium">
                                                <i class="fas fa-times-circle mr-1"></i>Batal
                                            </span>
                                        @else
                                            <span class="text-gray-500">
                                                <i class="fas fa-question-circle mr-1"></i>Tidak ada aksi
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-gray-400">Tidak ada data Reg Periksa</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <button onclick="closeStatusUpdateModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md transition duration-200">
                    Batal
                </button>
                <button onclick="updateStatus()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md transition duration-200">
                    <i class="fas fa-sync-alt mr-2"></i>Update Status
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function openStatusUpdateModal() {
        document.getElementById('statusUpdateModal').classList.remove('hidden');
    }

    function closeStatusUpdateModal() {
        document.getElementById('statusUpdateModal').classList.add('hidden');
    }

    function updateStatus() {
        if (confirm('Apakah Anda yakin ingin mengupdate status untuk {{ count($referensis) }} data yang ditampilkan?')) {
            // Create form data with current filters
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');

            // Add current filter parameters
            @if($request->date_from)
                formData.append('date_from', '{{ $request->date_from }}');
            @endif
            @if($request->date_to)
                formData.append('date_to', '{{ $request->date_to }}');
            @endif
            @if($request->no_rawat)
                formData.append('no_rawat', '{{ $request->no_rawat }}');
            @endif
            @if($request->no_booking)
                formData.append('no_booking', '{{ $request->no_booking }}');
            @endif
            @if($request->status)
                formData.append('status', '{{ $request->status }}');
            @endif

            // Collect all displayed no_booking values from the modal preview table
            const noBookingList = Array.from(document.querySelectorAll('#statusUpdateModal tbody tr')).map(row => {
                const cell = row.querySelector('td');
                return cell ? cell.textContent.trim() : null;
            }).filter(Boolean);
            formData.append('no_booking_list', JSON.stringify(noBookingList));

            // Show loading
            const updateBtn = document.querySelector('#statusUpdateModal button:last-child');
            const originalText = updateBtn.innerHTML;
            updateBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...';
            updateBtn.disabled = true;

            fetch('{{ route("referensi.pendafataran") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message with detailed results
                    let message = `Status berhasil diupdate!\n\n`;
                    message += `✓ Check-in: ${data.checkin_count} data\n`;
                    message += `✗ Dibatalkan: ${data.cancelled_count} data\n\n`;

                    if (data.updated_records && data.updated_records.length > 0) {
                        message += `Detail perubahan:\n`;
                        data.updated_records.forEach(record => {
                            message += `• ${record.no_booking} (${record.nm_pasien}): ${record.old_status} → ${record.new_status} (${record.action})\n`;
                        });
                    }

                    alert(message);
                    // location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Terjadi kesalahan'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mengupdate status');
            })
            .finally(() => {
                updateBtn.innerHTML = originalText;
                updateBtn.disabled = false;
                closeStatusUpdateModal();
            });
        }
    }
</script>

@endsection