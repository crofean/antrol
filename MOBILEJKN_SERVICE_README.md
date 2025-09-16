# Mobile JKN Service

This service provides functionality to update task IDs for Mobile JKN (Jaminan Kesehatan Nasional) bookings with automatic timestamp retrieval from database.

## Configuration

Add the following to your `.env` file:

```env
MOBILEJKN_BASE_URL=https://api.mobilejkn.com
MOBILEJKN_CONS_ID=your_cons_id_here
MOBILEJKN_USER_KEY=your_user_key_here
MOBILEJKN_SECRET_KEY=your_secret_key_here
```

## Task ID Timestamp Sources

The service automatically retrieves timestamps from the database based on task ID:

-   **Task ID 3**: Gets timestamp from `referensi_mobilejkn_bpjs.validasi` field, or falls back to `reg_periksa.jam_reg`
-   **Task ID 4**: Gets timestamp from `pemeriksaan_ralan.jam_rawat` where `nip` exists in `petugas` table
-   **Task ID 5**: Gets timestamp from `pemeriksaan_ralan.jam_rawat` where `nip` exists in `dokter` table
-   **Task ID 6**: Gets timestamp from `resep_obat.jam` field
-   **Task ID 7**: Gets timestamp from `resep_obat.jam_penyerahan` field

## Usage

### Using the Service Directly

```php
use App\Services\MobileJknService;

$service = new MobileJknService();

// Update task ID with timestamp from database (recommended)
$result = $service->updateTaskIdFromDatabase('BOOKING123', 3);

// Update task ID with specific timestamp
$result = $service->updateTaskId('BOOKING123', 3, '1699876543210');

// Update task ID with current timestamp
$result = $service->updateTaskIdNow('BOOKING123', 3);

// Batch update multiple bookings
$updates = [
    ['kodebooking' => 'BOOKING123', 'taskid' => 3], // Will use database timestamp
    ['kodebooking' => 'BOOKING124', 'taskid' => 4, 'waktu' => '1699876543210'] // Custom timestamp
];
$results = $service->batchUpdateTaskIds($updates);
```

### Using the Controller (API Endpoints)

#### Update Task ID from Database (Recommended)

```http
POST /api/mobilejkn/update-task-id-from-db
Content-Type: application/json

{
    "kodebooking": "BOOKING123",
    "taskid": 3
}
```

#### Update Task ID with Specific Timestamp

```http
POST /api/mobilejkn/update-task-id
Content-Type: application/json

{
    "kodebooking": "BOOKING123",
    "taskid": 3,
    "waktu": "1699876543210"
}
```

#### Update Task ID with Current Time

```http
POST /api/mobilejkn/update-task-id-now
Content-Type: application/json

{
    "kodebooking": "BOOKING123",
    "taskid": 3
}
```

#### Batch Update Task IDs

```http
POST /api/mobilejkn/batch-update-task-ids
Content-Type: application/json

{
    "updates": [
        {
            "kodebooking": "BOOKING123",
            "taskid": 3
        },
        {
            "kodebooking": "BOOKING124",
            "taskid": 4,
            "waktu": "1699876543210"
        }
    ]
}
```

## Task ID Values

Valid task IDs are:

-   1: Task 1
-   2: Task 2
-   3: Task 3
-   4: Task 4
-   5: Task 5
-   6: Task 6
-   7: Task 7
-   99: Final Task

## Response Format

All methods return a JSON response with the following structure:

```json
{
    "success": true,
    "status_code": 200,
    "data": {...},
    "metadata": {...}
}
```

On error:

```json
{
    "success": false,
    "error": "Error message",
    "status_code": 500
}
```

## Error Handling

The service includes comprehensive error handling and logging. All requests and responses are logged for debugging purposes.
