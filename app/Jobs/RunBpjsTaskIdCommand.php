<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Output\BufferedOutput;

class RunBpjsTaskIdCommand implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $options;
    protected $jobId;

    /**
     * Create a new job instance.
     */
    public function __construct(array $options = [], $jobId = null)
    {
        $this->options = $options;
        $this->jobId = $jobId ?? 'bpjs-task-' . uniqid();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Get existing cache entry or create a new one
        $cacheEntry = Cache::get('command-output:' . $this->jobId, [
            'output' => []
        ]);
        
        // Update the status to running and maintain any existing output
        $cacheEntry['status'] = 'running';
        $cacheEntry['started_at'] = now()->toIso8601String();
        
        // Add a message that the job is now running
        $cacheEntry['output'][] = "Command started at " . now()->format('Y-m-d H:i:s') . "\n";
        
        // Update the cache
        Cache::put('command-output:' . $this->jobId, $cacheEntry, 3600);

        // Build command options
        $commandOptions = [];
        
        if (!empty($this->options['date-from'])) {
            $commandOptions['--date-from'] = $this->options['date-from'];
        }
        
        if (!empty($this->options['date-to'])) {
            $commandOptions['--date-to'] = $this->options['date-to'];
        }
        
        if (!empty($this->options['dry-run'])) {
            $commandOptions['--dry-run'] = true;
        }

        // Run the command with retry logic
        $maxAttempts = (int) env('BPJS_TASK_RETRY_MAX', 5);
        $retryInterval = (int) env('BPJS_TASK_RETRY_INTERVAL', 10); // seconds
        $attempt = 0;
        $success = false;
        $combinedOutput = '';

        while (!$success && $attempt < $maxAttempts) {
            $attempt++;
            try {
                $outputBuffer = new BufferedOutput;
                $exitCode = Artisan::call('bpjs:send-task-ids', $commandOptions, $outputBuffer);
                $output = $outputBuffer->fetch();
                $combinedOutput .= "\n--- Attempt {$attempt} ---\n" . $output;

                Log::info('BPJS Task Command Attempt', [
                    'job_id' => $this->jobId,
                    'attempt' => $attempt,
                    'exit_code' => $exitCode,
                    'output_length' => strlen($output)
                ]);

                // Store incremental output in cache
                $currentOutput = Cache::get('command-output:' . $this->jobId, ['output' => []]);
                $currentOutput['output'][] = "Attempt {$attempt}: " . $output;
                $currentOutput['attempts'] = $attempt;
                $currentOutput['status'] = $exitCode === 0 ? 'completed' : 'retrying';
                Cache::put('command-output:' . $this->jobId, $currentOutput, 3600);

                if ($exitCode === 0) {
                    $success = true;
                    $currentOutput['status'] = 'completed';
                    $currentOutput['completed_at'] = now()->toIso8601String();
                    $currentOutput['exit_code'] = $exitCode;
                    Cache::put('command-output:' . $this->jobId, $currentOutput, 3600);
                    break;
                }

                // If not successful and we have more attempts, wait then retry
                if ($attempt < $maxAttempts) {
                    sleep($retryInterval);
                }
            } catch (\Exception $e) {
                Log::error('Error running BPJS task command (attempt '.$attempt.')', [
                    'job_id' => $this->jobId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                $currentOutput = Cache::get('command-output:' . $this->jobId, ['output' => []]);
                $currentOutput['output'][] = "Attempt {$attempt} Exception: " . $e->getMessage();
                $currentOutput['attempts'] = $attempt;
                $currentOutput['status'] = 'retrying';
                Cache::put('command-output:' . $this->jobId, $currentOutput, 3600);

                if ($attempt < $maxAttempts) {
                    sleep($retryInterval);
                }
            }
        }

        if (!$success) {
            $finalOutput = Cache::get('command-output:' . $this->jobId, ['output' => []]);
            $finalOutput['status'] = 'failed';
            $finalOutput['completed_at'] = now()->toIso8601String();
            $finalOutput['exit_code'] = $exitCode ?? 1;
            Cache::put('command-output:' . $this->jobId, $finalOutput, 3600);

            Log::error('BPJS Task Command failed after retries', ['job_id' => $this->jobId, 'attempts' => $attempt]);
        }
    }

    /**
     * Get the job ID.
     */
    public function getJobId(): string
    {
        return $this->jobId;
    }
}
