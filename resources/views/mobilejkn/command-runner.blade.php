@extends('mobilejkn.layout')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-6">Run BPJS Task ID Command</h1>
    
    <div id="queueHelperAlert" class="bg-yellow-100 border-l-4 border-yellow-500 p-4 mb-6 hidden">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-yellow-700">
                    <strong>Queue workers might not be running.</strong> If your command stays in "pending" state, you may need to start queue workers.
                </p>
                <p class="text-sm text-yellow-700 mt-2">
                    Run this command in your terminal: <code class="bg-gray-100 px-2 py-1 rounded">php artisan queue:work</code>
                </p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <form id="commandForm" class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                    <input type="date" id="date_from" name="date_from" 
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                    <input type="date" id="date_to" name="date_to" 
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            
            <div class="mt-4">
                <div class="flex items-center">
                    <input type="checkbox" id="dry_run" name="dry_run" class="h-4 w-4 text-blue-600 focus:ring-blue-500">
                    <label for="dry_run" class="ml-2 block text-sm text-gray-700">Dry Run Mode (✓ = simulate only, ✗ = make real API calls)</label>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
                    Run Command
                </button>
            </div>
        </form>
        
        <div id="statusArea" class="hidden bg-gray-100 p-4 rounded-md mb-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div id="statusIndicator" class="w-4 h-4 rounded-full bg-yellow-500 mr-2"></div>
                    <span id="statusText" class="text-sm font-medium">Running...</span>
                </div>
                <button id="stopButton" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 text-sm rounded-md">
                    Stop Command
                </button>
            </div>
        </div>
        
        <div id="outputContainer" class="hidden">
            <h3 class="text-lg font-medium mb-2">Command Output</h3>
            <div id="outputArea" class="bg-gray-900 text-gray-100 p-4 rounded-md font-mono text-sm h-96 overflow-y-auto whitespace-pre"></div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let jobId = null;
    let outputInterval = null;
    
    // Add stop button event listener
    document.getElementById('stopButton').addEventListener('click', function() {
        if (!jobId) return;
        
        // Show stopping message
        document.getElementById('outputArea').textContent += '\n\nStopping command...\n';
        document.getElementById('statusText').textContent = 'Stopping...';
        document.getElementById('statusIndicator').className = 'w-4 h-4 rounded-full bg-orange-500 mr-2';
        
        // Send request to stop the command
        fetch(`{{ route('command.stop') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                job_id: jobId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('outputArea').textContent += 'Command stopped successfully.\n';
                updateStatus('stopped');
                clearInterval(outputInterval);
            } else {
                document.getElementById('outputArea').textContent += `Failed to stop command: ${data.message}\n`;
            }
        })
        .catch(error => {
            document.getElementById('outputArea').textContent += `Error stopping command: ${error.message}\n`;
        });
    });
    
    document.getElementById('commandForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Get form values
        const dateFrom = document.getElementById('date_from').value;
        const dateTo = document.getElementById('date_to').value;
        const dryRun = document.getElementById('dry_run').checked;
        
        // Show status area
        document.getElementById('statusArea').classList.remove('hidden');
        document.getElementById('outputContainer').classList.remove('hidden');
        document.getElementById('outputArea').textContent = 'Starting command...\n';
        
        // Send request to run command
        fetch('{{ route("command.run") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                date_from: dateFrom,
                date_to: dateTo,
                dry_run: dryRun
            })
        })
        .then(response => response.json())
        .then(data => {
            jobId = data.job_id;
            
            // Start polling for output
            outputInterval = setInterval(fetchOutput, 1000);
            
            // Show queue information if available
            if (data.queue_info && data.queue_info.message) {
                document.getElementById('outputArea').textContent += 'Queue information: ' + data.queue_info.message + '\n';
            }
        })
        .catch(error => {
            document.getElementById('outputArea').textContent += 'Error: ' + error.message + '\n';
            updateStatus('failed');
        });
    });
    
    function fetchOutput() {
        if (!jobId) return;
        
        console.log('Fetching output for job:', jobId);
        
        fetch(`{{ route('command.output', ['jobId' => ':jobId']) }}`.replace(':jobId', jobId))
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Network response error: ${response.status} ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Received data:', data);
                
                // Update output area with all lines
                const outputArea = document.getElementById('outputArea');
                outputArea.textContent = '';
                
                if (data.output && data.output.length > 0) {
                    data.output.forEach(line => {
                        outputArea.textContent += line;
                    });
                }
                
                // Scroll to bottom
                outputArea.scrollTop = outputArea.scrollHeight;
                
                // Update status based on job status
                if (data.status === 'running') {
                    updateStatus('running');
                } else if (data.status === 'completed') {
                    updateStatus('completed');
                    clearInterval(outputInterval);
                    
                    // Add exit code info
                    if (data.exit_code !== undefined) {
                        outputArea.textContent += `\n\nCommand completed with exit code: ${data.exit_code}`;
                    }
                } else if (data.status === 'failed') {
                    updateStatus('failed');
                    outputArea.textContent += '\nError: ' + (data.error || 'Unknown error');
                    clearInterval(outputInterval);
                } else if (data.status === 'stopped') {
                    updateStatus('stopped');
                    clearInterval(outputInterval);
                } else if (data.status === 'pending' || data.status === 'initializing') {
                    // Still in pending state, show this in the UI
                    document.getElementById('statusText').textContent = 'Pending...';
                    
                    // Check if we have queue status information
                    if (data.queue_status && data.queue_status.queue_status === 'no_workers') {
                        // Show the queue helper alert
                        document.getElementById('queueHelperAlert').classList.remove('hidden');
                    }
                }
            })
            .catch(error => {
                console.error('Error fetching output:', error);
                document.getElementById('outputArea').textContent += `\nError fetching output: ${error.message}\n`;
                // Don't stop polling on temporary errors
            });
    }
    
    function updateStatus(status) {
        const indicator = document.getElementById('statusIndicator');
        const text = document.getElementById('statusText');
        
        if (status === 'running') {
            indicator.className = 'w-4 h-4 rounded-full bg-yellow-500 mr-2';
            text.textContent = 'Running...';
        } else if (status === 'completed') {
            indicator.className = 'w-4 h-4 rounded-full bg-green-500 mr-2';
            text.textContent = 'Completed';
        } else if (status === 'failed') {
            indicator.className = 'w-4 h-4 rounded-full bg-red-500 mr-2';
            text.textContent = 'Failed';
        } else if (status === 'stopped') {
            indicator.className = 'w-4 h-4 rounded-full bg-orange-500 mr-2';
            text.textContent = 'Stopped';
        }
    }
</script>
@endpush
@endsection
