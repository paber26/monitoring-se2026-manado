<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assignment;
use App\Imports\AssignmentImport;
use Maatwebsite\Excel\Facades\Excel;

class AssignmentController extends Controller
{
    public function index()
    {
        $assignments = Assignment::where('source_from', '!=', 'U-CAWI')
            ->where('assignment_status_alias', '!=', 'SUBMITTED RESPONDENT');
            
        $totalDocs = $assignments->count();
        
        $statusCounts = (clone $assignments)->selectRaw('assignment_status_alias, count(*) as total')
            ->groupBy('assignment_status_alias')
            ->pluck('total', 'assignment_status_alias')
            ->toArray();

        $pivotRaw = (clone $assignments)->selectRaw('level_3_name, count(*) as total')
            ->groupBy('level_3_name')
            ->get();
            
        $pivotData = [];
        foreach ($pivotRaw as $row) {
            $kec = $row->level_3_name ?: 'Tidak Diketahui';
            $pivotData[$kec] = ['total' => $row->total];
        }
        
        $totalTargetSls = \App\Models\Target::where('type', 'sls')->count();
        
        $totalSlsDikerjakan = (clone $assignments)->distinct('level_6_full_code')->count('level_6_full_code');

        return view('monitoring.dashboard', compact(
            'totalDocs', 'totalTargetSls', 'totalSlsDikerjakan', 'pivotData', 'statusCounts'
        ));
    }

    public function progresKecamatan()
    {
        $assignments = Assignment::where('source_from', '!=', 'U-CAWI')
            ->where('assignment_status_alias', '!=', 'SUBMITTED RESPONDENT');
            
        $statusKeys = (clone $assignments)->distinct('assignment_status_alias')
                        ->pluck('assignment_status_alias')
                        ->sort()
                        ->values()
                        ->toArray();
                        
        $rawCounts = (clone $assignments)->selectRaw('level_3_name, assignment_status_alias, count(*) as count')
            ->groupBy('level_3_name', 'assignment_status_alias')
            ->get();
            
        $pivotData = [];
        foreach ($rawCounts as $row) {
            $kec = $row->level_3_name ?: 'Tidak Diketahui';
            if (!isset($pivotData[$kec])) {
                $pivotData[$kec] = ['total' => 0];
            }
            $pivotData[$kec][$row->assignment_status_alias] = $row->count;
            $pivotData[$kec]['total'] += $row->count;
        }
        
        uasort($pivotData, function($a, $b) {
            return $b['total'] <=> $a['total'];
        });
        
        $targets = \App\Models\Target::where('type', 'region')->pluck('target_value', 'key')->toArray();

        return view('monitoring.progres-kecamatan', compact('pivotData', 'statusKeys', 'targets'));
    }

    public function progresSls(Request $request)
    {
        $assignments = Assignment::where('source_from', '!=', 'U-CAWI')
            ->where('assignment_status_alias', '!=', 'SUBMITTED RESPONDENT');
            
        $slsData = (clone $assignments)->selectRaw('level_6_full_code, max(level_3_name) as level_3_name, max(level_4_name) as level_4_name, max(level_5_name) as level_5_name, count(*) as count')
            ->groupBy('level_6_full_code')
            ->orderBy('level_6_full_code')
            ->get();
            
        $filterKec = $request->query('kecamatan');
        $filterFlag = $request->query('flag');
        
        $targets = \App\Models\Target::where('type', 'sls')->get()->keyBy('key');
        $uniqueKecamatan = $slsData->pluck('level_3_name')->unique()->sort()->values();
        
        if ($filterKec) {
            $slsData = $slsData->where('level_3_name', $filterKec);
        }
        
        if ($filterFlag !== null && $filterFlag !== '') {
            $slsData = $slsData->filter(function($item) use ($targets, $filterFlag) {
                $meta = isset($targets[$item->level_6_full_code]) ? $targets[$item->level_6_full_code]->meta : null;
                $flag = is_array($meta) && isset($meta['flag_sls_open_pbi']) ? $meta['flag_sls_open_pbi'] : 0;
                if ($filterFlag == '1') return $flag > 0;
                if ($filterFlag == '0') return $flag == 0;
                return true;
            });
        }

        return view('monitoring.progres-sls', compact('slsData', 'targets', 'uniqueKecamatan', 'filterKec', 'filterFlag'));
    }

    public function leaderboard()
    {
        $assignments = Assignment::where('source_from', '!=', 'U-CAWI')
            ->where('assignment_status_alias', '!=', 'SUBMITTED RESPONDENT')
            ->where(function($query) {
                $query->where('assignment_status_alias', '!=', 'DRAFT')
                      ->orWhereNull('assignment_status_alias');
            })
            ->get();
            
        $leaderboard = [];
        $uniqueRoles = ['Pencacah', 'Pengawas']; // Base roles, can add more if needed
        
        foreach ($assignments as $a) {
            foreach ($uniqueRoles as $roleName) {
                $username = '';
                if ($roleName === 'Pencacah') {
                    $username = $a->assigned_ppl_name;
                } else if ($roleName === 'Pengawas') {
                    $username = $a->assigned_pml_name;
                } else {
                    if ($a->current_user_survey_role_name === $roleName) {
                        $username = $a->real_name ?: $a->current_user_username ?: 'Sistem / Tidak Diketahui';
                    }
                }
                
                if (!empty($username) && trim($username) !== '' && strtolower(trim($username)) !== 'nan') {
                    $kec = $a->level_3_name ?: 'Tidak Diketahui';
                    $userKey = $username . '|' . $roleName;
                    
                    if (!isset($leaderboard[$userKey])) {
                        $leaderboard[$userKey] = [
                            'username' => $username,
                            'name' => $username,
                            'role' => $roleName,
                            'total' => 0,
                            'kecamatans' => []
                        ];
                    }
                    $leaderboard[$userKey]['total']++;
                    
                    if (!isset($leaderboard[$userKey]['kecamatans'][$kec])) {
                        $leaderboard[$userKey]['kecamatans'][$kec] = 0;
                    }
                    $leaderboard[$userKey]['kecamatans'][$kec]++;
                }
            }
        }
        
        uasort($leaderboard, function($a, $b) {
            return $b['total'] <=> $a['total'];
        });
        
        $pclData = array_slice(array_filter($leaderboard, fn($d) => $d['role'] === 'Pencacah'), 0, 10);
        $pmlData = array_slice(array_filter($leaderboard, fn($d) => $d['role'] === 'Pengawas'), 0, 10);
        
        $targets = \App\Models\Target::where('type', 'user')->pluck('target_value', 'key')->toArray();

        return view('monitoring.leaderboard', compact('pclData', 'pmlData', 'targets'));
    }

    public function targetHarian(Request $request)
    {
        $startDate = $request->query('start_date', '2026-06-15');
        $currentDate = $request->query('current_date', date('Y-m-d'));
        $role = $request->query('role', 'Pencacah');
        $kecamatan = $request->query('kecamatan');
        
        $calcWorkingDays = function($start, $end) {
            try {
                $begin = new \DateTime($start);
                $end = new \DateTime($end);
                if ($begin > $end) return 0;
                $end->modify('+1 day');
                $interval = \DateInterval::createFromDateString('1 day');
                $period = new \DatePeriod($begin, $interval, $end);
                $days = 0;
                foreach ($period as $dt) {
                    if ($dt->format('N') != 7) $days++;
                }
                return $days;
            } catch (\Exception $e) {
                return 0;
            }
        };
        
        $workingDays = $calcWorkingDays($startDate, '2026-07-15'); // Assume project end date
        $elapsedWorkingDays = $calcWorkingDays($startDate, $currentDate);
        
        $assignments = Assignment::where('source_from', '!=', 'U-CAWI')
            ->where('assignment_status_alias', '!=', 'SUBMITTED RESPONDENT')
            ->where(function($query) {
                $query->where('assignment_status_alias', '!=', 'DRAFT')
                      ->orWhereNull('assignment_status_alias');
            })
            ->get();
            
        $uniqueKecamatan = $assignments->pluck('level_3_name')->filter()->unique()->sort()->values();
            
        $leaderboard = [];
        $uniqueRoles = ['Pencacah', 'Pengawas'];
        
        foreach ($assignments as $a) {
            foreach ($uniqueRoles as $roleName) {
                $username = '';
                if ($roleName === 'Pencacah') {
                    $username = $a->assigned_ppl_name;
                } else if ($roleName === 'Pengawas') {
                    $username = $a->assigned_pml_name;
                }
                
                if (!empty($username) && trim($username) !== '' && strtolower(trim($username)) !== 'nan') {
                    $kec = $a->level_3_name ?: 'Tidak Diketahui';
                    $userKey = $username . '|' . $roleName;
                    
                    if (!isset($leaderboard[$userKey])) {
                        $leaderboard[$userKey] = [
                            'username' => $username,
                            'name' => $username,
                            'role' => $roleName,
                            'total' => 0,
                            'kecamatans' => []
                        ];
                    }
                    $leaderboard[$userKey]['total']++;
                    
                    if (!isset($leaderboard[$userKey]['kecamatans'][$kec])) {
                        $leaderboard[$userKey]['kecamatans'][$kec] = 0;
                    }
                    $leaderboard[$userKey]['kecamatans'][$kec]++;
                }
            }
        }
        
        $targetData = array_filter($leaderboard, function($d) use ($role, $kecamatan) {
            if ($role && $d['role'] !== $role) return false;
            if ($kecamatan && !isset($d['kecamatans'][$kecamatan])) return false;
            return true;
        });
        
        uasort($targetData, function($a, $b) {
            return $b['total'] <=> $a['total'];
        });
        
        $targets = \App\Models\Target::where('type', 'user')->pluck('target_value', 'key')->toArray();

        return view('monitoring.target-harian', compact('targetData', 'targets', 'uniqueKecamatan', 'workingDays', 'elapsedWorkingDays', 'startDate', 'currentDate', 'role', 'kecamatan'));
    }

    public function performaRole($role)
    {
        $assignments = Assignment::where('source_from', '!=', 'U-CAWI')->get();
            
        $leaderboard = [];
        $isPML = (strtolower($role) === 'pengawas');
        
        foreach ($assignments as $a) {
            $username = '';
            if (strtolower($role) === 'pencacah') {
                $username = $a->assigned_ppl_name;
            } else if (strtolower($role) === 'pengawas') {
                $username = $a->assigned_pml_name;
            } else {
                if (strtolower($a->current_user_survey_role_name) === strtolower($role)) {
                    $username = $a->real_name ?: $a->current_user_username ?: 'Sistem / Tidak Diketahui';
                }
            }
            
            if (!empty($username) && trim($username) !== '' && strtolower(trim($username)) !== 'nan') {
                $kec = $a->level_3_name ?: 'Tidak Diketahui';
                $status = $a->assignment_status_alias;
                
                if (!isset($leaderboard[$username])) {
                    $leaderboard[$username] = [
                        'username' => $username,
                        'name' => $username,
                        'total' => 0,
                        'kecamatans' => [],
                        'statuses' => []
                    ];
                }
                $leaderboard[$username]['total']++;
                
                if (!isset($leaderboard[$username]['kecamatans'][$kec])) {
                    $leaderboard[$username]['kecamatans'][$kec] = 0;
                }
                $leaderboard[$username]['kecamatans'][$kec]++;
                
                if ($status) {
                    if (!isset($leaderboard[$username]['statuses'][$status])) {
                        $leaderboard[$username]['statuses'][$status] = 0;
                    }
                    $leaderboard[$username]['statuses'][$status]++;
                }
            }
        }
        
        uasort($leaderboard, function($a, $b) {
            return $b['total'] <=> $a['total'];
        });
        
        $targets = \App\Models\Target::where('type', 'user')->pluck('target_value', 'key')->toArray();

        return view('monitoring.role', compact('role', 'leaderboard', 'targets', 'isPML'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:51200',
        ]);

        try {
            // Save file temporarily
            $filename = 'import_' . time() . '.' . $request->file('file')->getClientOriginalExtension();
            $path = $request->file('file')->storeAs('imports', $filename); // By default uses 'local' disk which is storage/app/private or storage/app depending on laravel version
            
            // Initialize progress
            \Illuminate\Support\Facades\Cache::put('upload_progress', [
                'status' => 'reading',
                'total' => 0,
                'current' => 0,
                'sls' => 'Menyiapkan Import...'
            ], 300);

            // Execute command in background
            $artisanPath = base_path('artisan');
            $command = "php \"{$artisanPath}\" import:excel \"{$path}\"";

            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                pclose(popen("start /B " . $command, "r")); 
            } else {
                exec($command . " > /dev/null 2>&1 &");
            }

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Proses import dimulai!']);
            }
            return redirect()->back()->with('success', 'Proses import berjalan di latar belakang.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Cache::put('upload_progress', [
                'status' => 'error',
                'message' => $e->getMessage()
            ], 300);

            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function progress()
    {
        $progress = \Illuminate\Support\Facades\Cache::get('upload_progress', [
            'status' => 'idle'
        ]);
        return response()->json($progress);
    }
}
