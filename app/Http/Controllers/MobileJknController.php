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
                'response' => $result['data'] ?? $result['response'] ?? null
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
     * Add a new antrean (appointment) for a patient
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function addAntrean(Request $request): JsonResponse
    {
        $request->validate([
            'payload' => 'required|array',
        ]);

        $payload = $request->payload;
        
        // Store registration number in the payload if provided
        if ($request->has('no_rawat')) {
            $payload['no_rawat'] = $request->no_rawat;
        }

        // Call the service method to add antrean
        $result = $this->mobileJknService->addAntrean($payload);

        return response()->json($result);
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
}
