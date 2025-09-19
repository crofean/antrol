## Feat

Add check riwayat HD

## BPJS Task ID Sender

This application includes a scheduled command for automatically sending BPJS task IDs for patients based on their registration data.

### Features

-   **Automatic Antrean Addition**: Adds patients to BPJS queue system
-   **Task ID Management**: Sends task IDs (3, 4, 5, 6, 7) based on database timestamps
-   **Flexible Filtering**: Filter by date range, BPJS payer code, and exclude specific polyclinics
-   **Live Console Output**: Real-time progress tracking and statistics
-   **Dry Run Mode**: Test the process without actually sending data

### Task ID Mapping

-   **Task ID 3**: Registration time (from `referensi_mobilejkn_bpjs.validasi` or `reg_periksa.jam_reg`)
-   **Task ID 4**: Medical examination start (from `pemeriksaan_ralan` where `nip` is in `petugas`)
-   **Task ID 5**: Doctor examination (from `pemeriksaan_ralan` where `nip` is in `dokter`)
-   **Task ID 6**: Prescription time (from `resep_obat.jam`)
-   **Task ID 7**: Prescription handover (from `resep_obat.jam_penyerahan`)

### Usage

#### Manual Execution

```bash
# Process today's patients
php artisan bpjs:send-task-ids

# Process patients for specific date range
php artisan bpjs:send-task-ids --date-from=2024-01-01 --date-to=2024-01-31

# Dry run mode (no actual API calls)
php artisan bpjs:send-task-ids --dry-run
```

#### Scheduled Execution

The command is automatically scheduled to run every minute. You can modify the schedule in `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule): void
{
    $schedule->command('bpjs:send-task-ids')
        ->everyMinute() // Change frequency as needed
        ->withoutOverlapping()
        ->runInBackground();
}
```

### Configuration

Add these environment variables to your `.env` file:

```env
# BPJS Mobile JKN API Configuration
MOBILEJKN_BASE_URL=https://apijkn.bpjs-kesehatan.go.id/antreanrs/bpjs
MOBILEJKN_CONS_ID=your_cons_id_here
MOBILEJKN_USER_KEY=your_user_key_here
MOBILEJKN_SECRET_KEY=your_secret_key_here

# BPJS Task ID Sender Configuration
BPJS_KD_PJ=BPJ
BPJS_EXCLUDE_POLI=POL001,POL002
```

### Console Output Example

```
🚀 Starting BPJS Task ID Sender...

📅 Processing patients from 2024-01-15 to 2024-01-15
🏥 BPJS Payer Code: BPJ
🚫 Excluding Poli: POL001, POL002

📊 Found 5 patients to process

🏥 Processing patient: 202401150001 (Booking: BK20240115001)
✅ Antrean added successfully for: BK20240115001
✅ Task ID 3 sent successfully for: BK20240115001
✅ Task ID 4 sent successfully for: BK20240115001
✅ Task ID 5 sent successfully for: BK20240115001
✅ Task ID 6 sent successfully for: BK20240115001
✅ Task ID 7 sent successfully for: BK20240115001

📈 Processing Statistics:
+--------------------+-------+
| Metric            | Count |
+--------------------+-------+
| Patients Processed| 5     |
| Antrean Success   | 5     |
| Antrean Failed    | 0     |
| Task ID Success   | 25    |
| Task ID Failed    | 0     |
+--------------------+-------+

✅ BPJS Task ID processing completed!
```
