<?php

namespace App\Console\Commands;

use App\Models\ReferensiMobilejknBpjs;
use App\Models\RegPeriksa;
use App\Models\Poliklinik;
use App\Models\Dokter;
use App\Services\MobileJknService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SendBpjsTaskIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bpjs:send-task-ids
                            {--date-from= : Start date (Y-m-d)}
                            {--date-to= : End date (Y-m-d)}
                            {--dry-run : Show what would be processed without actually sending}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send BPJS task IDs for patients based on registration data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting BPJS Task ID Sender...');
        $this->newLine();

        // Get configuration from environment
        $kdPj = config('services.mobilejkn.kd_pj', 'BPJ');
        $excludePoli = config('services.mobilejkn.exclude_poli', '');
        $excludePoliArray = array_filter(explode(',', $excludePoli));

        // Get date range
        $dateFrom = $this->option('date-from') ?: Carbon::today()->format('Y-m-d');
        $dateTo = $this->option('date-to') ?: Carbon::today()->format('Y-m-d');

        $this->info("Processing patients from {$dateFrom} to {$dateTo}");
        $this->info("BPJS Payer Code: {$kdPj}");
        if (!empty($excludePoliArray)) {
            $this->info("Excluding Poli: " . implode(', ', $excludePoliArray));
        }
        $this->newLine();

        // Build query for patients
        $query = RegPeriksa::with([
            'referensiMobilejknBpjs',
            'poliklinik',
            'dokter',
            'pasien'
        ])
        ->where('kd_pj', $kdPj)
        ->whereBetween('tgl_registrasi', [$dateFrom, $dateTo]);
        // ->whereHas('referensiMobilejknBpjs', function($q) {
        //     $q->whereNotNull('nobooking');
        // });

        // Exclude specific poli if configured
        if (!empty($excludePoliArray)) {
            $query->whereNotIn('kd_poli', $excludePoliArray);
        }

        $patients = $query->get();

        $this->info("Found {$patients->count()} patients to process");
        $this->newLine();

        if ($patients->isEmpty()) {
            $this->warn('No patients found matching the criteria.');
            return;
        }

        // Progress bar
        $progressBar = $this->output->createProgressBar($patients->count());
        $progressBar->start();

        $stats = [
            'processed' => 0,
            'antrean_success' => 0,
            'antrean_failed' => 0,
            'task_success' => 0,
            'task_failed' => 0,
        ];

        foreach ($patients as $patient) {
            $this->processPatient($patient, $stats);

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Display final statistics
        $this->displayStatistics($stats);

        $this->info('BPJS Task ID processing completed!');
    }

    /**
     * Process a single patient
     */
    protected function processPatient($patient, &$stats)
    {
        $stats['processed']++;

        $referensi = $patient->referensiMobilejknBpjs;
        if (!$referensi) {
            $this->line("No referensi data for patient: {$patient->no_rawat}");
            return;
        }

        $kodebooking = $referensi ? $referensi->no_rawat : $patient->no_rawat;

        // Prepare patient data for antrean
        $patientData = $this->preparePatientData($patient, $referensi);

        // Add antrean first
        $this->line("Processing patient: {$patient->no_rawat} (Booking: {$kodebooking})");

        if (!$this->option('dry-run')) {
            // $antreanResult = app(MobileJknService::class)->addAntrean($patientData);

            // if ($antreanResult['success']) {
            //     $stats['antrean_success']++;
            //     $this->line("Antrean added successfully for: {$kodebooking}");

                // Send task IDs
            $this->sendTaskIds($kodebooking, $stats);
            // } else {
            //     $stats['antrean_failed']++;
            //     $this->line("Failed to add antrean for: {$kodebooking} - " . ($antreanResult['error'] ?? 'Unknown error'));
            // }
        } else {
            $this->line("DRY RUN: Would add antrean for: {$kodebooking}");
            $this->sendTaskIds($kodebooking, $stats, true);
        }
    }

    /**
     * Send task IDs for a patient
     */
    protected function sendTaskIds($kodebooking, &$stats, $dryRun = false)
    {
        $taskIds = [3, 4, 5, 6, 7]; // Task IDs to send

        foreach ($taskIds as $taskId) {
            if ($dryRun) {
                $this->line("DRY RUN: Would send Task ID {$taskId} for: {$kodebooking}");
                continue;
            }

            $result = app(MobileJknService::class)->updateTaskIdFromDatabase($kodebooking, $taskId);

            if ($result['success']) {
                $stats['task_success']++;
                $this->line("Task ID {$taskId} sent successfully for: {$kodebooking}");
            } else {
                $stats['task_failed']++;
                $this->line("Failed to send Task ID {$taskId} for: {$kodebooking} - " . ($result['error'] ?? 'Unknown error'));
            }
        }
    }

    /**
     * Prepare patient data for antrean API
     */
    protected function preparePatientData($patient, $referensi)
    {
        return [
            'nobooking' => $referensi->nobooking,
            'nomorkartu' => $referensi->nomorkartu,
            'nik' => $referensi->nik,
            'nohp' => $referensi->nohp,
            'kodepoli' => $referensi->kodepoli,
            'nm_poli' => $patient->poliklinik->nm_poli ?? '',
            'pasienbaru' => $referensi->pasienbaru,
            'no_rkm_medis' => $referensi->norm,
            'tanggalperiksa' => $referensi->tanggalperiksa->format('Y-m-d'),
            'kodedokter' => $referensi->kodedokter,
            'nm_dokter' => $patient->dokter->nm_dokter ?? '',
            'jampraktek' => $referensi->jampraktek,
            'jeniskunjungan' => $referensi->jeniskunjungan,
            'nomorreferensi' => $referensi->nomorreferensi,
            'nomorantrean' => $referensi->nomorantrean,
            'angkaantrean' => $referensi->angkaantrean,
            'estimasidilayani' => $referensi->estimasidilayani,
            'sisakuotajkn' => $referensi->sisakuotajkn,
            'kuotajkn' => $referensi->kuotajkn,
            'sisakuotanonjkn' => $referensi->sisakuotanonjkn,
            'kuotanonjkn' => $referensi->kuotanonjkn,
        ];
    }

    /**
     * Display processing statistics
     */
    protected function displayStatistics($stats)
    {
        $this->info('📈 Processing Statistics:');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Patients Processed', $stats['processed']],
                ['Antrean Success', $stats['antrean_success']],
                ['Antrean Failed', $stats['antrean_failed']],
                ['Task ID Success', $stats['task_success']],
                ['Task ID Failed', $stats['task_failed']],
            ]
        );
    }
}
