# RegPeriksa Service & UI

This module provides functionality to manage and display patient registration data from the `reg_periksa` table, with a focus on BPJS patients.

## Features

-   **Today's BPJS Patients View**: Display all BPJS patients registered for the current day
-   **Advanced Filtering**: Filter by rekam medis, no rawat, no kartu, SEP, poli, status, and doctor
-   **Pagination**: Navigate through large datasets with customizable page sizes
-   **Statistics Dashboard**: Show patient counts and status breakdowns
-   **Date Navigation**: Navigate between different dates
-   **Responsive Design**: Mobile-friendly UI using TailwindCSS
-   **Auto-refresh**: Automatically refresh data every 5 minutes
-   **API Endpoints**: RESTful API for external integrations

## Filtering Options

The system supports comprehensive filtering by:

-   **Rekam Medis**: Patient medical record number
-   **No Rawat**: Registration number
-   **No Kartu**: BPJS card number (from Mobile JKN)
-   **SEP**: Surat Elegibilitas Pasien number
-   **Poli**: Polyclinic code
-   **Status**: Patient status (Belum/Sudah/Batal)
-   **Doctor**: Doctor code
-   **Date**: Registration date
-   **Insurance**: Insurance type (default: BPJ)

## Pagination

-   **Page Sizes**: 10, 15, 25, or 50 items per page
-   **Navigation**: Previous/Next buttons and page numbers
-   **Info Display**: Shows current page range and total results

## Web Interface

### Main View

Access the main patient list at: `/regperiksa`

Features:

-   Date navigation (Previous/Next/Today)
-   Statistics cards showing patient counts
-   Detailed patient table with:
    -   Registration number and time
    -   Patient information
    -   Doctor and polyclinic details
    -   Status indicators
    -   Mobile JKN booking reference
    -   SEP (Surat Elegibilitas Pasien) status

### Navigation

-   Use Previous/Next buttons to navigate between dates
-   Click "Today" to return to current date
-   All data automatically filters for BPJS patients (`kd_pj = 'BPJ'`)

## API Endpoints

### Get Today's BPJS Patients

```http
GET /api/regperiksa/today-bpjs?date=2025-09-16
```

### Get Statistics

```http
GET /api/regperiksa/statistics?date=2025-09-16
```

### Get Patient by Registration Number

```http
GET /api/regperiksa/patient/{no_rawat}
```

### Get Patients by Date Range

```http
GET /api/regperiksa/date-range?start_date=2025-09-01&end_date=2025-09-16&kd_pj=BPJ
```

### Get Patients by Status

```http
GET /api/regperiksa/by-status?status=Belum&date=2025-09-16
```

### Get Patients by Doctor

```http
GET /api/regperiksa/by-doctor?kd_dokter=DR001&date=2025-09-16
```

### Get Patients by Polyclinic

```http
GET /api/regperiksa/by-polyclinic?kd_poli=UMU&date=2025-09-16
```

### Get Filtered Patients with Pagination

```http
GET /api/regperiksa/filtered?date=2025-09-16&no_rkm_medis=123&no_rawat=2025&per_page=25
```

**Query Parameters:**

-   `date`: Filter by registration date (Y-m-d)
-   `kd_pj`: Filter by insurance type
-   `no_rkm_medis`: Filter by medical record number
-   `no_rawat`: Filter by registration number
-   `no_kartu`: Filter by BPJS card number
-   `no_sep`: Filter by SEP number
-   `kd_poli`: Filter by polyclinic code
-   `status`: Filter by patient status
-   `kd_dokter`: Filter by doctor code
-   `per_page`: Number of results per page (10, 15, 25, 50)

**Response:**

```json
{
    "success": true,
    "data": [...],
    "pagination": {
        "current_page": 1,
        "last_page": 5,
        "per_page": 25,
        "total": 120,
        "from": 1,
        "to": 25
    },
    "filters": {...}
}
```

## Service Methods

The `RegPeriksaService` provides the following methods:

-   `getTodayBpjsPatients($date)` - Get BPJS patients for specific date
-   `getPatientsByDateRange($startDate, $endDate, $kdPj)` - Get patients in date range
-   `getTodayStatistics($date)` - Get statistics for specific date
-   `getPatientByNoRawat($noRawat)` - Get single patient by registration number
-   `getPatientsByStatus($status, $date)` - Get patients by status
-   `getPatientsByDoctor($kdDokter, $date)` - Get patients by doctor
-   `getPatientsByPolyclinic($kdPoli, $date)` - Get patients by polyclinic
-   `getPatientsWithFilters($filters, $perPage)` - Get patients with advanced filtering and pagination
-   `getFilteredBpjsPatients($filters, $perPage)` - Get filtered BPJS patients with pagination

## Usage Examples

### Using the Service Directly

```php
use App\Services\RegPeriksaService;

$service = new RegPeriksaService();

// Get today's BPJS patients
$patients = $service->getTodayBpjsPatients();

// Get statistics
$stats = $service->getTodayStatistics();

// Get patient by registration number
$patient = $service->getPatientByNoRawat('2025/09/16/000001');
```

### Using the Controller

```php
use App\Http\Controllers\RegPeriksaController;

$controller = new RegPeriksaController(new RegPeriksaService());

// Get patients as JSON
$patients = $controller->getTodayBpjsPatients(request());
```

## Styling

The UI uses TailwindCSS via CDN link for:

-   Responsive grid layouts
-   Modern card designs
-   Status badges with colors
-   Hover effects and transitions
-   Font Awesome icons

## Auto-refresh

The web interface automatically refreshes every 5 minutes to show the latest data. This can be disabled by removing the JavaScript at the bottom of the view.

## Dependencies

-   Laravel Framework
-   Carbon (for date handling)
-   TailwindCSS (via CDN)
-   Font Awesome (via CDN)

## Configuration

No additional configuration is required. The service uses the existing database connection and model relationships.
