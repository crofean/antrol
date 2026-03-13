# Perbaikan Validasi Waktu - Panduan Deployment

## Status

✅ **Semua perbaikan diterapkan ke MobileJknService.php**

- Ekstraksi tanggal layanan yang ditingkatkan
- Validasi batas waktu yang ditingkatkan
- Deteksi korupsi untuk nilai waktu yang tidak valid
- Percobaan ulang otomatis dengan penyesuaian waktu
- Pembatasan percobaan ulang maksimal (mencegah loop tak terbatas)

## Apa yang Diperbaiki

### Deskripsi Masalah

ID Tugas diajukan ke API BPJS dengan waktu di luar batas tanggal layanan, menyebabkan kesalahan:

- "Waktu tidak valid (2026-03-14 00:44:08 WIB). Tanggal pelayanan untuk Kode Booking tersebut adalah (2026-03-13)"
- Waktu meluap ke hari berikutnya alih-alih tetap berada dalam 2026-03-13

### Penyebab Akar yang Diidentifikasi

1. **Luapan berurutan**: Setiap tugas menambah ~10 menit, menyebabkan waktu meluap melampaui 23:59:59
2. **Nilai database yang rusak**: Nilai negatif seperti `-62169984000000` menyebabkan kesalahan perhitungan
3. **Pelanggaran batas**: Waktu tugas harus semuanya dalam hari kalender yang sama (2026-03-13 00:00:00 hingga 23:59:59)

### Solusi yang Diimplementasikan

#### 1. **Ekstraksi Tanggal Layanan yang Diperkaya** (metode `getServiceDate`)

- Ekstraksi tanggal dari format kodebooking (YYYY/MM/DD/XXXX) sebagai metode UTAMA
- Fallback: Pencarian database melalui `ReferensiMobilejknBpjs` → `RegPeriksa.tgl_registrasi`
- Selalu mengembalikan tanggal di awal hari (00:00:00 UTC)
- Contoh: "2026/03/13/000317" → 2026-03-13 00:00:00

#### 2. **Validasi Batas Tanggal Layanan** (metode `ensureTimeWithinServiceDate`)

- Validasi semua waktu tetap berada dalam tanggal layanan
- Jika waktu SEBELUM awal hari → atur ke 00:00:00
- Jika waktu SETELAH akhir hari → **cap ke 23:59:59** (bukan hari berikutnya 00:00:00)
- Zona waktu UTC eksplisit untuk perbandingan yang konsisten
- Logging debug komprehensif

#### 3. **Celah 10 Menit dengan Penghormatan Batas** (metode `ensureTimeAfterPrevious`)

- Menegakkan celah minimum 10 menit antara tugas berurutan
- BARU: Menghormati batas tanggal layanan saat menghitung celah
- Jika celah 10 menit akan melampaui batas hari → cap ke 23:58:59
- Mencegah luapan ke hari berikutnya saat menambahkan buffer waktu

#### 4. **Deteksi Korupsi** (metode `updateTaskId`)

- Mendeteksi nilai waktu negatif (mis., -62169984000000)
- Mendeteksi waktu yang sangat lama (sebelum 1 Januari 2021)
- Mengganti nilai yang rusak dengan waktu saat ini (dibatasi oleh tanggal layanan)
- Mencatat peringatan untuk tinjauan manual

#### 5. **Pemulihan Kesalahan Otomatis**

- Kesalahan: "tidak boleh kurang atau sama dengan waktu sebelumnya"
    - Menambah 10+ menit dan percobaan ulang (menghormati batas hari)
- Kesalahan: "Waktu tidak valid"
    - Cap waktu ke 23:59:59 dan percobaan ulang
- Percobaan ulang maksimal 3 kali untuk mencegah loop tak terbatas

#### 6. **Persistensi Database**

- Menyimpan waktu yang sudah diperbaiki kembali ke database
- Proses di masa mendatang menggunakan waktu yang sudah diperbaiki sebagai titik awal
- Mencegah percobaan kesalahan berulang

## Alur Eksekusi

```
updateTaskId() dipanggil
    ↓
Ekstraksi tanggal layanan dari kodebooking (YYYY/MM/DD)
    ↓
Validasi waktu (deteksi nilai negatif/rusak)
    ↓
Pastikan dalam tanggal layanan (cap ke 23:59:59) ✅
    ↓
Dapatkan waktu tugas sebelumnya, tambahkan buffer 10 menit (menghormati batas) ✅
    ↓
Kirim ke API BPJS
    ↓
SUKSES? → Simpan ke DB ✅
    ↓
KESALAHAN "tidak boleh kurang..."? → Tambah 10min (dalam batas) → PERCOBAAN ULANG ✅
    ↓
KESALAHAN "Waktu tidak valid"? → Cap ke 23:59:59 → PERCOBAAN ULANG ✅
    ↓
Percobaan ulang terlampaui? → Kembalikan pesan kesalahan ✅
```

## Langkah-langkah Deployment

### Langkah 1: Komit Perubahan

```bash
cd /home/me/Developer/RSAM/antrol
git add -A
git commit -m "Perbaiki validasi batas tanggal layanan dan kesalahan validasi waktu

- Ekstraksi tanggal layanan yang ditingkatkan dari format kodebooking
- Implementasi validasi batas waktu yang ketat (cap ke 23:59:59)
- Penambahan deteksi korupsi untuk timestamp negatif/kuno
- Logika celah 10 menit yang ditingkatkan untuk menghormati batas hari
- Percobaan ulang otomatis dengan penyesuaian progresif
- Batasan percobaan ulang maksimal 3 untuk mencegah loop tak terbatas
- Logging debug komprehensif untuk troubleshooting"
```

### Langkah 2: Bersihkan Cache Aplikasi

```bash
cd /home/me/Developer/RSAM/antrol

# Bersihkan semua cache
php artisan cache:clear
php artisan config:clear
php artisan route:cache
php artisan view:cache

# Opsional: Bersihkan seluruh log penyimpanan jika diperlukan
# rm storage/logs/laravel.log
```

### Langkah 3: Restart Pekerja Antrian

```bash
# Jika queue:work sedang berjalan di terminal, hentikan dengan Ctrl+C
# Kemudian restart dengan:
php artisan queue:work --tries=1

# Atau di latar belakang:
php artisan queue:work > storage/logs/queue.log 2>&1 &
```

### Langkah 4: Verifikasi Log Dimulai Segar

```bash
# Tail log untuk memantau real-time
tail -f storage/logs/laravel.log
```

## Prosedur Pengujian

### Pengujian 1: Pantau Eksekusi Tugas yang Berhasil

1. Buka **Pemroses Batch**: `http://localhost:8000/command-runner`
2. Pilih rentang tanggal dengan pemesanan yang perlu pembaruan ID tugas
3. Periksa "View Logs" untuk memantau eksekusi
4. **Hasil yang Diharapkan**:
    - ✅ Semua waktu tetap berada dalam 2026-03-13 (atau tanggal yang dipilih)
    - ✅ Tidak ada kesalahan "Waktu tidak valid"
    - ✅ Celah 10 menit antara tugas berurutan dipertahankan
    - ✅ Respons API BPJS yang berhasil

### Pengujian 2: Periksa Log Real-Time

1. Buka **Log Viewer**: `http://localhost:8000/log-viewer`
2. Pantau secara real-time saat pemroses batch berjalan
3. **Cari indikator**:
    - ✅ Log "Time validation check" menunjukkan tanggal layanan yang benar
    - ✅ "Mobile JKN Task Update Response" dengan "Ok" atau "sudah ada"
    - ❌ TIDAK ada kesalahan "Waktu tidak valid"
    - ❌ TIDAK ada kesalahan "tidak boleh kurang atau sama dengan waktu sebelumnya"

### Pengujian 3: Pelacakan Eksekusi

1. Setelah batch berjalan, klik "Execution Details" di pemroses tugas
2. Periksa kemajuan setiap ID tugas:
    - ✅ Tugas 1-7 semuanya menunjukkan dalam 2026-03-13
    - ✅ Timestamp menunjukkan celah 10+ menit
    - ✅ Semua respons menunjukkan kesuksesan/sudah ada

### Pengujian 4: Verifikasi Pembaruan Database

```bash
# Periksa apakah waktu yang sudah diperbaiki disimpan
php artisan tinker

# Query ID tugas terbaru
>>> DB::table('referensi_mobilejkn_bpjs_taskid')
   ->whereDate('waktu', '2026-03-13')
   ->orderBy('created_at', 'desc')
   ->limit(20)
   ->get(['no_rawat', 'taskid', 'waktu']);

# Diharapkan: Semua nilai waktu harus pada 2026-03-13, dengan yang terakhir ~23:59:00
```

### Pengujian 5: Pemeriksaan Korupsi Database

```bash
# Periksa nilai yang rusak (harus TIDAK ADA setelah perbaikan)
php artisan tinker

>>> DB::table('referensi_mobilejkn_bpjs_taskid')
   ->whereRaw('UNIX_TIMESTAMP(waktu) < 0')
   ->orWhereRaw('YEAR(waktu) < 2021')
   ->count();

# Diharapkan: 0 (tidak ada record yang rusak)
```

## Rollback (jika diperlukan)

Jika masalah terjadi, rollback ke versi sebelumnya:

```bash
# Batalkan komit terakhir (pertahankan perubahan secara lokal)
git reset --soft HEAD~1

# Atau batalkan sepenuhnya (buang perubahan)
git reset --hard HEAD~1

# Restart layanan
php artisan cache:clear
php artisan queue:work
```

## Indikator Kesuksesan

✅ **Semua yang berikut harus benar setelah perbaikan**:

- [ ] Tidak ada kesalahan "Waktu tidak valid" dalam log
- [ ] Tidak ada kesalahan "tidak boleh kurang atau sama dengan waktu sebelumnya" pada percobaan pertama
- [ ] Semua waktu tugas tetap berada dalam tanggal layanan (2026-03-13 misalnya)
- [ ] Respons API BPJS yang berhasil (status 200, pesan "Ok" atau "sudah ada")
- [ ] Database menunjukkan waktu yang diperbarui dalam batas tanggal layanan
- [ ] Tidak ada nilai waktu negatif atau rusak dalam database

## Pemantauan Setelah Deployment

Pantau selama 24 jam untuk memastikan:

1. Semua batch runs selesai berhasil
2. Tidak perlu rollback
3. Log historis menunjukkan pola konsistensi kesuksesan

File log kunci: `/home/me/Developer/RSAM/antrol/storage/logs/laravel.log`

## Dukungan / Debugging

Jika masalah terus berlanjut:

1. **Periksa Log**:

    ```bash
    tail -n 100 storage/logs/laravel.log | grep -i "waktu\|error"
    ```

2. **Verifikasi Ekstraksi Tanggal Layanan**:

    ```bash
    php artisan tinker
    >>> $service = new \App\Services\MobileJknService(new \App\Services\BpjsLogService);
    >>> $date = $service->getServiceDate("2026/03/13/000317");
    >>> print_r($date);
    ```

3. **Periksa Sisa Data yang Rusak**:

    ```bash
    php artisan tinker
    >>> DB::table('referensi_mobilejkn_bpjs_taskid')
       ->whereRaw('UNIX_TIMESTAMP(waktu) < 0 OR YEAR(waktu) < 2021')
       ->count();
    ```

4. **Penyesuaian Waktu Manual** (jika diperlukan):
    ```bash
    # Di Tinker:
    >>> DB::table('referensi_mobilejkn_bpjs_taskid')
       ->where('no_rawat', 'XXXX')
       ->where('taskid', '5')
       ->update(['waktu' => \Carbon\Carbon::now()]);
    ```

## Pertanyaan?

Tinjau perubahan di: `app/Services/MobileJknService.php`

- Baris 294-327: Metode pembantu dan ekstraksi tanggal layanan
- Baris 327-420: Metode validasi batas waktu
- Baris 496-800: Metode updateTaskId utama dengan penanganan kesalahan
