<?php

namespace App\Services;

use App\Models\RegPeriksa;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RegPeriksaService
{
    /**
     * Get patients registered for today with BPJS insurance
     *
     * @param string|null $date Optional date in Y-m-d format, defaults to today
     * @return Collection
     */
    public function getTodayBpjsPatients(?string $date = null): Collection
    {
        $date = $date ?? Carbon::today()->format('Y-m-d');

        return RegPeriksa::with(['referensiMobilejknBpjs', 'bridgingSep', 'referensiMobilejknBpjsTaskid'])
            ->where('tgl_registrasi', $date)
            ->where('kd_pj', 'BPJ')
            ->orderBy('jam_reg', 'asc')
            ->get();
    }

    /**
     * Get patients by date range and insurance type
     *
     * @param string $startDate
     * @param string $endDate
     * @param string|null $kdPj Insurance code, null for all
     * @return Collection
     */
    public function getPatientsByDateRange(string $startDate, string $endDate, ?string $kdPj = null): Collection
    {
        $query = RegPeriksa::with(['referensiMobilejknBpjs', 'bridgingSep', 'referensiMobilejknBpjsTaskid'])
            ->whereBetween('tgl_registrasi', [$startDate, $endDate])
            ->orderBy('tgl_registrasi', 'desc')
            ->orderBy('jam_reg', 'asc');

        if ($kdPj) {
            $query->where('kd_pj', $kdPj);
        }

        return $query->get();
    }

    /**
     * Get patient statistics for today
     *
     * @param string|null $date
     * @return array
     */
    public function getTodayStatistics(?string $date = null): array
    {
        $date = $date ?? Carbon::today()->format('Y-m-d');

        $totalPatients = RegPeriksa::where('tgl_registrasi', $date)->count();
        $bpjsPatients = RegPeriksa::where('tgl_registrasi', $date)->where('kd_pj', 'BPJ')->count();
        $otherPatients = $totalPatients - $bpjsPatients;

        $statusCounts = RegPeriksa::where('tgl_registrasi', $date)
            ->select('stts', DB::raw('count(*) as count'))
            ->groupBy('stts')
            ->pluck('count', 'stts')
            ->toArray();

        return [
            'date' => $date,
            'total_patients' => $totalPatients,
            'bpjs_patients' => $bpjsPatients,
            'other_patients' => $otherPatients,
            'status_breakdown' => $statusCounts,
        ];
    }

    /**
     * Get patient by registration number
     *
     * @param string $noRawat
     * @return RegPeriksa|null
     */
    public function getPatientByNoRawat(string $noRawat): ?RegPeriksa
    {
        return RegPeriksa::with(['referensiMobilejknBpjs', 'bridgingSep'])
            ->where('no_rawat', $noRawat)
            ->first();
    }

    /**
     * Get patients by status
     *
     * @param string $status
     * @param string|null $date
     * @return Collection
     */
    public function getPatientsByStatus(string $status, ?string $date = null): Collection
    {
        $query = RegPeriksa::with(['referensiMobilejknBpjs', 'bridgingSep'])
            ->where('stts', $status);

        if ($date) {
            $query->where('tgl_registrasi', $date);
        }

        return $query->orderBy('jam_reg', 'asc')->get();
    }

    /**
     * Get patients by doctor
     *
     * @param string $kdDokter
     * @param string|null $date
     * @return Collection
     */
    public function getPatientsByDoctor(string $kdDokter, ?string $date = null): Collection
    {
        $query = RegPeriksa::with(['referensiMobilejknBpjs', 'bridgingSep'])
            ->where('kd_dokter', $kdDokter);

        if ($date) {
            $query->where('tgl_registrasi', $date);
        }

        return $query->orderBy('jam_reg', 'asc')->get();
    }

    /**
     * Get patients by polyclinic
     *
     * @param string $kdPoli
     * @param string|null $date
     * @return Collection
     */
    public function getPatientsByPolyclinic(string $kdPoli, ?string $date = null): Collection
    {
        $query = RegPeriksa::with(['referensiMobilejknBpjs', 'bridgingSep'])
            ->where('kd_poli', $kdPoli);

        if ($date) {
            $query->where('tgl_registrasi', $date);
        }

        return $query->orderBy('jam_reg', 'asc')->get();
    }

    /**
     * Get patients with pagination and filters
     *
     * @param array $filters
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPatientsWithFilters(array $filters = [], int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = RegPeriksa::with(['referensiMobilejknBpjs', 'bridgingSep', 'referensiMobilejknBpjsTaskid', 'pasien', 'dokter', 'poliklinik', 'penjab']);

        // Apply date filter
        if (isset($filters['date'])) {
            $query->where('tgl_registrasi', $filters['date']);
        }

        // Apply insurance filter
        if (isset($filters['kd_pj'])) {
            $query->where('kd_pj', $filters['kd_pj']);
        }

        // Apply rekam medis filter
        if (isset($filters['no_rkm_medis']) && !empty($filters['no_rkm_medis'])) {
            $query->where('no_rkm_medis', 'like', '%' . $filters['no_rkm_medis'] . '%');
        }

        // Apply no rawat filter
        if (isset($filters['no_rawat']) && !empty($filters['no_rawat'])) {
            $query->where('no_rawat', 'like', '%' . $filters['no_rawat'] . '%');
        }

        // Apply no kartu filter (from referensi_mobilejkn_bpjs)
        if (isset($filters['no_kartu']) && !empty($filters['no_kartu'])) {
            $query->whereHas('referensiMobilejknBpjs', function($q) use ($filters) {
                $q->where('nomorkartu', 'like', '%' . $filters['no_kartu'] . '%');
            });
        }

        // Apply SEP filter (from bridging_sep)
        if (isset($filters['no_sep']) && !empty($filters['no_sep'])) {
            $query->whereHas('bridgingSep', function($q) use ($filters) {
                $q->where('no_sep', 'like', '%' . $filters['no_sep'] . '%');
            });
        }

        // Apply poli filter
        if (isset($filters['kd_poli']) && !empty($filters['kd_poli'])) {
            $query->where('kd_poli', 'like', '%' . $filters['kd_poli'] . '%');
        }

        // Apply status filter
        if (isset($filters['status']) && !empty($filters['status'])) {
            $query->where('stts', $filters['status']);
        }

        // Apply doctor filter
        if (isset($filters['kd_dokter']) && !empty($filters['kd_dokter'])) {
            $query->where('kd_dokter', 'like', '%' . $filters['kd_dokter'] . '%');
        }

        return $query->orderBy('tgl_registrasi', 'desc')
                    ->orderBy('jam_reg', 'asc')
                    ->paginate($perPage);
    }

    /**
     * Get filtered patients for today with BPJS
     *
     * @param array $filters
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getFilteredBpjsPatients(array $filters = [], int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $date = $filters['date'] ?? Carbon::today()->format('Y-m-d');
        $filters['date'] = $date;
        $filters['kd_pj'] = 'BPJ';

        return $this->getPatientsWithFilters($filters, $perPage);
    }
}
