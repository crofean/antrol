<?php

namespace App\Http\Controllers;

use App\Services\BpjsLogService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use App\Models\BpjsWsRsLog;

class BpjsLogController extends Controller
{
    protected $bpjsLogService;

    public function __construct(BpjsLogService $bpjsLogService)
    {
        $this->bpjsLogService = $bpjsLogService;
    }

    /**
     * Display BPJS logs dashboard
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $limit = $request->get('limit', 100);
        $logs = $this->bpjsLogService->getRecentLogs($limit);

        return view('bpjs-logs.index', compact('logs', 'limit'));
    }

    /**
     * Get BPJS logs as JSON
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getLogs(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 100);
        $logs = $this->bpjsLogService->getRecentLogs($limit);

        return response()->json([
            'success' => true,
            'data' => $logs
        ]);
    }

    /**
     * Get logs by date range
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getLogsByDateRange(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        $logs = $this->bpjsLogService->getLogsByDateRange(
            $request->start_date,
            $request->end_date
        );

        return response()->json([
            'success' => true,
            'data' => $logs
        ]);
    }

    /**
     * Get logs by HTTP status code
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getLogsByCode(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|integer|min:100|max:599'
        ]);

        $limit = $request->get('limit', 50);
        $logs = $this->bpjsLogService->getLogsByCode($request->code, $limit);

        return response()->json([
            'success' => true,
            'data' => $logs
        ]);
    }

    /**
     * Get logs by task ID and no_rawat (improved search)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getLogsByTask(Request $request): JsonResponse
    {
        $request->validate([
            'no_rawat' => 'required|string',
            'task_id' => 'nullable|integer|min:1|max:99'
        ]);

        $log = null;

        if ($request->task_id) {
            // Search with task ID
            $log = $this->bpjsLogService->getLogByTaskAndNoRawat($request->no_rawat, $request->task_id);
        }

        // If no task ID provided or not found with task ID, try booking code only
        if (!$log) {
            $log = $this->bpjsLogService->getLogByBookingCode($request->no_rawat);
        }

        if ($log) {
            return response()->json([
                'success' => true,
                'data' => $log
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No log found for the specified criteria'
        ]);
    }
}
