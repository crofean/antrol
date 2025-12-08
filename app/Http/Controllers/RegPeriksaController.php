<?php

namespace App\Http\Controllers;

use App\Services\MobileJknService;
use App\Services\RegPeriksaService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Carbon\Carbon;

class RegPeriksaController extends Controller
{
    protected $regPeriksaService;
    protected $mobileJknService;

    public function __construct(RegPeriksaService $regPeriksaService, MobileJknService $mobileJknService)
    {
        $this->regPeriksaService = $regPeriksaService;
        $this->mobileJknService = $mobileJknService;
    }

    /**
     * Display today's BPJS patients with filters and pagination
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $filters = $request->only([
            'date', 'no_rkm_medis', 'no_rawat', 'no_kartu', 'no_sep', 'kd_poli', 'nobooking', 'status', 'kd_dokter'
        ]);

        $perPage = $request->get('per_page', 15);

        // Set default date if not provided
        if (!isset($filters['date'])) {
            $filters['date'] = Carbon::today()->format('Y-m-d');
        }

        $patients = $this->regPeriksaService->getFilteredBpjsPatients($filters, $perPage);
        $statistics = $this->regPeriksaService->getTodayStatistics($filters['date']);

        return view('regperiksa.index', compact('patients', 'statistics', 'filters', 'perPage'));
    }

    /**
     * Get filtered patients as JSON with pagination
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getFilteredPatients(Request $request): JsonResponse
    {
        $filters = $request->only([
            'date', 'kd_pj', 'no_rkm_medis', 'no_rawat', 'no_kartu', 'no_sep', 'kd_poli', 'nobooking', 'status', 'kd_dokter'
        ]);

        $perPage = $request->get('per_page', 15);

        // Set default date if not provided
        if (!isset($filters['date'])) {
            $filters['date'] = Carbon::today()->format('Y-m-d');
        }

        $patients = $this->regPeriksaService->getPatientsWithFilters($filters, $perPage);

        return response()->json([
            'success' => true,
            'data' => $patients->items(),
            'pagination' => [
                'current_page' => $patients->currentPage(),
                'last_page' => $patients->lastPage(),
                'per_page' => $patients->perPage(),
                'total' => $patients->total(),
                'from' => $patients->firstItem(),
                'to' => $patients->lastItem(),
            ],
            'filters' => $filters
        ]);
    }

    /**
     * Get today's BPJS patients as JSON
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getTodayBpjsPatients(Request $request): JsonResponse
    {
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        $patients = $this->regPeriksaService->getTodayBpjsPatients($date);

        return response()->json([
            'success' => true,
            'data' => $patients,
            'date' => $date
        ]);
    }

    /**
     * Get patients by date range
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getPatientsByDateRange(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'kd_pj' => 'nullable|string'
        ]);

        $patients = $this->regPeriksaService->getPatientsByDateRange(
            $request->start_date,
            $request->end_date,
            $request->kd_pj
        );

        return response()->json([
            'success' => true,
            'data' => $patients
        ]);
    }

    /**
     * Get patient statistics
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getStatistics(Request $request): JsonResponse
    {
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        $statistics = $this->regPeriksaService->getTodayStatistics($date);

        return response()->json([
            'success' => true,
            'data' => $statistics
        ]);
    }

    /**
     * Get patient by registration number
     *
     * @param string $noRawat
     * @return JsonResponse
     */
    public function getPatient(): JsonResponse
    {
        $noRawat = request()->get('no_rawat');
        $patient = $this->regPeriksaService->getPatientByNoRawat($noRawat);

        $taskList = $this->mobileJknService->getTaskIdRecord($noRawat);

        // if ($include === 'task') {
        $task = $this->mobileJknService->getPatientDataForTaskId($noRawat);
        // }

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $patient,
            'task' => $task ?? null,
            'task_list' => $taskList ?? null
        ]);
    }

    /**
     * Get patients by status
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getPatientsByStatus(Request $request): JsonResponse
    {
        $request->validate([
            'status' => 'required|string',
            'date' => 'nullable|date'
        ]);

        $patients = $this->regPeriksaService->getPatientsByStatus(
            $request->status,
            $request->date
        );

        return response()->json([
            'success' => true,
            'data' => $patients
        ]);
    }

    /**
     * Get patients by doctor
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getPatientsByDoctor(Request $request): JsonResponse
    {
        $request->validate([
            'kd_dokter' => 'required|string',
            'date' => 'nullable|date'
        ]);

        $patients = $this->regPeriksaService->getPatientsByDoctor(
            $request->kd_dokter,
            $request->date
        );

        return response()->json([
            'success' => true,
            'data' => $patients
        ]);
    }

    /**
     * Get patients by polyclinic
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getPatientsByPolyclinic(Request $request): JsonResponse
    {
        $request->validate([
            'kd_poli' => 'required|string',
            'date' => 'nullable|date'
        ]);

        $patients = $this->regPeriksaService->getPatientsByPolyclinic(
            $request->kd_poli,
            $request->date
        );

        return response()->json([
            'success' => true,
            'data' => $patients
        ]);
    }
}
