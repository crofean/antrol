<?php

namespace App\Services;

use App\Models\MapingDokterDpjpvclaim;
use App\Models\MapingPoliBpjs;
use App\Models\ReferensiMobilejknBpjs;
use App\Models\RegPeriksa;
use App\Models\PemeriksaanRalan;
use App\Models\Petugas;
use App\Models\Dokter;
use App\Models\ResepObat;
use App\Services\BpjsLogService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use App\Models\ReferensiMobilejknBpjsTaskid;
use Throwable;
use App\Models\Jadwal;
use App\Models\Pasien;

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
        $this->baseUrl = config('mobilejkn.base_url', 'https://apijkn.bpjs-kesehatan.go.id/antreanrs');
        $this->consId = config('mobilejkn.cons_id');
        $this->userKey = config('mobilejkn.user_key');
        $this->secretKey = config('mobilejkn.secret_key');
        $this->bpjsLogService = $bpjsLogService;
    }

    /**
     * Generate signature for authentication
     *
     * @param string $timestamp
     * @return string
     */
    protected function generateSignature(string $timestamp): string
    {
        $data = $this->consId . '&' . $timestamp;
        return hash_hmac('sha256', $data, $this->secretKey, true);
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
                case 99:
                    return (string) now()->timestamp * 1000; // Current time in milliseconds
                default:
                    return null;
            }
        } catch (Exception $e) {
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
            return $referensi->validasi->timestamp * 1000;
        }

        // If not found, get from reg_periksa jam_reg
        $regPeriksa = RegPeriksa::where('no_rawat', $kodebooking)->first();

        if ($regPeriksa && $regPeriksa->jam_reg) {
            $waktuReg =  Carbon::parse(str_replace(' 00:00:00', '', $regPeriksa->tgl_registrasi) . ' ' . $regPeriksa->jam_reg->toTimeString());
            return $waktuReg->timestamp * 1000;
        }

        return null;
    }

    /**
     * Get task 4 timestamp - from pemeriksaan_ralan where nip is in petugas
     */
    protected function getTask4Timestamp(string $kodebooking): ?string
    {
        $pemeriksaan = PemeriksaanRalan::where('no_rawat', $kodebooking)
        ->whereHas('petugas') // nip exists in petugas table
        ->orderBy('jam_rawat', 'desc')
        ->first();

        if ($pemeriksaan && $pemeriksaan->jam_rawat) {
            $waktu = Carbon::parse(str_replace(' 00:00:00', '', $pemeriksaan->tgl_perawatan) . ' ' . $pemeriksaan->jam_rawat->toTimeString());
            return (string) ($waktu->timestamp * 1000);
        }

        return null;
    }

    /**
     * Get task 5 timestamp - from pemeriksaan_ralan where nip is in dokter
     */
    protected function getTask5Timestamp(string $kodebooking): ?string
    {
        $pemeriksaan = PemeriksaanRalan::where('no_rawat', $kodebooking)
        ->whereHas('dokter') // nip exists in dokter table
        ->orderBy('jam_rawat', 'desc')
        ->first();

        if ($pemeriksaan && $pemeriksaan->jam_rawat) {
            $waktu = Carbon::parse(str_replace(' 00:00:00', '', $pemeriksaan->tgl_perawatan) . ' ' . $pemeriksaan->jam_rawat->toTimeString());
            return (string) ($waktu->timestamp * 1000);
        }

        return null;
    }

    /**
     * Get task 6 timestamp - from resep_obat jam
     */
    protected function getTask6Timestamp(string $kodebooking): ?string
    {
        $resep = ResepObat::where('tgl_perawatan', $kodebooking)
        ->orderBy('jam', 'desc')
        ->first();

        if ($resep && $resep->jam) {
            $waktu = Carbon::parse(str_replace(' 00:00:00', '', $resep->tgl_perawatan) . ' ' . $resep->jam->toTimeString());
            return (string) ($waktu->timestamp * 1000);
        }

        return null;
    }

    /**
     * Get task 7 timestamp - from resep_obat jam_penyerahan
     */
    protected function getTask7Timestamp(string $kodebooking): ?string
    {
        $resep = ResepObat::where('no_rawat', $kodebooking)
        ->orderBy('jam_penyerahan', 'desc')
        ->first();

        if ($resep && $resep->jam_penyerahan) {
            $waktu = Carbon::parse(str_replace(' 00:00:00', '', $resep->tgl_penyerahan) . ' ' . $resep->jam_penyerahan->toTimeString());
            return (string) ($waktu->timestamp * 1000);
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
                throw new InvalidArgumentException('Invalid taskid. Must be one of: 1,2,3,4,5,6,7,99');
            }

            // If waktu is not provided, get from database
            if ($waktu === null) {
                $waktu = $this->getTaskTimestampFromDatabase($kodebooking, $taskid);

                if ($waktu === null) {
                    throw new Exception("Could not find timestamp for task ID {$taskid} in database");
                }
            }

            date_default_timezone_set('UTC');
            // Generate timestamp and signature
            $timestamp = $this->getUtcTimestamp();
            $signature = base64_encode($this->generateSignature($timestamp));

            Log::info($waktu);
            // Prepare request data
            $requestData = [
                'kodebooking' => $kodebooking,
                'taskid' => (string) $taskid,
                'waktu' => (int) $waktu
            ];

            // Log the request
            Log::info('Mobile JKN Task Update Request', [
                'kodebooking' => $kodebooking,
                'taskid' => $taskid,
                'waktu' => (int) $waktu,
                'timestamp' => (int) $timestamp
            ]);

            // Make HTTP request
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-cons-id' => $this->consId,
                'X-timestamp' => $timestamp,
                'X-signature' => $signature,
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

            // If BPJS returns message indicating the TaskId already exists, persist it locally
            $metaMessage = $responseData['metadata']['message'] ?? ($responseData['metadata'] ?? null);
            if (is_string($metaMessage) && (strpos($metaMessage, "TaskId={$taskid} sudah ada") !== false || strpos($metaMessage, "Ok") !== false)) {
                try {
                    $taskRecord = ReferensiMobilejknBpjsTaskid::firstOrNew([
                        'no_rawat' => $kodebooking,
                        'taskid' => (string) $taskid,
                        'waktu' =>  Carbon::createFromTimestampMs((int)$waktu)->toDateTimeString()
                    ]);

                    $taskRecord->save();
                } catch (Throwable $e) {
                    Log::error('Failed to save ReferensiMobilejknBpjsTaskid', ['error' => $e->getMessage(), 'kodebooking' => $kodebooking, 'taskid' => $taskid]);
                }
            }

            $deleteTaskId = $taskid-1;
            if (is_string($metaMessage) && strpos($metaMessage, "TaskId={$deleteTaskId} belum ada") !== false) {
                ReferensiMobilejknBpjsTaskid::where('no_rawat', $kodebooking)
                    ->where('taskid', $deleteTaskId)
                    ->delete();

                return [
                    'success' => true,
                    'message' => "TaskId {$taskid} belum ada. Hapus secara lokal.",
                    'status_code' => $response->status(),
                    'data' => $responseData,
                    'metadata' => $responseData['metadata'] ?? null
                ];
            }

            return [
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'data' => $responseData,
                'metadata' => $responseData['metadata'] ?? null
            ];

        } catch (Exception $e) {
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

            $poliBpjs = MapingPoliBpjs::where('kd_poli', $patientData['kodepoli'])->first();
            $doctorBpjs = MapingDokterDpjpvclaim::where('kd_dokter', $patientData['kodedokter'])->first();


            // Prepare request data based on the Java code structure
            $requestData = [
                'kodebooking' => $patientData['nobooking'],
                'jenispasien' => 'JKN',
                'nomorkartu' => $patientData['nomorkartu'],
                'nik' => $patientData['nik'],
                'nohp' => $patientData['nohp'],
                'kodepoli' => $poliBpjs ? $poliBpjs->kd_poli_bpjs : $patientData['kodepoli'],
                'namapoli' => $poliBpjs ? $poliBpjs->nm_poli_bpjs : $patientData['nm_poli'],
                'pasienbaru' => (int) $patientData['pasienbaru'],
                'norm' => $patientData['no_rkm_medis'],
                'tanggalperiksa' => $patientData['tanggalperiksa'],
                'kodedokter' => (int) $doctorBpjs->kd_dokter_bpjs,
                'namadokter' => $doctorBpjs->nm_dokter_bpjs,
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

        } catch (Exception $e) {
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
        return strval(time()-strtotime('1970-01-01 00:00:00')); // Convert to milliseconds
    }
    
    /**
     * Get patient data needed for task ID updates
     *
     * @param string $regNo
     * @return array|null
     */
    public function getPatientDataForTaskId(string $regNo): ?array
    {
        try {
            // Get registration data
            $regPeriksa = RegPeriksa::where('no_rawat', $regNo)->first();
            
            if (!$regPeriksa) {
                Log::error('Registration not found: ' . $regNo);
                // return null;
            }
            
            // Get doctor information
            $dokter = PemeriksaanRalan::where('no_rawat', $regNo)->where('nip', $regPeriksa->kd_dokter)->first();

            // Get referral data from BPJS
            $referral = ReferensiMobilejknBpjs::where('no_rawat', $regNo)->first();
            
            if (!$referral) {
                Log::error('BPJS referral not found for: ' . $regNo);
                // return null;
            }
            
            // Get examination data
            $pemeriksaan = PemeriksaanRalan::where('no_rawat', $regNo)->join('petugas', 'petugas.nip', '=', 'pemeriksaan_ralan.nip')->first();

            // Get prescription data
            $resepObat = ResepObat::where('no_rawat', $regNo)->first();

            $kode = $referral != null ? $referral->nobooking : $regPeriksa->no_rawat;

            return [
                'registration' => $regPeriksa,
                'doctor' => $dokter,
                'referral' => $referral,
                'examination' => $pemeriksaan,
                'prescription' => $resepObat,
                'task_timestamps' => [
                    '3' => $this->getTask3Timestamp($kode),
                    '4' => $this->getTask4Timestamp($kode),
                    '5' => $this->getTask5Timestamp($kode),
                    '6' => $this->getTask6Timestamp($kode),
                    '7' => $this->getTask7Timestamp($kode)
                ],
                'kodebooking' => $kode
            ];
            
        } catch (Exception $e) {
            Log::error('Error retrieving patient data for task ID: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get saved task ID record from referensi_mobilejkn_bpjs_taskid by no_rawat and taskid
     *
     * @param string $noRawat
     * @param int $taskid
     * @return array|null
     */
    public function getTaskIdRecord(string $noRawat, int $taskid = null): ?array
    {
        try {
            $record = ReferensiMobilejknBpjsTaskid::where('no_rawat', $noRawat);

            if ($taskid !== null) {
                $record = $record->where('taskid', $taskid)->first();
            } else {
                $record = $record->get();
            }

            if (!$record) {
                return null;
            }

            // Return array representation; waktu will be cast to ISO string by model casting
            return $record->toArray();
        } catch (Throwable $e) {
            Log::error('Error fetching ReferensiMobilejknBpjsTaskid', [
                'no_rawat' => $noRawat,
                'taskid' => $taskid,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Wrapper to call sendAddAntreanByNoRawat (public API)
     */
    public function sendAddAntreanByNoRawat(string $noRawat): array
    {
        return $this->sendAddAntreanByNoRawatInternal($noRawat);
    }

    /**
     * Internal implementation (kept separate to avoid duplicate name issues)
     */
    protected function sendAddAntreanByNoRawatInternal(string $noRawat): array
    {
        try {
            $reg = RegPeriksa::where('no_rawat', $noRawat)->first();
            if (!$reg) {
                return [
                    'status' => false,
                    'message' => 'Registration not found',
                    'data' => [],
                    'payload' => null,
                    'bpjs' => null
                ];
            }

            $pasien = Pasien::where('no_rkm_medis', $reg->no_rkm_medis)->first();

            $mapPoli = MapingPoliBpjs::where('kd_poli_rs', $reg->kd_poli)->first();
            $mapDok = MapingDokterDpjpvclaim::where('kd_dokter', $reg->kd_dokter)->first();

            $jadwal = Jadwal::where('kd_dokter', $reg->kd_dokter)
                ->where('kd_poli', $reg->kd_poli)
                ->orderBy('jam_mulai')
                ->first();

            $kodepoli = $mapPoli ? $mapPoli->kd_poli_bpjs : $reg->kd_poli;
            $namapoli = $reg->poliklinik->nm_poli ?? ($mapPoli->nm_poli_bpjs ?? null);
            $kodedokter = $mapDok ? $mapDok->kd_dokter_bpjs : $reg->kd_dokter;
            $namadokter = $mapDok ? ($mapDok->nm_dokter_bpjs ?? $reg->dokter->nm_dokter ?? null) : ($reg->dokter->nm_dokter ?? null);

            if ($jadwal) {
                $jamMulai = substr($jadwal->jam_mulai ?? '00:00:00', 0, 5);
                $jamSelesai = substr($jadwal->jam_selesai ?? $jadwal->jam_mulai ?? '00:00:00', 0, 5);
                $jampraktek = $jamMulai . '-' . $jamSelesai;
            } else {
                $jampraktek = '08:00-16:00';
            }

            $noRegInt = intval($reg->no_reg);
            $baseDatetime = Carbon::parse($reg->tgl_registrasi . ' ' . ($jadwal->jam_mulai ?? '00:00:00'));
            $estimasidilayani = $baseDatetime->copy()->addMinutes($noRegInt * 2);

            $pasienbaru = 0;
            if (stripos($reg->stts_daftar ?? '', 'Baru') !== false) {
                $pasienbaru = 1;
            }

            $jenisKunjungan = 1;
            $nomorreferensi = $this->fetchRujukan($pasien->no_peserta ?? '', $kodepoli);
            if (empty($nomorreferensi)) {
                $jenisKunjungan = 4; // Rujukan RS
                $nomorreferensi = $this->fetchRujukanRS($pasien->no_peserta ?? '', $kodepoli);
            }

            if (empty($nomorreferensi)) {
                $jenisKunjungan = 3; // Kontrol
                $nomorreferensi = $this->fetchKontrol($pasien->no_peserta ?? '', $kodepoli, date('m', $reg->tgl_registrasi), date('Y'));
            }

            $angkaAntrean = str_pad((string) intval($reg->no_reg), 3, '0', STR_PAD_LEFT);
            $nomorAntrean = ($kodepoli ? $kodepoli : $reg->kd_poli) . '-' . $angkaAntrean;

            $payload = [
                'kodebooking' => $reg->no_rawat,
                'jenispasien' => 'JKN',
                'nomorkartu' => $pasien->no_peserta ?? '',
                'nik' => $pasien->no_ktp ?? '',
                'nohp' => $pasien->no_tlp ?? '00000000',
                'norm' => $reg->no_rkm_medis,
                'kodepoli' => $kodepoli,
                'namapoli' => $namapoli,
                'pasienbaru' => $pasienbaru,
                'tanggalperiksa' => $reg->tgl_registrasi,
                'kodedokter' => $kodedokter,
                'namadokter' => $namadokter,
                'jampraktek' => $jampraktek,
                'jeniskunjungan' => $jenisKunjungan,
                'nomorreferensi' => $nomorreferensi ?: '-',
                'nomorantrean' => $nomorAntrean,
                'angkaantrean' => $angkaAntrean,
                'estimasidilayani' => (int) ($estimasidilayani->timestamp * 1000),
                'sisakuotajkn' => $jadwal ? max(0, intval($jadwal->kuota) - intval($reg->no_reg)) : 0,
                'kuotajkn' => $jadwal ? intval($jadwal->kuota) : 0,
                'sisakuotanonjkn' => $jadwal ? max(0, intval($jadwal->kuota) - intval($reg->no_reg)) : 0,
                'kuotanonjkn' => $jadwal ? intval($jadwal->kuota) : 0,
                'keterangan' => 'Peserta harap 30 menit lebih awal guna pencatatan administrasi.'
            ];

            $resp = $this->addAntrean($payload);

            $status = $resp['success'] ?? false;
            $message = $status ? ($resp['metadata']['message'] ?? 'Antrean sent') : ($resp['metadata']['message'] ?? 'Mohon Maaf Gagal Mengirim Antrean, Silahkan Coba Lagi!');

            return [
                'status' => $status,
                'message' => $message,
                'data' => [],
                'payload' => $payload,
                'bpjs' => [
                    'metadata' => $resp['metadata'] ?? null,
                    'data' => $resp['data'] ?? null
                ]
            ];

        } catch (Throwable $e) {
            Log::error('Error in sendAddAntreanByNoRawat: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => $e->getMessage(),
                'data' => [],
                'payload' => null,
                'bpjs' => null
            ];
        }
    }

    /**
     * Placeholder: fetch referral by participant and poli (implement BPJS lookup if available)
     */
    protected function fetchRujukan(string $noPeserta, string $kdPoliBpjs): string
    {
        $noRujukan = '';

        try {
            $timestamp = $this->getUtcTimestamp();
            $signature = base64_encode($this->generateSignature($timestamp));

            $url = rtrim($this->baseUrl, '/') . '/Rujukan/List/Peserta/' . $noPeserta;

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-cons-id' => $this->consId,
                'x-timestamp' => $timestamp,
                'x-signature' => $signature,
                'user_key' => $this->userKey,
            ])->get($url);

            $responseData = $response->json();

            // Log request/response to BPJS log
            try {
                $this->bpjsLogService->logRequest(
                    $response->status(),
                    json_encode([]),
                    json_encode($responseData),
                    $url,
                    'GET'
                );
            } catch (Throwable $e) {
                Log::error('Failed to log BPJS rujukan request', ['error' => $e->getMessage()]);
            }

            $meta = $responseData['metaData'] ?? $responseData['metadata'] ?? null;
            $code = $meta['code'] ?? $meta['status'] ?? null;

            if ($code === '200' || $code === 200) {
                $rujukanList = null;

                // Response may contain 'response' as an object/array or a JSON string
                if (isset($responseData['response'])) {
                    $respContent = $responseData['response'];

                    if (is_string($respContent)) {
                        $decoded = json_decode($respContent, true);
                        if (is_array($decoded) && isset($decoded['rujukan'])) {
                            $rujukanList = $decoded['rujukan'];
                        }
                    } elseif (is_array($respContent) && isset($respContent['rujukan'])) {
                        $rujukanList = $respContent['rujukan'];
                    }
                }

                if (is_array($rujukanList)) {
                    foreach ($rujukanList as $item) {
                        $kodePoli = $item['poliRujukan']['kode'] ?? ($item['poliRujukan']['kode'] ?? '');
                        if ($kodePoli === $kdPoliBpjs) {
                            $noRujukan = $item['noKunjungan'] ?? '';
                            break;
                        }
                    }
                }
            }

            return $noRujukan;
        } catch (Throwable $e) {
            Log::error('Error fetching BPJS rujukan', ['noPeserta' => $noPeserta, 'error' => $e->getMessage()]);
            return $noRujukan;
        }
    }

    /**
     * Placeholder: fetch RS referral fallback
     */
    protected function fetchRujukanRS(string $noPeserta, string $kdPoliBpjs): string
    {
        $noRujukan = '';

        try {
            $timestamp = $this->getUtcTimestamp();
            $signature = base64_encode($this->generateSignature($timestamp));

            $url = rtrim($this->baseUrl, '/') . '/Rujukan/RS/List/Peserta/' . $noPeserta;

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-cons-id' => $this->consId,
                'x-timestamp' => $timestamp,
                'x-signature' => $signature,
                'user_key' => $this->userKey,
            ])->get($url);

            $responseData = $response->json();

            // Log request/response to BPJS log
            try {
                $this->bpjsLogService->logRequest(
                    $response->status(),
                    json_encode([]),
                    json_encode($responseData),
                    $url,
                    'GET'
                );
            } catch (Throwable $e) {
                Log::error('Failed to log BPJS rujukan RS request', ['error' => $e->getMessage()]);
            }

            $meta = $responseData['metaData'] ?? $responseData['metadata'] ?? null;
            $code = $meta['code'] ?? $meta['status'] ?? null;

            if ($code === '200' || $code === 200) {
                $rujukanList = null;

                if (isset($responseData['response'])) {
                    $respContent = $responseData['response'];

                    if (is_string($respContent)) {
                        $decoded = json_decode($respContent, true);
                        if (is_array($decoded) && isset($decoded['rujukan'])) {
                            $rujukanList = $decoded['rujukan'];
                        }
                    } elseif (is_array($respContent) && isset($respContent['rujukan'])) {
                        $rujukanList = $respContent['rujukan'];
                    }
                }

                if (is_array($rujukanList)) {
                    foreach ($rujukanList as $item) {
                        $kodePoli = $item['poliRujukan']['kode'] ?? ($item['poliRujukan']['kode'] ?? '');
                        if ($kodePoli === $kdPoliBpjs) {
                            $noRujukan = $item['noKunjungan'] ?? '';
                            break;
                        }
                    }
                }
            }

            return $noRujukan;
        } catch (Throwable $e) {
            Log::error('Error fetching BPJS rujukan RS', ['noPeserta' => $noPeserta, 'error' => $e->getMessage()]);
            return $noRujukan;
        }

        
    }

    /**
     * Placeholder: fetch kontrol rujukan by peserta and poli
     */
    protected function fetchKontrol(string $noPeserta, string $kdPoliBpjs, ?string $bulan = null, ?string $tahun = null, int $filter = 2): string
    {
        $noSuratKontrol = '';

        try {
            // default to current month/year if not provided
            $now = Carbon::now();
            $bulan = $bulan ?: $now->format('m');
            $tahun = $tahun ?: $now->format('Y');

            $timestamp = $this->getUtcTimestamp();
            $signature = base64_encode($this->generateSignature($timestamp));

            $url = rtrim($this->baseUrl, '/') . "/RencanaKontrol/ListRencanaKontrol/Bulan/{$bulan}/Tahun/{$tahun}/Nokartu/{$noPeserta}/filter/{$filter}";

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-cons-id' => $this->consId,
                'x-timestamp' => $timestamp,
                'x-signature' => $signature,
                'user_key' => $this->userKey,
            ])->get($url);

            $responseData = $response->json();

            // Log request/response
            try {
                $this->bpjsLogService->logRequest(
                    $response->status(),
                    json_encode([]),
                    json_encode($responseData),
                    $url,
                    'GET'
                );
            } catch (Throwable $e) {
                Log::error('Failed to log BPJS kontrol request', ['error' => $e->getMessage()]);
            }

            $meta = $responseData['metaData'] ?? $responseData['metadata'] ?? null;
            $code = $meta['code'] ?? $meta['status'] ?? null;

            if ($code === '200' || $code === 200) {
                $list = $responseData['response']['list'] ?? null;

                if (is_array($list)) {
                    foreach ($list as $item) {
                        // match poli by kode (poliTujuan) or namaPoliTujuan if needed
                        $poliTujuan = $item['poliTujuan'] ?? ($item['poliTujuan'] ?? '');
                        if ($poliTujuan === $kdPoliBpjs) {
                            $noSuratKontrol = $item['noSuratKontrol'] ?? '';
                            break;
                        }
                    }
                }
            }

            return $noSuratKontrol;
        } catch (Throwable $e) {
            Log::error('Error fetching BPJS rencana kontrol', ['noPeserta' => $noPeserta, 'error' => $e->getMessage()]);
            return $noSuratKontrol;
        }
    }
}
