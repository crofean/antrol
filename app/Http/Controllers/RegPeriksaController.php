<?php

namespace App\Http\Controllers;

use App\Services\MobileJknService;
use App\Services\RegPeriksaService;
use Illuminate\Http\Request;
use App\Models\ReferensiMobilejknBpjs;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Carbon\Carbon;
use App\Http\Requests\DateRangeRequest;
use App\Http\Requests\GetPatientsByStatusRequest;
use App\Http\Requests\GetPatientsByDoctorRequest;
use App\Http\Requests\GetPatientsByPolyclinicRequest;
use App\Http\Resources\PatientResource;
use App\Http\Resources\ApiSuccessResource;

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
            'date', 'no_rkm_medis', 'no_rawat', 'no_kartu', 'no_sep', 'kd_poli', 'status', 'kd_dokter'
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
            'date', 'kd_pj', 'no_rkm_medis', 'no_rawat', 'no_kartu', 'no_sep', 'kd_poli', 'status', 'kd_dokter'
        ]);

        $perPage = $request->get('per_page', 15);

        // Set default date if not provided
        if (!isset($filters['date'])) {
            $filters['date'] = Carbon::today()->format('Y-m-d');
        }

        $patients = $this->regPeriksaService->getPatientsWithFilters($filters, $perPage);

        return response()->json([
            'success' => true,
            'data' => PatientResource::collection($patients->items()),
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
     * @return ApiSuccessResource
     */
    public function getTodayBpjsPatients(Request $request): ApiSuccessResource
    {
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        $patients = $this->regPeriksaService->getTodayBpjsPatients($date);

        return (new ApiSuccessResource(PatientResource::collection($patients)))
            ->additional(['date' => $date]);
    }

    /**
     * Get patients by date range
     *
     * @param DateRangeRequest $request
     * @return ApiSuccessResource
     */
    public function getPatientsByDateRange(DateRangeRequest $request): ApiSuccessResource
    {
        $patients = $this->regPeriksaService->getPatientsByDateRange(
            $request->start_date,
            $request->end_date,
            $request->kd_pj
        );

        return new ApiSuccessResource(PatientResource::collection($patients));
    }

    /**
     * Get patient statistics
     *
     * @param Request $request
     * @return ApiSuccessResource
     */
    public function getStatistics(Request $request): ApiSuccessResource
    {
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        $statistics = $this->regPeriksaService->getTodayStatistics($date);

        return new ApiSuccessResource($statistics);
    }

    /**
     * Get patient by registration number
     *
     * @return ApiSuccessResource|JsonResponse
     */
    public function getPatient(): ApiSuccessResource|JsonResponse
    {
        $noRawat = request()->get('no_rawat');
        $patient = $this->regPeriksaService->getPatientByNoRawat($noRawat);

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient not found'
            ], 404);
        }

        $ref = ReferensiMobilejknBpjs::where('no_rawat', $noRawat)->where('status', 'Checkin')->first();

        $taskList = $this->mobileJknService->getTaskIdRecord($noRawat);

        $task = $this->mobileJknService->getPatientDataForTaskId($noRawat);

        $refArray = $ref ? $ref->toArray() : null;

        return (new ApiSuccessResource(
            array_merge($patient->toArray(), ['referensi_mobilejkn_bpjs' => $refArray])
        ))->additional([
            'task' => $task ?? null,
            'task_list' => $taskList ?? null
        ]);
    }

    /**
     * Get patients by status
     *
     * @param GetPatientsByStatusRequest $request
     * @return ApiSuccessResource
     */
    public function getPatientsByStatus(GetPatientsByStatusRequest $request): ApiSuccessResource
    {
        $patients = $this->regPeriksaService->getPatientsByStatus(
            $request->status,
            $request->date
        );

        return new ApiSuccessResource(PatientResource::collection($patients));
    }

    /**
     * Get patients by doctor
     *
     * @param GetPatientsByDoctorRequest $request
     * @return ApiSuccessResource
     */
    public function getPatientsByDoctor(GetPatientsByDoctorRequest $request): ApiSuccessResource
    {
        $patients = $this->regPeriksaService->getPatientsByDoctor(
            $request->kd_dokter,
            $request->date
        );

        return new ApiSuccessResource(PatientResource::collection($patients));
    }

    /**
     * Get patients by polyclinic
     *
     * @param GetPatientsByPolyclinicRequest $request
     * @return ApiSuccessResource
     */
    public function getPatientsByPolyclinic(GetPatientsByPolyclinicRequest $request): ApiSuccessResource
    {
        $patients = $this->regPeriksaService->getPatientsByPolyclinic(
            $request->kd_poli,
            $request->date
        );

        return new ApiSuccessResource(PatientResource::collection($patients));
    }
}
