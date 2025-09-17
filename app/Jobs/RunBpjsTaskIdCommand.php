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

        // Run the command
        try {
            // Use the buffer approach to capture output
            $outputBuffer = new BufferedOutput;
            $exitCode = Artisan::call('bpjs:send-task-ids', $commandOptions, $outputBuffer);
            $output = $outputBuffer->fetch();
            
            // Log the output for debugging
            Log::info('BPJS Task Command Output', [
                'job_id' => $this->jobId,
                'output_length' => strlen($output),
                'exit_code' => $exitCode
            ]);
            
            // Store output in cache
            $currentOutput = Cache::get('command-output:' . $this->jobId);
            $currentOutput['output'][] = $output;
            Cache::put('command-output:' . $this->jobId, $currentOutput, 3600);
            
            $currentOutput['status'] = 'completed';
            $currentOutput['completed_at'] = now()->toIso8601String();
            $currentOutput['exit_code'] = $exitCode;
            Cache::put('command-output:' . $this->jobId, $currentOutput, 3600);
        } catch (\Exception $e) {
            // Log the error with full stack trace
            Log::error('Error running BPJS task command', [
                'job_id' => $this->jobId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $currentOutput = Cache::get('command-output:' . $this->jobId);
            $currentOutput['status'] = 'failed';
            $currentOutput['error'] = $e->getMessage();
            $currentOutput['error_trace'] = $e->getTraceAsString();
            $currentOutput['completed_at'] = now()->toIso8601String();
            Cache::put('command-output:' . $this->jobId, $currentOutput, 3600);
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
