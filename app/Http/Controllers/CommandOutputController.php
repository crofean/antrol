<?php

namespace App\Http\Controllers;

use App\Jobs\RunBpjsTaskIdCommand;
use App\Models\ReferensiMobilejknBpjsTaskid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Log;

class CommandOutputController extends Controller
{
    /**
     * Show the form to run the BPJS task ID command.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('mobilejkn.command-runner');
    }

    /**
     * Run the BPJS task ID command.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function runCommand(Request $request)
    {
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'dry_run' => 'nullable|boolean',
        ]);

        // Create options array for the command
        $options = [
            'date-from' => $request->date_from,
            'date-to' => $request->date_to,
            'dry-run' => $request->has('dry_run') ? $request->dry_run : false,
        ];

        // Create a unique job ID
        $jobId = 'bpjs-task-' . uniqid();
        
        // Initialize the cache entry for immediate access
        Cache::put('command-output:' . $jobId, [
            'status' => 'pending',
            'output' => ['Job initialized, waiting to start...'],
            'started_at' => now()->toIso8601String(),
        ], 3600);

        // Create the job
        $job = new RunBpjsTaskIdCommand($options, $jobId);
        
        // Dispatch the job with explicit queue information
        try {
            dispatch($job)->onQueue('default');
            
            // Log the job dispatch
            \Illuminate\Support\Facades\Log::info('BPJS Task Command dispatched', [
                'job_id' => $jobId,
                'options' => $options
            ]);
            
            return response()->json([
                'status' => 'started',
                'job_id' => $jobId,
                'queue_info' => [
                    'message' => 'Job dispatched to queue. If it stays in "pending" state, ensure queue workers are running with: php artisan queue:work',
                ]
            ]);
        } catch (\Exception $e) {
            // If there was an error dispatching the job
            $errorOutput = Cache::get('command-output:' . $jobId);
            $errorOutput['status'] = 'failed';
            $errorOutput['error'] = 'Failed to dispatch job: ' . $e->getMessage();
            $errorOutput['output'][] = 'Error: ' . $e->getMessage();
            Cache::put('command-output:' . $jobId, $errorOutput, 3600);
            
            return response()->json([
                'status' => 'error',
                'job_id' => $jobId,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the command output for a specific job ID.
     *
     * @param  string  $jobId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOutput($jobId)
    {
        $output = Cache::get('command-output:' . $jobId);

        if (!$output) {
            // Initialize a default response if cache entry doesn't exist
            $output = [
                'status' => 'initializing',
                'output' => ['Waiting for job to start...'],
                'started_at' => now()->toIso8601String(),
            ];
            
            // Store it for future requests
            Cache::put('command-output:' . $jobId, $output, 3600);
        }
        
        // If the job has been pending for more than 30 seconds, check queue status
        if ($output['status'] === 'pending' || $output['status'] === 'initializing') {
            $startedAt = \Carbon\Carbon::parse($output['started_at']);
            $now = \Carbon\Carbon::now();
            
            if ($now->diffInSeconds($startedAt) > 30) {
                // Check if queue workers are running
                $queueStatus = $this->checkQueueStatus($jobId);
                
                // Add queue status to the output
                $output['queue_status'] = $queueStatus;
                
                // Add warning about queue workers if needed
                if (isset($queueStatus['suggestion'])) {
                    $output['output'][] = "\n⚠️ " . $queueStatus['message'] . "\n";
                    $output['output'][] = "💡 " . $queueStatus['suggestion'] . "\n";
                }
            }
        }

        return response()->json($output);
    }

    /**
     * Get task IDs being sent in the current batch.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTaskIds(Request $request)
    {
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ]);

        $dateFrom = $request->date_from ? \Carbon\Carbon::parse($request->date_from)->startOfDay() : now()->startOfDay();
        $dateTo = $request->date_to ? \Carbon\Carbon::parse($request->date_to)->endOfDay() : now()->endOfDay();

        // Ensure task IDs 1-5 exist with appropriate timing
        $this->ensureTaskIds($dateFrom, $dateTo);

        // Get all task IDs for the date range
        $taskIds = ReferensiMobilejknBpjsTaskid::whereBetween('waktu', [$dateFrom, $dateTo])
            ->orderBy('taskid')
            ->distinct()
            ->pluck('taskid')
            ->values();

        return response()->json([
            'task_ids' => $taskIds,
            'date_range' => [
                'from' => $dateFrom->toDateString(),
                'to' => $dateTo->toDateString()
            ]
        ]);
    }

    /**
     * Ensure that task IDs 1-5 exist for the given date range.
     * Task ID 5 is automatically created based on Task ID 4 + 10-15 minutes.
     *
     * @param  \Carbon\Carbon  $dateFrom
     * @param  \Carbon\Carbon  $dateTo
     * @return void
     */
    private function ensureTaskIds($dateFrom, $dateTo)
    {
        // Get all unique raw records for the date range
        $records = ReferensiMobilejknBpjsTaskid::whereBetween('waktu', [$dateFrom, $dateTo])
            ->get();

        // Group by no_rawat to process each record
        $groupedByNoRawat = $records->groupBy('no_rawat');

        foreach ($groupedByNoRawat as $noRawat => $taskIdRecords) {
            $taskIds = $taskIdRecords->pluck('taskid')->unique()->values();

            // Check if task ID 4 exists
            $hasTaskId4 = $taskIds->contains(4);
            $hasTaskId5 = $taskIds->contains(5);

            if ($hasTaskId4 && !$hasTaskId5) {
                // Get task ID 4 record to get its timing
                $taskId4Record = $taskIdRecords->firstWhere('taskid', 4);
                
                if ($taskId4Record) {
                    $taskId4Time = \Carbon\Carbon::parse($taskId4Record->waktu);
                    
                    // Create task ID 5 with time 10-15 minutes after task ID 4
                    $taskId5Time = $taskId4Time->addMinutes(rand(10, 15));

                    // Check if this specific task ID 5 doesn't exist
                    $existingTask5 = ReferensiMobilejknBpjsTaskid::where('no_rawat', $noRawat)
                        ->where('taskid', 5)
                        ->first();

                    if (!$existingTask5) {
                        ReferensiMobilejknBpjsTaskid::create([
                            'no_rawat' => $noRawat,
                            'taskid' => 5,
                            'waktu' => $taskId5Time,
                        ]);

                        Log::info('Task ID 5 created automatically', [
                            'no_rawat' => $noRawat,
                            'task_id_4_time' => $taskId4Record->waktu,
                            'task_id_5_time' => $taskId5Time,
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Stop a running command.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function stopCommand(Request $request)
    {
        $request->validate([
            'job_id' => 'required|string'
        ]);

        $jobId = $request->job_id;
        $cacheKey = 'command-output:' . $jobId;
        
        // Check if the job exists
        $output = Cache::get($cacheKey);
        if (!$output) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found'
            ], 404);
        }
        
        // Update the cache entry to indicate the command was stopped
        $output['status'] = 'stopped';
        $output['stopped_at'] = now()->toIso8601String();
        $output['output'][] = "\n\n[Command manually stopped by user]";
        Cache::put($cacheKey, $output, 3600);
        
        // Try to find and terminate the job
        // Note: This is a basic implementation, actual job termination might require
        // queue worker configuration or direct process termination
        try {
            // Log the stop request
            Log::info('Command stop requested', [
                'job_id' => $jobId,
                'user_id' => auth()->id() ?? 'unauthenticated',
                'timestamp' => now()->toIso8601String()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Command marked as stopped'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error stopping command: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Debug method to check cache entries.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function debugCache($jobId = null)
    {
        if ($jobId) {
            $output = Cache::get('command-output:' . $jobId);
            return response()->json([
                'job_id' => $jobId,
                'cache_entry' => $output
            ]);
        }
        
        // We can't easily list all cache keys in Laravel
        // Just indicate that the cache debug function was called
        $keys = [
            'debug_accessed_at' => now()->toIso8601String(),
            'message' => 'Individual cache keys cannot be listed, please provide a specific job ID'
        ];
        
        return response()->json([
            'cache_keys' => $keys
        ]);
    }

    /**
     * Check if a job is actually running or still in the queue.
     * This helps identify if there might be an issue with queue workers.
     *
     * @param  string  $jobId
     * @return array
     */
    private function checkQueueStatus($jobId)
    {
        $result = [
            'queue_status' => 'unknown',
            'message' => 'Queue status could not be determined'
        ];

        try {
            // Check if the Laravel queue worker is running
            // This command varies depending on your queue configuration
            $queueProcessCount = 0;
            
            if (function_exists('exec')) {
                exec('ps aux | grep "queue:work\|queue:listen" | grep -v grep | wc -l', $output);
                if (!empty($output[0])) {
                    $queueProcessCount = (int)$output[0];
                }
            }
            
            $result['queue_workers_running'] = $queueProcessCount > 0;
            
            if ($queueProcessCount === 0) {
                $result['queue_status'] = 'no_workers';
                $result['message'] = 'No queue workers appear to be running. Jobs may not be processed.';
                $result['suggestion'] = 'Run "php artisan queue:work" in your terminal to start processing jobs.';
            } else {
                $result['queue_status'] = 'workers_running';
                $result['message'] = 'Queue workers are running. If jobs are stuck in "pending", check for errors in the worker output.';
            }
        } catch (\Exception $e) {
            $result['error'] = $e->getMessage();
        }

        return $result;
    }
}
