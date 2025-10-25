<?php

namespace App\Services;

use App\Models\MapingDokterDpjpvclaim;
use App\Models\MapingPoliBpjs;
use App\Models\ReferensiMobilejknBpjs;
use App\Models\ReferensiMobilejknBpjsBatal;
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
use Illuminate\Support\Facades\DB;

class MobileJknService
{
    protected $baseUrl;
    protected $baseUrlVclaim = 'https://apijkn.bpjs-kesehatan.go.id/vclaim-rest';
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
     * Decrypt string using AES-256-CBC
     * 
     * @param string $key The key to use for decryption
     * @param string $string The string to decrypt
     * @return string Decrypted string
     */
    protected function stringDecrypt($key, $string)
    {
        $encrypt_method = 'AES-256-CBC';
        
        // hash
        $key_hash = hex2bin(hash('sha256', $key));
        
        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hex2bin(hash('sha256', $key)), 0, 16);
        
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key_hash, OPENSSL_RAW_DATA, $iv);
        
        return $output;
    }
    
    /**
     * Generate decryption key for BPJS responses
     * 
     * @param string $timestamp Timestamp from the request
     * @return string Decryption key
     */
    protected function generateDecryptionKey(string $timestamp): string
    {
        // Key is consid + secretkey + timestamp
        return $this->consId . $this->secretKey . $timestamp;
    }

    /**
     * Decompress string using LZString library
     * 
     * @param string $string The string to decompress
     * @return string Decompressed string
     */
    protected function decompress($string)
    {
        return \LZCompressor\LZString::decompressFromEncodedURIComponent($string);
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
                case 2:
                    return $this->getTask2Timestamp($kodebooking); // 1 hour before task 3
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
    protected function getTask2Timestamp(string $kodebooking): ?string
    {
        // First try to get from referensi_mobilejkn_bpjs
        $referensi = ReferensiMobilejknBpjs::where('nobooking', $kodebooking)->first();

        if ($referensi && $referensi->validasi) {
            return $referensi->validasi->timestamp * 1000;
        }

        // If not found, get from reg_periksa jam_reg
        $regPeriksa = RegPeriksa::select(DB::raw('CONCAT(tgl_registrasi, " ", jam_reg + INTERVAL 5 MINUTE) AS waktu_reg'))->where('no_rawat', $referensi ? $referensi->no_rawat : $kodebooking)->first();

        if ($regPeriksa && $regPeriksa->waktu_reg) {
            $waktuReg =  Carbon::parse($regPeriksa->waktu_reg);
            return $waktuReg->timestamp * 1000;
        }

        return null;
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
        $regPeriksa = RegPeriksa::where('no_rawat', $referensi ? $referensi->no_rawat : $kodebooking)->first();

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
        $referensi = ReferensiMobilejknBpjs::where('nobooking', $kodebooking)->first();
        if ($referensi) $kodebooking = $referensi->no_rawat;

        $pemeriksaan = PemeriksaanRalan::where('no_rawat', $kodebooking)
        ->whereHas('petugas') // nip exists in petugas table
        ->orderBy('jam_rawat', 'asc')
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
        $referensi = ReferensiMobilejknBpjs::where('nobooking', $kodebooking)->first();
        if ($referensi) $kodebooking = $referensi->no_rawat;

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
        $referensi = ReferensiMobilejknBpjs::where('nobooking', $kodebooking)->first();
        if ($referensi) $kodebooking = $referensi->no_rawat;

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
        $referensi = ReferensiMobilejknBpjs::where('nobooking', $kodebooking)->first();
        if ($referensi) $kodebooking = $referensi->no_rawat;

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

            $batal = '';
            // if ($taskid == 99) {
            //     $refBatal = ReferensiMobilejknBpjsBatal::where('nobooking', $kodebooking)->first();
            //     $batal = $this->batalAntrean($kodebooking, $refBatal ? $refBatal->keterangan : 'Batal.');
            // }

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

            $referensi = ReferensiMobilejknBpjs::where('nobooking', $kodebooking)->first();

            // If BPJS returns message indicating the TaskId already exists, persist it locally
            $metaMessage = $responseData['metadata']['message'] ?? ($responseData['metadata'] ?? null);
            if (is_string($metaMessage) && (strpos($metaMessage, "TaskId={$taskid} sudah ada") !== false || strpos($metaMessage, "Ok") !== false)) {
                try {
                    $cekRecord = ReferensiMobilejknBpjsTaskid::where('no_rawat', $referensi ? $referensi->no_rawat : $kodebooking)
                        ->where('taskid', (string) $taskid)
                        ->first();
                    if (!$cekRecord) {
                        $taskRecord = ReferensiMobilejknBpjsTaskid::firstOrNew([
                            'no_rawat' => $referensi ? $referensi->no_rawat : $kodebooking,
                            'taskid' => (string) $taskid,
                            'waktu' =>  Carbon::createFromTimestampMs((int)$waktu)->toDateTimeString()
                        ]);

                        $taskRecord->save();
                    }
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
                    'metadata' => $responseData['metadata'] ?? null,
                    'batal' => $batal
                ];
            }

            return [
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'data' => $responseData,
                'metadata' => $responseData['metadata'] ?? null,
                'batal' => $batal
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
            // Extract the no_rawat/kodebooking from the payload
            $noRawat = $patientData['nobooking'] ?? $patientData['kodebooking'] ?? null;
            
            if (empty($noRawat)) {
                throw new InvalidArgumentException('No rawat/kodebooking is required');
            }
            
            // Call sendAddAntreanByNoRawat instead of direct API call
            $result = $this->sendAddAntreanByNoRawat($noRawat);
            
            // Map the response structure to match the original addAntrean format
            return [
                'success' => $result['status'] ?? false,
                'status_code' => $result['bpjs']['metadata']['code'] ?? 500,
                'data' => $result['bpjs']['data'] ?? [],
                'metadata' => $result['bpjs']['metadata'] ?? null
            ];
            
        } catch (Exception $e) {
            Log::error('Mobile JKN Add Antrean Error', [
                'kodebooking' => $patientData['nobooking'] ?? $patientData['kodebooking'] ?? 'unknown',
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
        $payload = [];
        try {
            $reg = RegPeriksa::where('no_rawat', $noRawat)->first();
            if (!$reg) {
                $reg = ReferensiMobilejknBpjs::select('reg_periksa.*')
                        ->where('no_rawat', $noRawat)
                        ->join('reg_periksa', 'reg_periksa.no_rawat', '=', 'referensi_mobilejkn_bpjs.no_rawat')
                        ->first();
            }

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

            Log::info("Mapping Poli: {$reg->kd_poli} => {$kodepoli}, Dokter: {$reg->kd_dokter} => {$kodedokter}");

            if ($jadwal) {
                $jamMulai = substr($jadwal->jam_mulai ?? '00:00:00', 0, 5);
                $jamSelesai = substr($jadwal->jam_selesai ?? $jadwal->jam_mulai ?? '00:00:00', 0, 5);
                $jampraktek = $jamMulai . '-' . $jamSelesai;
            } else {
                $jampraktek = '08:00-16:00';
            }

            $noRegInt = intval($reg->no_reg);
            $baseDatetime = Carbon::parse(explode(' ', $reg->tgl_registrasi)[0] . ' ' . ($jadwal->jam_mulai ?? '00:00:00'));
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
                $nomorreferensi = $this->fetchKontrol($pasien->no_peserta ?? '', $kodepoli, null, null);
            }

            Log::info("Nomor Referensi: {$nomorreferensi}, Jenis Kunjungan: {$jenisKunjungan}");

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
                'tanggalperiksa' => explode(' ', $reg->tgl_registrasi)[0],
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

            date_default_timezone_set('UTC');
            // Make the actual BPJS API call
            $timestamp = $this->getUtcTimestamp();
            $signature = base64_encode($this->generateSignature($timestamp));

            // Log the request
            Log::info('Mobile JKN Add Antrean Request', [
                'kodebooking' => $payload['kodebooking'],
                'timestamp' => $timestamp,
                'request_data' => $payload
            ]);

            // Make HTTP request
            $response = Http::withHeaders(headers: [
                'Content-Type' => 'application/json',
                'x-cons-id' => $this->consId,
                'x-timestamp' => $timestamp,
                'x-signature' => $signature,
                'user_key' => $this->userKey,
            ])->post($this->baseUrl . '/antrean/add', $payload);

            // Parse response
            $responseData = $response->json();

            // Log to BPJS log database
            $this->bpjsLogService->logRequest(
                $response->status(),
                json_encode($payload),
                json_encode($responseData),
                $this->baseUrl . '/antrean/add',
                'POST'
            );

            // Log the response
            Log::info('Mobile JKN Add Antrean Response', [
                'status' => $response->status(),
                'response' => $responseData
            ]);

            $status = $response->successful();
            $message = $status ? ($responseData['metadata']['message'] ?? 'Antrean sent') : ($responseData['metadata']['message'] ?? 'Mohon Maaf Gagal Mengirim Antrean, Silahkan Coba Lagi!');

            return [
                'status' => $status,
                'message' => $message,
                'data' => [],
                'payload' => $payload,
                'bpjs' => [
                    'metadata' => $responseData['metadata'] ?? null,
                    'data' => $responseData['data'] ?? null
                ]
            ];

        } catch (Exception $e) {
            Log::error('Error in sendAddAntreanByNoRawat: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => $e->getMessage(),
                'data' => [],
                'payload' => $payload,
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
            date_default_timezone_set('UTC');
            $timestamp = $this->getUtcTimestamp();
            $signature = base64_encode($this->generateSignature($timestamp));

            $url = rtrim($this->baseUrlVclaim, '/') . '/Rujukan/List/Peserta/' . $noPeserta;

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-cons-id' => $this->consId,
                'x-timestamp' => $timestamp,
                'x-signature' => $signature,
                'user_key' => $this->userKey,
            ])->get($url);

            $responseData = $response->json();
            Log::info('Rujukan ', ['response' => $response]);

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
                    
                    // Handle encrypted response
                    if (is_string($respContent)) {
                        try {
                            // Generate decryption key using consid + secretkey + timestamp
                            $decryptionKey = $this->generateDecryptionKey($timestamp);
                            
                            // Decrypt response using our generated key
                            $decryptedResponse = $this->stringDecrypt($decryptionKey, $respContent);
                            
                            // Decompress if needed
                            if ($decryptedResponse) {
                                $decompressedResponse = $this->decompress($decryptedResponse);
                                $decoded = json_decode($decompressedResponse, true);
                                
                                Log::info('Decrypted rujukan response', [
                                    'decrypted' => substr($decryptedResponse, 0, 100) . '...',
                                    'decompressed' => substr($decompressedResponse, 0, 100) . '...'
                                ]);
                                
                                if (is_array($decoded) && isset($decoded['rujukan'])) {
                                    $rujukanList = $decoded['rujukan'];
                                }
                            }
                        } catch (Throwable $decryptError) {
                            Log::error('Error decrypting BPJS rujukan response', [
                                'error' => $decryptError->getMessage()
                            ]);
                        }
                    } elseif (is_string($respContent)) {
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
            date_default_timezone_set('UTC');
            $timestamp = $this->getUtcTimestamp();
            $signature = base64_encode($this->generateSignature($timestamp));

            $url = rtrim($this->baseUrlVclaim, '/') . '/Rujukan/RS/List/Peserta/' . $noPeserta;

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-cons-id' => $this->consId,
                'x-timestamp' => $timestamp,
                'x-signature' => $signature,
                'user_key' => $this->userKey,
            ])->get($url);

            $responseData = $response->json();
            Log::info('Rujukan RS ', ['response' => $response]);

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
                    
                    // Handle encrypted response
                    if (is_string($respContent)) {
                        try {
                            // Generate decryption key using consid + secretkey + timestamp
                            $decryptionKey = $this->generateDecryptionKey($timestamp);
                            
                            // Decrypt response using our generated key
                            $decryptedResponse = $this->stringDecrypt($decryptionKey, $respContent);
                            
                            // Decompress if needed
                            if ($decryptedResponse) {
                                $decompressedResponse = $this->decompress($decryptedResponse);
                                $decoded = json_decode($decompressedResponse, true);
                                
                                Log::info('Decrypted rujukan RS response', [
                                    'decrypted' => substr($decryptedResponse, 0, 100) . '...',
                                    'decompressed' => substr($decompressedResponse, 0, 100) . '...'
                                ]);
                                
                                if (is_array($decoded) && isset($decoded['rujukan'])) {
                                    $rujukanList = $decoded['rujukan'];
                                }
                            }
                        } catch (Throwable $decryptError) {
                            Log::error('Error decrypting BPJS rujukan RS response', [
                                'error' => $decryptError->getMessage()
                            ]);
                        }
                    } elseif (is_string($respContent)) {
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

            date_default_timezone_set('UTC');
            $timestamp = $this->getUtcTimestamp();
            $signature = base64_encode($this->generateSignature($timestamp));

            $url = rtrim($this->baseUrlVclaim, '/') . "/RencanaKontrol/ListRencanaKontrol/Bulan/{$bulan}/Tahun/{$tahun}/Nokartu/{$noPeserta}/filter/{$filter}";

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-cons-id' => $this->consId,
                'x-timestamp' => $timestamp,
                'x-signature' => $signature,
                'user_key' => $this->userKey,
            ])->get($url);

            $responseData = $response->json();
            Log::info('Kontrol ', ['response' => $response]);
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
                $respContent = $responseData['response'] ?? null;
                $list = null;
                
                // Handle encrypted response
                if (is_string($respContent)) {
                    try {
                        // Generate decryption key using consid + secretkey + timestamp
                        $decryptionKey = $this->generateDecryptionKey($timestamp);
                        
                        // Decrypt response using our generated key
                        $decryptedResponse = $this->stringDecrypt($decryptionKey, $respContent);
                        
                        // Decompress if needed
                        if ($decryptedResponse) {
                            $decompressedResponse = $this->decompress($decryptedResponse);
                            $decoded = json_decode($decompressedResponse, true);
                            
                            Log::info('Decrypted kontrol response', [
                                'decrypted' => substr($decryptedResponse, 0, 100) . '...',
                                'decompressed' => substr($decompressedResponse, 0, 100) . '...'
                            ]);
                            
                            if (is_array($decoded) && isset($decoded['list'])) {
                                $list = $decoded['list'];
                            }
                        }
                    } catch (Throwable $decryptError) {
                        Log::error('Error decrypting BPJS kontrol response', [
                            'error' => $decryptError->getMessage()
                        ]);
                    }
                } else {
                    $list = $responseData['response']['list'] ?? null;
                }

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

    /**
     * Cancel an appointment in Mobile JKN API
     * 
     * @param string $kodeBooking Registration code / booking code
     * @param string $keterangan Reason for cancellation
     * @return array
     */
    public function batalAntrean(string $kodeBooking, string $keterangan = ''): array
    {
        try {
            if (empty($kodeBooking)) {
                throw new InvalidArgumentException('Kode booking is required');
            }

            // Default reason if not provided
            if (empty($keterangan)) {
                $keterangan = 'Pembatalan antrean oleh pasien/RS';
            }
            
            // Generate timestamp and signature
            $timestamp = $this->getUtcTimestamp();
            $signature = base64_encode($this->generateSignature($timestamp));

            // Prepare request data
            $requestData = [
                'kodebooking' => $kodeBooking,
                'keterangan' => $keterangan
            ];

            // Log the request
            Log::info('Mobile JKN Batal Antrean Request', [
                'kodebooking' => $kodeBooking,
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
            ])->post($this->baseUrl . '/antrean/batal', $requestData);

            // Parse response
            $responseData = $response->json();

            // Log to BPJS log database
            $this->bpjsLogService->logRequest(
                $response->status(),
                json_encode($requestData),
                json_encode($responseData),
                $this->baseUrl . '/antrean/batal',
                'POST'
            );

            // Log the response
            Log::info('Mobile JKN Batal Antrean Response', [
                'status' => $response->status(),
                'response' => $responseData
            ]);

            // If successful, save cancellation to local DB
            // if ($response->successful()) {
            //     try {
            //         // Get registration data
            //         $regPeriksa = RegPeriksa::where('no_rawat', $kodeBooking)->first();
                    
            //         // If not found as no_rawat, try finding via BPJS referral table
            //         if (!$regPeriksa) {
            //             $referensi = ReferensiMobilejknBpjs::where('nobooking', $kodeBooking)->first();
            //             if ($referensi) {
            //                 $regPeriksa = RegPeriksa::where('no_rawat', $referensi->no_rawat)->first();
            //             }
            //         }

            //         // Save cancellation record if reg data found
            //         if ($regPeriksa) {
            //             $batalRecord = new ReferensiMobilejknBpjsBatal([
            //                 'no_rawat' => $regPeriksa->no_rawat,
            //                 'nobooking' => $kodeBooking,
            //                 'status' => 'Batal',
            //                 'keterangan' => $keterangan,
            //                 'response' => json_encode($responseData),
            //                 'tanggal' => now()
            //             ]);
            //             $batalRecord->save();
            //         }
            //     } catch (Throwable $e) {
            //         Log::error('Failed to save ReferensiMobilejknBpjsBatal', ['error' => $e->getMessage(), 'kodebooking' => $kodeBooking]);
            //     }
            // }

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
                json_encode(['kodebooking' => $kodeBooking, 'keterangan' => $keterangan]),
                $e->getMessage(),
                $this->baseUrl . '/antrean/batal',
                'POST'
            );

            Log::error('Mobile JKN Batal Antrean Error', [
                'kodebooking' => $kodeBooking,
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
     * Wrapper method to cancel an appointment by No. Rawat
     * Handles finding the correct BPJS booking code
     * 
     * @param string $noRawat Hospital registration number
     * @param string $keterangan Reason for cancellation
     * @return array
     */
    public function batalAntreanByNoRawat(string $noRawat, string $keterangan = ''): array
    {
        try {
            // Find BPJS booking code
            $referensi = ReferensiMobilejknBpjs::where('no_rawat', $noRawat)->first();
            
            // If found in referensi table, use nobooking
            if ($referensi && $referensi->nobooking) {
                return $this->batalAntrean($referensi->nobooking, $keterangan);
            }
            
            // Otherwise use no_rawat as the booking code
            return $this->batalAntrean($noRawat, $keterangan);
            
        } catch (Exception $e) {
            Log::error('Error canceling appointment by no_rawat', [
                'no_rawat' => $noRawat,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status_code' => 500
            ];
        }
    }
}
