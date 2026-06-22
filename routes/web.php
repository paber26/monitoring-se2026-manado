<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssignmentController;
use App\Models\Assignment;
use App\Models\Target;

Route::get('/', [AssignmentController::class, 'index'])->name('dashboard');
Route::post('/upload', [AssignmentController::class, 'upload'])->name('upload');

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
