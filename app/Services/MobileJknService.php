<?php

namespace App\Services;

use App\Models\ReferensiMobilejknBpjs;
use App\Models\RegPeriksa;
use App\Models\PemeriksaanRalan;
use App\Models\Petugas;
use App\Models\Dokter;
use App\Models\ResepObat;
use App\Services\BpjsLogService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MobileJknService
{
    protected $baseUrl;
    protected $consId;
    protected $userKey;
    protected $secretKey;
    protected $bpjsLogService;

    public function __construct(BpjsLogService $bpjsLogService)
    {
        // You should configure these in your .env file or config
        $this->baseUrl = config('services.mobilejkn.base_url', 'https://api.mobilejkn.com');
        $this->consId = config('services.mobilejkn.cons_id');
        $this->userKey = config('services.mobilejkn.user_key');
        $this->secretKey = config('services.mobilejkn.secret_key');
        $this->bpjsLogService = $bpjsLogService;
    }

    /**
     * Get timestamp for a specific task ID from database
     *
     * @param string $kodebooking
     * @param int $taskid
     * @return string|null Timestamp in milliseconds or null if not found
     */
    public function getTaskTimestampFromDatabase(string $kodebooking, int $taskid): ?string
    {
        try {
            switch ($taskid) {
                case 3:
                    return $this->getTask3Timestamp($kodebooking);
                case 4:
                    return $this->getTask4Timestamp($kodebooking);
                case 5:
                    return $this->getTask5Timestamp($kodebooking);
                case 6:
                    return $this->getTask6Timestamp($kodebooking);
                case 7:
                    return $this->getTask7Timestamp($kodebooking);
                default:
                    return null;
            }
        } catch (\Exception $e) {
            Log::error('Error getting task timestamp from database', [
                'kodebooking' => $kodebooking,
                'taskid' => $taskid,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get task 3 timestamp - from referensi_mobilejkn_bpjs validasi or reg_periksa jam_reg
     */
    protected function getTask3Timestamp(string $kodebooking): ?string
    {
        // First try to get from referensi_mobilejkn_bpjs
        $referensi = ReferensiMobilejknBpjs::where('nobooking', $kodebooking)->first();

        if ($referensi && $referensi->validasi) {
            return (string) ($referensi->validasi->timestamp * 1000);
        }

        // If not found, get from reg_periksa jam_reg
        $regPeriksa = RegPeriksa::whereHas('referensiMobilejknBpjs', function($query) use ($kodebooking) {
            $query->where('nobooking', $kodebooking);
        })->first();

        if ($regPeriksa && $regPeriksa->jam_reg) {
            return (string) ($regPeriksa->jam_reg->timestamp * 1000);
        }

        return null;
    }

    /**
     * Get task 4 timestamp - from pemeriksaan_ralan where nip is in petugas
     */
    protected function getTask4Timestamp(string $kodebooking): ?string
    {
        $pemeriksaan = PemeriksaanRalan::whereHas('regPeriksa.referensiMobilejknBpjs', function($query) use ($kodebooking) {
            $query->where('nobooking', $kodebooking);
        })
        ->whereHas('petugas') // nip exists in petugas table
        ->orderBy('jam_rawat', 'desc')
        ->first();

        if ($pemeriksaan && $pemeriksaan->jam_rawat) {
            return (string) ($pemeriksaan->jam_rawat->timestamp * 1000);
        }

        return null;
    }

    /**
     * Get task 5 timestamp - from pemeriksaan_ralan where nip is in dokter
     */
    protected function getTask5Timestamp(string $kodebooking): ?string
    {
        $pemeriksaan = PemeriksaanRalan::whereHas('regPeriksa.referensiMobilejknBpjs', function($query) use ($kodebooking) {
            $query->where('nobooking', $kodebooking);
        })
        ->whereHas('dokter') // nip exists in dokter table
        ->orderBy('jam_rawat', 'desc')
        ->first();

        if ($pemeriksaan && $pemeriksaan->jam_rawat) {
            return (string) ($pemeriksaan->jam_rawat->timestamp * 1000);
        }

        return null;
    }

    /**
     * Get task 6 timestamp - from resep_obat jam
     */
    protected function getTask6Timestamp(string $kodebooking): ?string
    {
        $resep = ResepObat::whereHas('regPeriksa.referensiMobilejknBpjs', function($query) use ($kodebooking) {
            $query->where('nobooking', $kodebooking);
        })
        ->orderBy('jam', 'desc')
        ->first();

        if ($resep && $resep->jam) {
            return (string) ($resep->jam->timestamp * 1000);
        }

        return null;
    }

    /**
     * Get task 7 timestamp - from resep_obat jam_penyerahan
     */
    protected function getTask7Timestamp(string $kodebooking): ?string
    {
        $resep = ResepObat::whereHas('regPeriksa.referensiMobilejknBpjs', function($query) use ($kodebooking) {
            $query->where('nobooking', $kodebooking);
        })
        ->orderBy('jam_penyerahan', 'desc')
        ->first();

        if ($resep && $resep->jam_penyerahan) {
            return (string) ($resep->jam_penyerahan->timestamp * 1000);
        }

        return null;
    }

    /**
     * Send task ID update to Mobile JKN API
     *
     * @param string $kodebooking
     * @param int $taskid (1,2,3,4,5,6,7,99)
     * @param string|null $waktu Timestamp in milliseconds, if null will get from database
     * @return array
     */
    public function updateTaskId(string $kodebooking, int $taskid, ?string $waktu = null): array
    {
        try {
            // Validate taskid
            if (!in_array($taskid, [1, 2, 3, 4, 5, 6, 7, 99])) {
                throw new \InvalidArgumentException('Invalid taskid. Must be one of: 1,2,3,4,5,6,7,99');
            }

            // If waktu is not provided, get from database
            if ($waktu === null) {
                $waktu = $this->getTaskTimestampFromDatabase($kodebooking, $taskid);

                if ($waktu === null) {
                    throw new \Exception("Could not find timestamp for task ID {$taskid} in database");
                }
            }

            // Generate timestamp and signature
            $timestamp = $this->getUtcTimestamp();
            $signature = $this->generateSignature($timestamp);

            // Prepare request data
            $requestData = [
                'kodebooking' => $kodebooking,
                'taskid' => (string) $taskid,
                'waktu' => $waktu
            ];

            // Log the request
            Log::info('Mobile JKN Task Update Request', [
                'kodebooking' => $kodebooking,
                'taskid' => $taskid,
                'waktu' => $waktu,
                'timestamp' => $timestamp
            ]);

            // Make HTTP request
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-cons-id' => $this->consId,
                'x-timestamp' => $timestamp,
                'x-signature' => $signature,
                'user_key' => $this->userKey,
            ])->post($this->baseUrl . '/antrean/updatewaktu', $requestData);

            // Parse response
            $responseData = $response->json();

            // Log to BPJS log database
            $this->bpjsLogService->logRequest(
                $response->status(),
                json_encode($requestData),
                json_encode($responseData),
                $this->baseUrl . '/antrean/updatewaktu',
                'POST'
            );

            // Log the response
            Log::info('Mobile JKN Task Update Response', [
                'status' => $response->status(),
                'response' => $responseData
            ]);

            return [
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'data' => $responseData,
                'metadata' => $responseData['metadata'] ?? null
            ];

        } catch (\Exception $e) {
            // Log error to BPJS log database
            $this->bpjsLogService->logRequest(
                500,
                json_encode(['kodebooking' => $kodebooking, 'taskid' => $taskid, 'waktu' => $waktu]),
                $e->getMessage(),
                $this->baseUrl . '/antrean/updatewaktu',
                'POST'
            );

            Log::error('Mobile JKN Task Update Error', [
                'kodebooking' => $kodebooking,
                'taskid' => $taskid,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status_code' => 500
            ];
        }
    }

    /**
     * Update task ID with timestamp from database
     *
     * @param string $kodebooking
     * @param int $taskid
     * @return array
     */
    public function updateTaskIdFromDatabase(string $kodebooking, int $taskid): array
    {
        return $this->updateTaskId($kodebooking, $taskid, null);
    }

    /**
     * Update task ID with current timestamp
     *
     * @param string $kodebooking
     * @param int $taskid
     * @return array
     */
    public function updateTaskIdNow(string $kodebooking, int $taskid): array
    {
        $currentTime = (string) now()->timestamp * 1000; // Current time in milliseconds
        return $this->updateTaskId($kodebooking, $taskid, $currentTime);
    }

    /**
     * Batch update multiple task IDs
     *
     * @param array $updates Array of ['kodebooking', 'taskid', 'waktu'] arrays
     * @return array
     */
    public function batchUpdateTaskIds(array $updates): array
    {
        $results = [];

        foreach ($updates as $update) {
            $result = $this->updateTaskId(
                $update['kodebooking'],
                $update['taskid'],
                $update['waktu'] ?? null
            );
            $results[] = $result;
        }

        return $results;
    }

    /**
     * Add antrean (queue) to Mobile JKN API
     *
     * @param array $patientData Patient data from database
     * @return array
     */
    public function addAntrean(array $patientData): array
    {
        try {
            // Generate timestamp and signature
            $timestamp = $this->getUtcTimestamp();
            $signature = $this->generateSignature($timestamp);

            // Prepare request data based on the Java code structure
            $requestData = [
                'kodebooking' => $patientData['nobooking'],
                'jenispasien' => 'JKN',
                'nomorkartu' => $patientData['nomorkartu'],
                'nik' => $patientData['nik'],
                'nohp' => $patientData['nohp'],
                'kodepoli' => $patientData['kodepoli'],
                'namapoli' => $patientData['nm_poli'],
                'pasienbaru' => (int) $patientData['pasienbaru'],
                'norm' => $patientData['no_rkm_medis'],
                'tanggalperiksa' => $patientData['tanggalperiksa'],
                'kodedokter' => (int) $patientData['kodedokter'],
                'namadokter' => $patientData['nm_dokter'],
                'jampraktek' => $patientData['jampraktek'],
                'jeniskunjungan' => (int) substr($patientData['jeniskunjungan'], 0, 1),
                'nomorreferensi' => $patientData['nomorreferensi'],
                'nomorantrean' => $patientData['nomorantrean'],
                'angkaantrean' => (int) $patientData['angkaantrean'],
                'estimasidilayani' => (int) $patientData['estimasidilayani'],
                'sisakuotajkn' => (int) $patientData['sisakuotajkn'],
                'kuotajkn' => (int) $patientData['kuotajkn'],
                'sisakuotanonjkn' => (int) $patientData['sisakuotanonjkn'],
                'kuotanonjkn' => (int) $patientData['kuotanonjkn'],
                'keterangan' => 'Peserta harap 30 menit lebih awal guna pencatatan administrasi.'
            ];

            // Log the request
            Log::info('Mobile JKN Add Antrean Request', [
                'kodebooking' => $patientData['nobooking'],
                'timestamp' => $timestamp,
                'request_data' => $requestData
            ]);

            // Make HTTP request
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-cons-id' => $this->consId,
                'x-timestamp' => $timestamp,
                'x-signature' => $signature,
                'user_key' => $this->userKey,
            ])->post($this->baseUrl . '/antrean/add', $requestData);

            // Parse response
            $responseData = $response->json();

            // Log to BPJS log database
            $this->bpjsLogService->logRequest(
                $response->status(),
                json_encode($requestData),
                json_encode($responseData),
                $this->baseUrl . '/antrean/add',
                'POST'
            );

            // Log the response
            Log::info('Mobile JKN Add Antrean Response', [
                'status' => $response->status(),
                'response' => $responseData
            ]);

            return [
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'data' => $responseData,
                'metadata' => $responseData['metadata'] ?? null
            ];

        } catch (\Exception $e) {
            // Log error to BPJS log database
            $this->bpjsLogService->logRequest(
                500,
                json_encode($requestData ?? []),
                $e->getMessage(),
                $this->baseUrl . '/antrean/add',
                'POST'
            );

            Log::error('Mobile JKN Add Antrean Error', [
                'kodebooking' => $patientData['nobooking'] ?? 'unknown',
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status_code' => 500
            ];
        }
    }

    /**
     * Get UTC timestamp as string
     *
     * @return string
     */
    protected function getUtcTimestamp(): string
    {
        return (string) now()->utc()->timestamp * 1000; // Convert to milliseconds
    }

    /**
     * Generate HMAC signature
     *
     * @param string $timestamp
     * @return string
     */
    protected function generateSignature(string $timestamp): string
    {
        $data = $this->consId . '&' . $timestamp;
        return hash_hmac('sha256', $data, $this->secretKey);
    }
}
