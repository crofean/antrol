<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Tracks task execution for each booking with detailed status.
 * Stores data in cache for real-time UI updates.
 */
class TaskExecutionTracker
{
    private string $jobId;
    private string $cacheKey;

    public function __construct(string $jobId)
    {
        $this->jobId = $jobId;
        $this->cacheKey = 'task-execution:' . $jobId;
        
        // Initialize if not exists
        if (!Cache::has($this->cacheKey)) {
            Cache::put($this->cacheKey, [
                'job_id' => $jobId,
                'bookings' => [],
                'summary' => [
                    'total_bookings' => 0,
                    'task_ids' => [],
                    'completed' => 0,
                    'failed' => 0,
                    'pending' => 0,
                    'started_at' => now()->toIso8601String(),
                ]
            ], 3600);
        }
    }

    /**
     * Record a task execution step for a booking.
     *
     * @param string $noRawat Booking number
     * @param int $taskId Task ID number
     * @param string $status Status (pending, processing, completed, failed, skipped)
     * @param string $response Response/error message
     * @param int|null $duration Duration in seconds
     * @return void
     */
    public function recordStep($noRawat, $taskId, $status, $response = '', $duration = null)
    {
        $data = Cache::get($this->cacheKey, [
            'bookings' => [],
            'summary' => ['total_bookings' => 0, 'completed' => 0, 'failed' => 0, 'pending' => 0]
        ]);

        // Ensure booking exists
        if (!isset($data['bookings'][$noRawat])) {
            $data['bookings'][$noRawat] = [
                'no_rawat' => $noRawat,
                'steps' => [],
                'overall_status' => 'processing',
                'created_at' => now()->toIso8601String(),
            ];
            $data['summary']['total_bookings']++;
        }

        // Record the step
        $data['bookings'][$noRawat]['steps'][$taskId] = [
            'task_id' => $taskId,
            'status' => $status,
            'response' => $response,
            'timestamp' => now()->toIso8601String(),
            'duration' => $duration,
        ];

        // Track unique task IDs
        if (!in_array($taskId, $data['summary']['task_ids'] ?? [])) {
            $data['summary']['task_ids'][] = $taskId;
            sort($data['summary']['task_ids']);
        }

        // Update overall status
        $data['bookings'][$noRawat]['overall_status'] = $this->determineOverallStatus($data['bookings'][$noRawat]['steps']);

        // Update summary counters
        $this->updateSummary($data);

        // Save to cache
        Cache::put($this->cacheKey, $data, 3600);
    }

    /**
     * Mark a booking as completed.
     *
     * @param string $noRawat
     * @return void
     */
    public function completeBooking($noRawat)
    {
        $data = Cache::get($this->cacheKey, []);
        
        if (isset($data['bookings'][$noRawat])) {
            $data['bookings'][$noRawat]['overall_status'] = 'completed';
            $data['bookings'][$noRawat]['completed_at'] = now()->toIso8601String();
            $this->updateSummary($data);
            Cache::put($this->cacheKey, $data, 3600);
        }
    }

    /**
     * Mark a booking as failed.
     *
     * @param string $noRawat
     * @param string $reason
     * @return void
     */
    public function failBooking($noRawat, $reason = '')
    {
        $data = Cache::get($this->cacheKey, []);
        
        if (isset($data['bookings'][$noRawat])) {
            $data['bookings'][$noRawat]['overall_status'] = 'failed';
            $data['bookings'][$noRawat]['failure_reason'] = $reason;
            $data['bookings'][$noRawat]['failed_at'] = now()->toIso8601String();
            $this->updateSummary($data);
            Cache::put($this->cacheKey, $data, 3600);
        }
    }

    /**
     * Get all tracking data.
     *
     * @return array
     */
    public function getData()
    {
        return Cache::get($this->cacheKey, [
            'bookings' => [],
            'summary' => ['total_bookings' => 0, 'completed' => 0, 'failed' => 0, 'pending' => 0]
        ]);
    }

    /**
     * Determine overall status based on steps.
     *
     * @param array $steps
     * @return string
     */
    private function determineOverallStatus($steps)
    {
        if (empty($steps)) {
            return 'pending';
        }

        $failed = array_filter($steps, fn($s) => $s['status'] === 'failed');
        if (!empty($failed)) {
            return 'failed';
        }

        $completed = array_filter($steps, fn($s) => $s['status'] === 'completed');
        if (count($completed) === count($steps)) {
            return 'completed';
        }

        return 'processing';
    }

    /**
     * Update summary statistics.
     *
     * @param array $data
     * @return void
     */
    private function updateSummary(&$data)
    {
        $completed = 0;
        $failed = 0;
        $pending = 0;

        foreach ($data['bookings'] as $booking) {
            match ($booking['overall_status']) {
                'completed' => $completed++,
                'failed' => $failed++,
                default => $pending++,
            };
        }

        $data['summary']['completed'] = $completed;
        $data['summary']['failed'] = $failed;
        $data['summary']['pending'] = $pending;
    }
}
