<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MobileJknController;
use App\Http\Controllers\RegPeriksaController;
use App\Http\Controllers\BpjsLogController;

Route::get('/', function () {
    return view('welcome');
});

// Mobile JKN API Routes
Route::prefix('api/mobilejkn')->group(function () {
    Route::post('/update-task-id', [MobileJknController::class, 'updateTaskId']);
    Route::post('/update-task-id-from-db', [MobileJknController::class, 'updateTaskIdFromDatabase']);
    Route::post('/update-task-id-now', [MobileJknController::class, 'updateTaskIdNow']);
    Route::post('/batch-update-task-ids', [MobileJknController::class, 'batchUpdateTaskIds']);
});

// RegPeriksa Routes
Route::prefix('regperiksa')->group(function () {
    Route::get('/', [RegPeriksaController::class, 'index'])->name('regperiksa.index');
});

// RegPeriksa API Routes
Route::prefix('api/regperiksa')->group(function () {
    Route::get('/today-bpjs', [RegPeriksaController::class, 'getTodayBpjsPatients']);
    Route::get('/filtered', [RegPeriksaController::class, 'getFilteredPatients']);
    Route::get('/statistics', [RegPeriksaController::class, 'getStatistics']);
    Route::get('/patient/{noRawat}', [RegPeriksaController::class, 'getPatient']);
    Route::get('/by-status', [RegPeriksaController::class, 'getPatientsByStatus']);
    Route::get('/by-doctor', [RegPeriksaController::class, 'getPatientsByDoctor']);
    Route::get('/by-polyclinic', [RegPeriksaController::class, 'getPatientsByPolyclinic']);
    Route::get('/date-range', [RegPeriksaController::class, 'getPatientsByDateRange']);
});

// BPJS Log Routes
Route::prefix('bpjs-logs')->group(function () {
    Route::get('/', [BpjsLogController::class, 'index'])->name('bpjs-logs.index');
});

// BPJS Log API Routes
Route::prefix('api/bpjs-logs')->group(function () {
    Route::get('/', [BpjsLogController::class, 'getLogs']);
    Route::get('/by-date-range', [BpjsLogController::class, 'getLogsByDateRange']);
    Route::get('/by-code', [BpjsLogController::class, 'getLogsByCode']);
    Route::get('/by-task', [BpjsLogController::class, 'getLogsByTask']);
});
