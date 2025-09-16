<?php

namespace App\Http\Controllers;

use App\Services\MobileJknService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MobileJknController extends Controller
{
    protected $mobileJknService;

    public function __construct(MobileJknService $mobileJknService)
    {
        $this->mobileJknService = $mobileJknService;
    }

    /**
     * Update task ID for a booking
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateTaskId(Request $request): JsonResponse
    {
        $request->validate([
            'kodebooking' => 'required|string',
            'taskid' => 'required|integer|in:1,2,3,4,5,6,7,99',
            'waktu' => 'nullable|string'
        ]);

        $result = $this->mobileJknService->updateTaskId(
            $request->kodebooking,
            $request->taskid,
            $request->waktu
        );

        return response()->json($result);
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
}
