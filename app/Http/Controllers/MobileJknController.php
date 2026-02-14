<?php

namespace App\Http\Controllers;

use App\Services\MobileJknService;
use App\Services\BpjsLogService;
use App\Models\BpjsWsRsLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Contracts\View\Factory;

class MobileJknController extends Controller
{
    protected $mobileJknService;
    protected $bpjsLogService;

    public function __construct(MobileJknService $mobileJknService, BpjsLogService $bpjsLogService)
    {
        $this->mobileJknService = $mobileJknService;
        $this->bpjsLogService = $bpjsLogService;
    }

    /**
     * Update task ID for a booking
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateTaskId(Request $request): JsonResponse
    {
        $data = $request->all();
        if (!isset($data['kodebooking'], $data['taskid'])) {
            return response()->json(['success' => false, 'message' => 'Missing required fields'], 422);
        }

        try {
            $result = $this->mobileJknService->updateTaskId(
                $data['kodebooking'],
                (int)$data['taskid'],
                null
                // $data['waktu']
            );
            return response()->json([
                'success' => $result['success'],
                'message' => $result['error'] ?? $result['metadata']['message'] ?? $result['message'] ?? null,
                'response' => $result['data'] ?? $result['response'] ?? null,
                'batal' => $result['batal']['data'] ?? null
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update task ID with timestamp from database
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateTaskIdFromDatabase(Request $request): JsonResponse
    {
        $request->validate([
            'kodebooking' => 'required|string',
            'taskid' => 'required|integer|in:3,4,5,6,7'
        ]);

        $result = $this->mobileJknService->updateTaskIdFromDatabase(
            $request->kodebooking,
            $request->taskid
        );

        return response()->json($result);
    }

    /**
     * Update task ID with current timestamp
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateTaskIdNow(Request $request): JsonResponse
    {
        $request->validate([
            'kodebooking' => 'required|string',
            'taskid' => 'required|integer|in:1,2,3,4,5,6,7,99'
        ]);

        $result = $this->mobileJknService->updateTaskIdNow(
            $request->kodebooking,
            $request->taskid
        );

        return response()->json($result);
    }

    /**
     * Batch update multiple task IDs
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function batchUpdateTaskIds(Request $request): JsonResponse
    {
        $request->validate([
            'updates' => 'required|array',
            'updates.*.kodebooking' => 'required|string',
            'updates.*.taskid' => 'required|integer|in:1,2,3,4,5,6,7,99',
            'updates.*.waktu' => 'nullable|string'
        ]);

        $result = $this->mobileJknService->batchUpdateTaskIds($request->updates);

        return response()->json($result);
    }

    /**
     * Display task ID logs view
     *
     * @return View
     */
    public function taskIdLogs(): View
    {
        // Get recent logs for task ID updates
        $logs = $this->bpjsLogService->getTaskIdLogs();
        
        // Get task ID stats
        $successCount = BpjsWsRsLog::where('url', 'like', '%/antrean/updatewaktu%')
            ->where('code', '>=', 200)
            ->where('code', '<', 300)
            ->count();
            
        $errorCount = BpjsWsRsLog::where('url', 'like', '%/antrean/updatewaktu%')
            ->where('code', '>=', 400)
            ->count();
            
        $totalCount = BpjsWsRsLog::where('url', 'like', '%/antrean/updatewaktu%')->count();

        // Get antrean add stats
        $antreanSuccessCount = BpjsWsRsLog::where('url', 'like', '%/antrean/add%')
            ->where('code', '>=', 200)
            ->where('code', '<', 300)
            ->count();
            
        $antreanErrorCount = BpjsWsRsLog::where('url', 'like', '%/antrean/add%')
            ->where('code', '>=', 400)
            ->count();
            
        $antreanTotalCount = BpjsWsRsLog::where('url', 'like', '%/antrean/add%')->count();
        
        return view('mobilejkn.taskid-logs', compact(
            'logs', 
            'successCount', 
            'errorCount', 
            'totalCount',
            'antreanSuccessCount',
            'antreanErrorCount',
            'antreanTotalCount'
        ));
    }

    /**
     * Get task ID logs API endpoint
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getTaskIdLogs(Request $request): JsonResponse
    {
        $request->validate([
            'perPage' => 'nullable|integer|min:10|max:100',
            'page' => 'nullable|integer|min:1',
        ]);

        $perPage = $request->perPage ?? 25;
        $page = $request->page ?? 1;

        $logs = $this->bpjsLogService->getTaskIdLogs($perPage, $page);

        return response()->json($logs);
    }

    /**
     * Get filtered task ID logs API endpoint
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getFilteredTaskIdLogs(Request $request): JsonResponse
    {
        $request->validate([
            'startDate' => 'required|date_format:Y-m-d',
            'endDate' => 'required|date_format:Y-m-d',
            'perPage' => 'nullable|integer|min:10|max:100',
            'page' => 'nullable|integer|min:1',
        ]);

        $perPage = $request->perPage ?? 25;
        $page = $request->page ?? 1;

        $logs = $this->bpjsLogService->filterTaskIdLogs(
            $request->startDate . ' 00:00:00',
            $request->endDate . ' 23:59:59',
            $perPage,
            $page
        );

        return response()->json($logs);
    }
    
    /**
     * Get antrean add logs API endpoint
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAntreanAddLogs(Request $request): JsonResponse
    {
        $request->validate([
            'perPage' => 'nullable|integer|min:10|max:100',
            'page' => 'nullable|integer|min:1',
        ]);

        $perPage = $request->perPage ?? 25;
        $page = $request->page ?? 1;

        $logs = $this->bpjsLogService->getAntreanAddLogs($perPage, $page);

        return response()->json($logs);
    }
    
    /**
     * Get patient data needed for task ID updates
     *
     * @param string $regNo
     * @return JsonResponse
     */
    public function getPatientData(string $regNo): JsonResponse
    {
        $data = $this->mobileJknService->getPatientDataForTaskId($regNo);
        
        if (!$data) {
            return response()->json([
                'status' => false,
                'message' => 'Data not found',
                'data' => null
            ], 404);
        }
        
        return response()->json([
            'status' => true,
            'message' => 'Data retrieved successfully',
            'data' => $data
        ]);
    }
    
    /**
     * Display the patient data view
     *
     * @return View|Factory
     */
    public function showPatientDataForm()
    {
        return view('mobilejkn.patient-data');
    }
    
    /**
     * Send antrean by no_rawat
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendAntrian(Request $request): JsonResponse
    {
        $noRawat = $request->input('no_rawat');
        if (!$noRawat) {
            return response()->json(['status' => false, 'message' => 'no_rawat is required', 'data' => [], 'payload' => null]);
        }

        try {
            $service = app(MobileJknService::class);
            $result = $service->sendAddAntreanByNoRawat($noRawat);
            return response()->json($result);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => [], 'payload' => null], 500);
        }
    }

    /**
     * Display referensi pendafataran MJKN page
     *
     * @param Request $request
     * @return View
     */
    public function referensiPendafataran(Request $request): View
    {
        $query = \App\Models\ReferensiMobilejknBpjs::with(['regPeriksa.pasien', 'referensiMobilejknBpjsTaskid']);

        // Apply filters
        if ($request->filled('date_from') || $request->filled('date_to')) {
            if ($request->filled('date_from') && $request->filled('date_to')) {
                $query->whereBetween('tanggalperiksa', [$request->date_from, $request->date_to]);
            } elseif ($request->filled('date_from')) {
                $query->whereDate('tanggalperiksa', '>=', $request->date_from);
            } elseif ($request->filled('date_to')) {
                $query->whereDate('tanggalperiksa', '<=', $request->date_to);
            }
        }

        if ($request->filled('no_rawat')) {
            $query->where('no_rawat', 'like', '%' . $request->no_rawat . '%');
        }

        if ($request->filled('status')) {
            if ($request->status === 'belum') {
                $query->whereNull('status')->orWhere('status', '');
            } else {
                $query->where('status', $request->status);
            }
        }

        $referensis = $query->orderBy('tanggalperiksa', 'desc')
            ->paginate(20)
            ->appends($request->query());

        // Calculate statistics
        $totalReferensi = \App\Models\ReferensiMobilejknBpjs::count();
        $todayReferensi = \App\Models\ReferensiMobilejknBpjs::whereDate('tanggalperiksa', today())->count();

        // Calculate filtered statistics if filters are applied
        $filteredCount = null;
        if ($request->hasAny(['date_from', 'date_to', 'no_rawat', 'no_booking'])) {
            $filteredQuery = \App\Models\ReferensiMobilejknBpjs::query();

            if ($request->filled('date_from') || $request->filled('date_to')) {
                if ($request->filled('date_from') && $request->filled('date_to')) {
                    $filteredQuery->whereBetween('tanggalperiksa', [$request->date_from, $request->date_to]);
                } elseif ($request->filled('date_from')) {
                    $filteredQuery->whereDate('tanggalperiksa', '>=', $request->date_from);
                } elseif ($request->filled('date_to')) {
                    $filteredQuery->whereDate('tanggalperiksa', '<=', $request->date_to);
                }
            }

            if ($request->filled('no_rawat')) {
                $filteredQuery->where('no_rawat', 'like', '%' . $request->no_rawat . '%');
            }

            if ($request->filled('no_booking')) {
                $filteredQuery->where('nobooking', 'like', '%' . $request->no_booking . '%');
            }

            if ($request->filled('status')) {
                if ($request->status === 'belum') {
                    $filteredQuery->whereNull('status')->orWhere('status', '');
                } else {
                    $filteredQuery->where('status', $request->status);
                }
            }
        }

        return view('mobilejkn.referensi-pendafataran', compact('referensis', 'totalReferensi', 'todayReferensi', 'filteredCount', 'request'));
    }

    /**
     * Update status for filtered referensi records
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateReferensiStatus(Request $request): JsonResponse
    {
        try {
            // Get the same query as the GET method to get the current displayed records
            $query = \App\Models\ReferensiMobilejknBpjs::with(['regPeriksa.pasien']);

            // Apply the same filters as the GET method
            if ($request->filled('date_from') || $request->filled('date_to')) {
                if ($request->filled('date_from') && $request->filled('date_to')) {
                    $query->whereBetween('tanggalperiksa', [$request->date_from, $request->date_to]);
                } elseif ($request->filled('date_from')) {
                    $query->whereDate('tanggalperiksa', '>=', $request->date_from);
                } elseif ($request->filled('date_to')) {
                    $query->whereDate('tanggalperiksa', '<=', $request->date_to);
                }
            }

            if ($request->filled('no_rawat')) {
                $query->where('no_rawat', 'like', '%' . $request->no_rawat . '%');
            }

            if ($request->filled('no_booking')) {
                $query->where('nobooking', 'like', '%' . $request->no_booking . '%');
            }

            if ($request->filled('status')) {
                if ($request->status === 'belum') {
                    $query->whereNull('status')->orWhere('status', '');
                } else {
                    $query->where('status', $request->status);
                }
            }

            // Get all records that match the current filters (not paginated)
            $referensis = $query->get();

            $updatedCount = 0;
            $cancelledCount = 0;
            $checkinCount = 0;
            $errors = [];
            $updatedRecords = [];

            foreach ($referensis as $referensi) {
                try {
                    $shouldCancel = false;
                    $shouldCheckin = false;
                    $action = '';

                    // Check reg periksa status
                    if ($referensi->regPeriksa) {
                        $regStatus = strtolower($referensi->regPeriksa->status_lanjut ?? '');
                        if ($regStatus === 'sudah') {
                            $shouldCheckin = true;
                            $action = 'Check-in';
                        } elseif (in_array($regStatus, ['batal', 'belum'])) {
                            $shouldCancel = true;
                            $action = 'Batal';
                        }
                    } else {
                        // If no reg periksa found, treat as belum/batal
                        $shouldCancel = true;
                        $action = 'Batal (no Reg Periksa)';
                    }

                    // Update status and timestamp
                    $oldStatus = $referensi->status;
                    $newStatus = $shouldCancel ? '0' : '1';

                    $referensi->update([
                        'status' => $newStatus,
                        'validasi' => now(),
                    ]);

                    if ($shouldCancel) {
                        $cancelledCount++;
                    } elseif ($shouldCheckin) {
                        $checkinCount++;
                    }

                    $updatedCount++;

                    // Track updated records
                    $updatedRecords[] = [
                        'nobooking' => $referensi->nobooking,
                        'no_rawat' => $referensi->no_rawat,
                        'pasien' => $referensi->regPeriksa->pasien->nm_pasien ?? '-',
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                        'action' => $action,
                        'reg_status' => $referensi->regPeriksa->status_lanjut ?? 'N/A'
                    ];

                } catch (\Exception $e) {
                    $errors[] = "Error updating {$referensi->nobooking}: " . $e->getMessage();
                }
            }

            $message = "Status berhasil diupdate untuk {$updatedCount} data.";
            if ($checkinCount > 0) {
                $message .= "\n{$checkinCount} data di-checkin (Reg Periksa status 'sudah').";
            }
            if ($cancelledCount > 0) {
                $message .= "\n{$cancelledCount} data dibatalkan (Reg Periksa status 'batal'/'belum').";
            }
            if (!empty($errors)) {
                $message .= "\n\nError:\n" . implode("\n", $errors);
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'updated' => $updatedCount,
                'checkin' => $checkinCount,
                'cancelled' => $cancelledCount,
                'errors' => $errors,
                'updated_records' => $updatedRecords
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
