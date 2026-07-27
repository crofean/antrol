<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MobileJknController;
use App\Http\Controllers\RegPeriksaController;
use App\Http\Controllers\BpjsLogController;
use App\Http\Controllers\CommandOutputController;
use App\Http\Controllers\FlowAnalyticsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// ══════════════════════════════════════════════════════════
// PUBLIC ROUTES — tidak perlu login
// ══════════════════════════════════════════════════════════

Route::get('/', function () {
    return view('welcome');
});

// ── Auth Halaman ─────────────────────────────────────────
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle.login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ══════════════════════════════════════════════════════════
// PROTECTED ROUTES — wajib login
// ══════════════════════════════════════════════════════════

Route::middleware('auth.external')->group(function () {

    // ── Reg Periksa Halaman ──────────────────────────────────
    Route::prefix('regperiksa')->group(function () {
        Route::get('/', [RegPeriksaController::class, 'index'])->name('regperiksa.index');
    });

    // ── Mobile JKN Halaman ───────────────────────────────────
    Route::prefix('mobilejkn')->group(function () {
        Route::get('/taskid-logs', [MobileJknController::class, 'taskIdLogs'])->name('taskid.logs');
        Route::get('/run-command', [CommandOutputController::class, 'index'])->name('command.index');
        Route::get('/patient-data', [MobileJknController::class, 'showPatientDataForm'])->name('patient.data');
        Route::get('/referensi-pendaftaran', [MobileJknController::class, 'referensiPendaftaran'])->name('referensi.pendaftaran');
        Route::post('/referensi-pendaftaran', [MobileJknController::class, 'updateReferensiStatus'])->name('referensi.update-status');
    });

    // ── Command Runner & Logging ─────────────────────────────
    Route::prefix('command')->group(function () {
        Route::post('/run', [CommandOutputController::class, 'runCommand'])->name('command.run');
        Route::post('/stop', [CommandOutputController::class, 'stopCommand'])->name('command.stop');
        Route::get('/output/{jobId}', [CommandOutputController::class, 'getOutput'])->name('command.output');
        Route::get('/task-ids', [CommandOutputController::class, 'getTaskIds'])->name('command.task-ids');
        Route::get('/debug-cache/{jobId?}', [CommandOutputController::class, 'debugCache'])->name('command.debug');
    });

    Route::get('/log-viewer', [CommandOutputController::class, 'showLogViewer'])->name('log.viewer');
    Route::get('/stream-logs', [CommandOutputController::class, 'streamLogs'])->name('logs.stream');
    Route::get('/recent-logs/{lines?}', [CommandOutputController::class, 'getRecentLogs'])->name('logs.recent');

    // ── Execution Tracking Halaman ───────────────────────────
    Route::prefix('execution')->group(function () {
        Route::get('/details/{jobId}', [CommandOutputController::class, 'getDetailedExecution'])->name('execution.details.api');
        Route::get('/stream/{jobId}', [CommandOutputController::class, 'streamTaskExecution'])->name('execution.stream');
        Route::get('/viewer/{jobId}', [CommandOutputController::class, 'showExecutionViewer'])->name('execution.viewer');
    });

    // ── BPJS Logs Halaman ────────────────────────────────────
    Route::prefix('bpjs-logs')->group(function () {
        Route::get('/', [BpjsLogController::class, 'index'])->name('bpjs-logs.index');
    });

    // ── Flow Monitoring Halaman ──────────────────────────────
    Route::get('/monitoring', [FlowAnalyticsController::class, 'index'])->name('monitoring.index');
    Route::get('/monitoring/print', [FlowAnalyticsController::class, 'print'])->name('monitoring.print');
});
