<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\MonitoringV2Controller;
use App\Models\Assignment;
use App\Models\Target;

// removed
Route::post('/upload', [AssignmentController::class, 'upload'])->name('upload');
Route::get('/upload-progress', [AssignmentController::class, 'progress'])->name('upload.progress');

Route::get('/api/data', function () {
    return response()->json(Assignment::all());
});

Route::get('/api/target', function () {
    $targets = Target::all();
    $result = [
        'region' => [],
        'user' => [],
        'sls' => []
    ];
    
    foreach ($targets as $t) {
        if ($t->type === 'sls') {
            $result['sls'][$t->key] = [
                'total_assignment' => $t->target_value,
                'flag_sls_open_pbi' => $t->meta['flag_sls_open_pbi'] ?? 0,
                'kk_open_pbi' => $t->meta['kk_open_pbi'] ?? 0
            ];
        } else {
            $result[$t->type][$t->key] = $t->target_value;
        }
    }
    
    return response()->json($result);
});

Route::get('/api/metadata', function () {
    // Just return current time or the time of the latest update
    $latest = Assignment::max('updated_at');
    $timeStr = $latest ? \Carbon\Carbon::parse($latest)->translatedFormat('d F Y H:i:s') : 'Tidak diketahui';
    return response()->json([
        'extraction_time' => now()->translatedFormat('d F Y H:i:s'),
        'file_timestamp' => $timeStr
    ]);
});

Route::get('/dashboard-utama', [MonitoringV2Controller::class, 'index'])->name('dashboard');
Route::get('/progres-kecamatan', [MonitoringV2Controller::class, 'progresKecamatan'])->name('progres.kecamatan');
Route::get('/progres-sls', [MonitoringV2Controller::class, 'progresSls'])->name('progres.sls');
Route::get('/', [MonitoringV2Controller::class, 'dashboardDesa'])->name('dashboard.desa');
Route::get('/leaderboard', [MonitoringV2Controller::class, 'leaderboard'])->name('leaderboard');
Route::get('/target-harian', [MonitoringV2Controller::class, 'targetHarian'])->name('target.harian');
Route::get('/role/{role}', [MonitoringV2Controller::class, 'performaRole'])->name('role.performa');
Route::get('/queries', [MonitoringV2Controller::class, 'queries'])->name('queries');

// Data Petugas
Route::get('/data-petugas', [MonitoringV2Controller::class, 'dataPetugas'])->name('data.petugas');
Route::post('/data-petugas/upload', [MonitoringV2Controller::class, 'uploadPetugas'])->name('data.petugas.upload');
