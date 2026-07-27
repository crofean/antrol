<?php

namespace App\Http\Controllers;

use App\Services\BpjsLogService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use App\Http\Requests\DateRangeRequest;
use App\Http\Requests\GetLogsByCodeRequest;
use App\Http\Requests\GetLogsByTaskRequest;
use App\Http\Resources\BpjsLogResource;
use App\Http\Resources\ApiSuccessResource;

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
     * @return ApiSuccessResource
     */
    public function getLogs(Request $request): ApiSuccessResource
    {
        $limit = $request->get('limit', 100);
        $logs = $this->bpjsLogService->getRecentLogs($limit);

        return new ApiSuccessResource(BpjsLogResource::collection($logs));
    }

    /**
     * Get logs by date range
     *
     * @param DateRangeRequest $request
     * @return ApiSuccessResource
     */
    public function getLogsByDateRange(DateRangeRequest $request): ApiSuccessResource
    {
        $logs = $this->bpjsLogService->getLogsByDateRange(
            $request->start_date,
            $request->end_date
        );

        return new ApiSuccessResource(BpjsLogResource::collection($logs));
    }

    /**
     * Get logs by HTTP status code
     *
     * @param GetLogsByCodeRequest $request
     * @return ApiSuccessResource
     */
    public function getLogsByCode(GetLogsByCodeRequest $request): ApiSuccessResource
    {
        $limit = $request->get('limit', 50);
        $logs = $this->bpjsLogService->getLogsByCode($request->code, $limit);

        return new ApiSuccessResource(BpjsLogResource::collection($logs));
    }

    /**
     * Get logs by task ID and no_rawat (improved search)
     *
     * @param GetLogsByTaskRequest $request
     * @return ApiSuccessResource|JsonResponse
     */
    public function getLogsByTask(GetLogsByTaskRequest $request): ApiSuccessResource|JsonResponse
    {
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
            return new ApiSuccessResource(new BpjsLogResource($log));
        }

        return response()->json([
            'success' => false,
            'message' => 'No log found for the specified criteria'
        ]);
    }
}
