<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssignmentController;
use App\Models\Assignment;
use App\Models\Target;

Route::get('/', [AssignmentController::class, 'index'])->name('dashboard');
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

Route::get('/', [AssignmentController::class, 'index'])->name('dashboard');
Route::get('/progres-kecamatan', [AssignmentController::class, 'progresKecamatan'])->name('progres.kecamatan');
Route::get('/progres-sls', [AssignmentController::class, 'progresSls'])->name('progres.sls');
Route::get('/leaderboard', [AssignmentController::class, 'leaderboard'])->name('leaderboard');
Route::get('/target-harian', [AssignmentController::class, 'targetHarian'])->name('target.harian');
Route::get('/role/{role}', [AssignmentController::class, 'performaRole'])->name('role.performa');
