<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assignment;
use App\Imports\AssignmentImport;
use Maatwebsite\Excel\Facades\Excel;

class MonitoringV2Controller extends Controller
{
    public function index()
    {
        $petugas = \App\Models\Petugas::all();
        $slsTargets = \App\Models\Target::where('type', 'sls')->get()->keyBy('key');
        $wilkerstats = \App\Models\Wilkerstat::all()->keyBy('idsubsls');

        $totalDocs = 0;
        $statusCounts = [
            'OPEN' => 0, 'DRAFT' => 0, 'SUBMITTED BY PENCACAH' => 0,
            'APPROVED BY PENGAWAS' => 0, 'REJECTED BY PENGAWAS' => 0,
            'SUBMITTED RESPONDENT' => 0, 'REVOKED BY PENGAWAS' => 0,
            'COMPLETED BY ADMIN KABUPATEN' => 0
        ];
        $pivotData = [];
        $slsDikerjakan = [];

        foreach ($petugas as $p) {
            $slsCode = $p->kode_identitas;
             $kecamatan = 'Tidak Diketahui';
            if (isset($wilkerstats[$slsCode])) {
                $kecamatan = $wilkerstats[$slsCode]->nmkec ?? 'Tidak Diketahui';
            } else
            if (isset($slsTargets[$slsCode])) {
                $kecamatan = $slsTargets[$slsCode]->meta['nmkec'] ?? 'Tidak Diketahui';
            }

            $statusCounts['OPEN'] += $p->open;
            $statusCounts['DRAFT'] += $p->draft;
            $statusCounts['SUBMITTED BY PENCACAH'] += $p->submitted_by_pencacah;
            $statusCounts['APPROVED BY PENGAWAS'] += $p->approved_by_pengawas;
            $statusCounts['REJECTED BY PENGAWAS'] += $p->rejected_by_pengawas;
            $statusCounts['SUBMITTED RESPONDENT'] += $p->submitted_respondent;
            $statusCounts['REVOKED BY PENGAWAS'] += $p->revoked_by_pengawas;
            $statusCounts['COMPLETED BY ADMIN KABUPATEN'] += $p->completed_by_admin_kabupaten;

            $realisasi = $p->submitted_by_pencacah + $p->approved_by_pengawas + 
                         $p->rejected_by_pengawas + $p->revoked_by_pengawas + 
                         $p->completed_by_admin_kabupaten;

            $totalDocs += $realisasi;

            if ($realisasi > 0) {
                $slsDikerjakan[$slsCode] = true;
            }

            if (!isset($pivotData[$kecamatan])) {
                $pivotData[$kecamatan] = ['total' => 0];
            }
            $pivotData[$kecamatan]['total'] += $realisasi;
        }

        $totalTargetSls = $slsTargets->count();
        $totalSlsDikerjakan = count($slsDikerjakan);

        // Filter out status counts that are 0 for cleaner UI, or keep them.
        $statusCounts = array_filter($statusCounts, fn($v) => $v > 0);

        return view('monitoring_v2.dashboard', compact(
            'totalDocs', 'totalTargetSls', 'totalSlsDikerjakan', 'pivotData', 'statusCounts'
        ));
    }

    public function progresKecamatan()
    {
        $petugas = \App\Models\Petugas::all();
        $slsTargets = \App\Models\Target::where('type', 'sls')->get()->keyBy('key');
        $wilkerstats = \App\Models\Wilkerstat::all()->keyBy('idsubsls');
            
        $statusKeys = [
            'OPEN', 'DRAFT', 'SUBMITTED BY PENCACAH', 'APPROVED BY PENGAWAS', 
            'REJECTED BY PENGAWAS', 'SUBMITTED RESPONDENT', 'REVOKED BY PENGAWAS', 
            'COMPLETED BY ADMIN KABUPATEN'
        ];
            
        $pivotData = [];
        foreach ($petugas as $p) {
            $slsCode = $p->kode_identitas;
             $kecamatan = 'Tidak Diketahui';
            if (isset($wilkerstats[$slsCode])) {
                $kecamatan = $wilkerstats[$slsCode]->nmkec ?? 'Tidak Diketahui';
            } else
            if (isset($slsTargets[$slsCode])) {
                $kecamatan = $slsTargets[$slsCode]->meta['nmkec'] ?? 'Tidak Diketahui';
            }
            
            if (!isset($pivotData[$kecamatan])) {
                $pivotData[$kecamatan] = ['total' => 0];
                foreach ($statusKeys as $k) $pivotData[$kecamatan][$k] = 0;
            }
            
            $pivotData[$kecamatan]['OPEN'] += $p->open;
            $pivotData[$kecamatan]['DRAFT'] += $p->draft;
            $pivotData[$kecamatan]['SUBMITTED BY PENCACAH'] += $p->submitted_by_pencacah;
            $pivotData[$kecamatan]['APPROVED BY PENGAWAS'] += $p->approved_by_pengawas;
            $pivotData[$kecamatan]['REJECTED BY PENGAWAS'] += $p->rejected_by_pengawas;
            $pivotData[$kecamatan]['SUBMITTED RESPONDENT'] += $p->submitted_respondent;
            $pivotData[$kecamatan]['REVOKED BY PENGAWAS'] += $p->revoked_by_pengawas;
            $pivotData[$kecamatan]['COMPLETED BY ADMIN KABUPATEN'] += $p->completed_by_admin_kabupaten;
            
            $realisasi = $p->submitted_by_pencacah + $p->approved_by_pengawas + 
                         $p->rejected_by_pengawas + $p->revoked_by_pengawas + 
                         $p->completed_by_admin_kabupaten;
                         
            $pivotData[$kecamatan]['total'] += $realisasi;
        }
        
        uasort($pivotData, function($a, $b) {
            return $b['total'] <=> $a['total'];
        });
        
        $targets = \App\Models\Target::where('type', 'region')->pluck('target_value', 'key')->toArray();

        return view('monitoring_v2.progres-kecamatan', compact('pivotData', 'statusKeys', 'targets'));
    }

    public function progresSls(Request $request)
    {
        $slsTargets = \App\Models\Target::where('type', 'sls')->get();
        $petugas = \App\Models\Petugas::all()->keyBy('kode_identitas');
        
        $filterKec = $request->query('kecamatan');
        $filterFlag = $request->query('flag');
        
        $uniqueKecamatan = collect();
        $slsData = collect();
        $slsCodesInPetugas = $petugas->keys();
        
        foreach ($slsCodesInPetugas as $slsCode) {
            $kec = 'Tidak Diketahui';
            $nmdesa = '-';
            $namaSls = '-';
            
            $wilkerstat = \App\Models\Wilkerstat::find($slsCode);
            if ($wilkerstat) {
                $kec = $wilkerstat->nmkec ?? 'Tidak Diketahui';
                $nmdesa = $wilkerstat->nmdesa ?? '-';
                $namaSls = $wilkerstat->nmsls ?? '-';
            } else {
                $t = $slsTargets->firstWhere('key', $slsCode);
                if ($t) {
                    $kec = $t->meta['nmkec'] ?? 'Tidak Diketahui';
                    $nmdesa = $t->meta['nmdesa'] ?? '-';
                    $namaSls = $t->meta['nama_sls'] ?? '-';
                }
            }
            
            $uniqueKecamatan->push($kec);
            if ($filterKec && $kec !== $filterKec) continue;
            
            $p = $petugas[$slsCode] ?? null;
            $count = 0;
            if ($p) {
                $count = $p->submitted_by_pencacah + $p->approved_by_pengawas + 
                         $p->rejected_by_pengawas + $p->revoked_by_pengawas + 
                         $p->completed_by_admin_kabupaten;
            }
            
            $slsData->push((object)[
                'level_6_full_code' => $slsCode,
                'level_3_name' => $kec,
                'level_4_name' => $nmdesa,
                'level_5_name' => $namaSls,
                'count' => $count
            ]);
        }
        
        $uniqueKecamatan = $uniqueKecamatan->unique()->sort()->values();
        $targets = $slsTargets->keyBy('key');

        return view('monitoring_v2.progres-sls', compact('slsData', 'targets', 'uniqueKecamatan', 'filterKec', 'filterFlag'));
    }



    public function leaderboard()
    {
        $petugasList = \App\Models\Petugas::all();
        $slsTargets = \App\Models\Target::where('type', 'sls')->get()->keyBy('key');
        $wilkerstats = \App\Models\Wilkerstat::all()->keyBy('idsubsls');
        
        $leaderboard = [];
        
        foreach ($petugasList as $p) {
            $slsCode = $p->kode_identitas;
            
             $kecamatan = 'Tidak Diketahui';
            if (isset($wilkerstats[$slsCode])) {
                $kecamatan = $wilkerstats[$slsCode]->nmkec ?? 'Tidak Diketahui';
            } else
            if (isset($slsTargets[$slsCode])) {
                $kecamatan = $slsTargets[$slsCode]->meta['nmkec'] ?? 'Tidak Diketahui';
            }
            
            $realisasi = $p->submitted_by_pencacah + $p->approved_by_pengawas + 
                         $p->rejected_by_pengawas + $p->revoked_by_pengawas + 
                         $p->completed_by_admin_kabupaten;
                         
            $username = $p->nama ?? $p->email ?? $p->kode_identitas;
            if (empty($username)) continue;
            
            $roleName = 'Pencacah';
            $normalizedName = strtolower(trim($username));
            $userKey = $normalizedName . '|' . $roleName;
            
            if (!isset($leaderboard[$userKey])) {
                $leaderboard[$userKey] = [
                    'username' => $normalizedName,
                    'name' => $username,
                    'role' => $roleName,
                    'total' => 0,
                    'kecamatans' => []
                ];
            }
            $leaderboard[$userKey]['total'] += $realisasi;
            
            if (!isset($leaderboard[$userKey]['kecamatans'][$kecamatan])) {
                $leaderboard[$userKey]['kecamatans'][$kecamatan] = 0;
            }
            $leaderboard[$userKey]['kecamatans'][$kecamatan] += $realisasi;
        }
        
        uasort($leaderboard, function($a, $b) {
            return $b['total'] <=> $a['total'];
        });
        
        $pclData = array_slice(array_filter($leaderboard, fn($d) => $d['role'] === 'Pencacah'), 0, 10);
        $pmlData = array_slice(array_filter($leaderboard, fn($d) => $d['role'] === 'Pengawas'), 0, 10);
        
        $targets = [];
        foreach ($petugasList as $p) {
            $username = $p->nama ?? $p->email ?? $p->kode_identitas;
            if (empty($username)) continue;
            
            $normalizedName = strtolower(trim($username));
            $slsCode = $p->kode_identitas;
            
            $targetVal = 0;
            if (isset($wilkerstats[$slsCode])) {
                $targetVal = $wilkerstats[$slsCode]->target_fasih ?? 0;
            } elseif (isset($slsTargets[$slsCode])) {
                $targetVal = $slsTargets[$slsCode]->target_value ?? 0;
            }
            
            if (!isset($targets[$normalizedName])) {
                $targets[$normalizedName] = 0;
            }
            $targets[$normalizedName] += $targetVal;
        }

        return view('monitoring_v2.leaderboard', compact('pclData', 'pmlData', 'targets'));
    }

    public function targetHarian(Request $request)
    {
        $startDate = $request->query('start_date', '2026-06-15');
        $currentDate = $request->query('current_date', date('Y-m-d'));
        $roleFilter = $request->query('role', 'Pencacah');
        $kecamatanFilter = $request->query('kecamatan');
        
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
        
        $workingDays = 60; // Fixed working days based on user requirement
        $elapsedWorkingDays = $calcWorkingDays($startDate, $currentDate);
        
        $petugasList = \App\Models\Petugas::all();
        $slsTargets = \App\Models\Target::where('type', 'sls')->get()->keyBy('key');
        $wilkerstats = \App\Models\Wilkerstat::all()->keyBy('idsubsls');
        
        $leaderboard = [];
        $uniqueKecamatan = collect();
        
        foreach ($petugasList as $p) {
            $slsCode = $p->kode_identitas;
            
            $kecamatan = 'Tidak Diketahui';
            if (isset($wilkerstats[$slsCode])) {
                $kecamatan = $wilkerstats[$slsCode]->nmkec ?? 'Tidak Diketahui';
            } elseif (isset($slsTargets[$slsCode])) {
                $kecamatan = $slsTargets[$slsCode]->meta['nmkec'] ?? 'Tidak Diketahui';
            }
            $uniqueKecamatan->push($kecamatan);
            
            $realisasi = $p->submitted_by_pencacah + $p->approved_by_pengawas + 
                         $p->rejected_by_pengawas + $p->revoked_by_pengawas + 
                         $p->completed_by_admin_kabupaten;
                         
            $username = $p->nama ?? $p->email ?? $p->kode_identitas;
            if (empty($username)) continue;
            
            $roleName = 'Pencacah';
            $normalizedName = strtolower(trim($username));
            $userKey = $normalizedName . '|' . $roleName;
            if (!isset($leaderboard[$userKey])) {
                $leaderboard[$userKey] = [
                    'username' => $normalizedName,
                    'name' => $username,
                    'role' => $roleName,
                    'total' => 0,
                    'kecamatans' => [],
                    'sls_details' => []
                ];
            }
            $leaderboard[$userKey]['total'] += $realisasi;
            
            if (!isset($leaderboard[$userKey]['kecamatans'][$kecamatan])) {
                $leaderboard[$userKey]['kecamatans'][$kecamatan] = 0;
            }
            $leaderboard[$userKey]['kecamatans'][$kecamatan] += $realisasi;
            
            // Add SLS detail
            $namaSls = '';
            $desa = '';
            $targetVal = 0;
            if (isset($wilkerstats[$slsCode])) {
                $namaSls = $wilkerstats[$slsCode]->nmsls ?? '';
                $desa = $wilkerstats[$slsCode]->nmdesa ?? '';
                $targetVal = $wilkerstats[$slsCode]->target_fasih ?? 0;
            } elseif (isset($slsTargets[$slsCode])) {
                $namaSls = $slsTargets[$slsCode]->meta['nama_sls'] ?? '';
                $desa = $slsTargets[$slsCode]->meta['nmdesa'] ?? '';
                $targetVal = $slsTargets[$slsCode]->target_value ?? 0;
            }
            $leaderboard[$userKey]['sls_details'][] = [
                'kode_sls' => $slsCode,
                'nama_sls' => $namaSls,
                'desa' => $desa,
                'open' => $p->open,
                'draft' => $p->draft,
                'submit_pencacah' => $p->submitted_by_pencacah,
                'submit_respondent' => $p->submitted_respondent,
                'approved' => $p->approved_by_pengawas,
                'rejected' => $p->rejected_by_pengawas,
                'revoked' => $p->revoked_by_pengawas,
                'completed' => $p->completed_by_admin_kabupaten,
                'realisasi' => $realisasi,
                'target' => $targetVal
            ];
        }
        
        $uniqueKecamatan = $uniqueKecamatan->unique()->sort()->values();
        
        $targetData = array_filter($leaderboard, function($d) use ($roleFilter, $kecamatanFilter) {
            if ($roleFilter && $d['role'] !== $roleFilter) return false;
            if ($kecamatanFilter && !isset($d['kecamatans'][$kecamatanFilter])) return false;
            return true;
        });
        
        uasort($targetData, function($a, $b) {
            return $b['total'] <=> $a['total'];
        });
        
        $targets = [];
        foreach ($petugasList as $p) {
            $username = $p->nama ?? $p->email ?? $p->kode_identitas;
            if (empty($username)) continue;
            
            $normalizedName = strtolower(trim($username));
            $slsCode = $p->kode_identitas;
            
            $targetVal = 0;
            if (isset($wilkerstats[$slsCode])) {
                $targetVal = $wilkerstats[$slsCode]->target_fasih ?? 0;
            } elseif (isset($slsTargets[$slsCode])) {
                $targetVal = $slsTargets[$slsCode]->target_value ?? 0;
            }
            
            if (!isset($targets[$normalizedName])) {
                $targets[$normalizedName] = 0;
            }
            $targets[$normalizedName] += $targetVal;
        }

        // Pass 'role' variable to view instead of 'roleFilter' to match blade template
        $role = $roleFilter;
        $kecamatan = $kecamatanFilter;

        return view('monitoring_v2.target-harian', compact('targetData', 'targets', 'uniqueKecamatan', 'workingDays', 'elapsedWorkingDays', 'startDate', 'currentDate', 'role', 'kecamatan'));
    }

    public function performaRole($role)
    {
        $petugasList = \App\Models\Petugas::all();
        $slsTargets = \App\Models\Target::where('type', 'sls')->get()->keyBy('key');
        $wilkerstats = \App\Models\Wilkerstat::all()->keyBy('idsubsls');
            
        $leaderboard = [];
        $isPML = (strtolower($role) === 'pengawas');
        
        foreach ($petugasList as $p) {
            $slsCode = $p->kode_identitas;
            $kecamatan = 'Tidak Diketahui';
            if (isset($wilkerstats[$slsCode])) {
                $kecamatan = $wilkerstats[$slsCode]->nmkec ?? 'Tidak Diketahui';
            } elseif (isset($slsTargets[$slsCode])) {
                $kecamatan = $slsTargets[$slsCode]->meta['nmkec'] ?? 'Tidak Diketahui';
            }
            
            $inferredRole = 'Pencacah'; 
            if (isset($slsTargets[$slsCode]) && strtolower($slsTargets[$slsCode]->meta['pml_name'] ?? '') == strtolower($p->nama)) {
                $inferredRole = 'Pengawas';
            }
            // Without slsTargets, we can't accurately determine role, so we show them for either or don't filter out completely
            
            $username = $p->nama ?? $p->email ?? $p->kode_identitas;
            if (empty($username)) continue;
            
            if (!empty($username) && trim($username) !== '-' && trim($username) !== '' && strtolower(trim($username)) !== 'nan') {
                $normalizedName = strtolower(trim($username));
                if (!isset($leaderboard[$normalizedName])) {
                    $leaderboard[$normalizedName] = [
                        'username' => $normalizedName,
                        'name' => $username,
                        'total' => 0,
                        'target' => 0,
                        'sls_count' => 0,
                        'kecamatans' => [],
                        'statuses' => [],
                        'sls_details' => []
                    ];
                }
                
                $realisasi = $p->submitted_by_pencacah + $p->approved_by_pengawas + 
                             $p->rejected_by_pengawas + $p->revoked_by_pengawas + 
                             $p->completed_by_admin_kabupaten;
                             
                $targetVal = isset($wilkerstats[$slsCode]) ? ($wilkerstats[$slsCode]->target_fasih ?? 0) : 0;
                if ($targetVal == 0 && isset($slsTargets[$slsCode])) {
                    $targetVal = $slsTargets[$slsCode]->target_value ?? 0;
                }
                
                $leaderboard[$normalizedName]['total'] += $realisasi;
                $leaderboard[$normalizedName]['target'] += $targetVal;
                $leaderboard[$normalizedName]['sls_count']++;
                
                if (!isset($leaderboard[$normalizedName]['kecamatans'][$kecamatan])) {
                    $leaderboard[$normalizedName]['kecamatans'][$kecamatan] = 0;
                }
                $leaderboard[$normalizedName]['kecamatans'][$kecamatan] += $realisasi;
                
                // Add statuses
                $statuses = [
                    'OPEN' => $p->open,
                    'DRAFT' => $p->draft,
                    'SUBMITTED BY PENCACAH' => $p->submitted_by_pencacah,
                    'APPROVED BY PENGAWAS' => $p->approved_by_pengawas,
                    'REJECTED BY PENGAWAS' => $p->rejected_by_pengawas,
                    'SUBMITTED RESPONDENT' => $p->submitted_respondent,
                    'REVOKED BY PENGAWAS' => $p->revoked_by_pengawas,
                    'COMPLETED BY ADMIN KABUPATEN' => $p->completed_by_admin_kabupaten
                ];
                
                foreach ($statuses as $st => $val) {
                    if ($val > 0) {
                        if (!isset($leaderboard[$normalizedName]['statuses'][$st])) {
                            $leaderboard[$normalizedName]['statuses'][$st] = 0;
                        }
                        $leaderboard[$normalizedName]['statuses'][$st] += $val;
                    }
                }
                
                // Add SLS detail
                $namaSls = '';
                $desa = '';
                if (isset($wilkerstats[$slsCode])) {
                    $namaSls = $wilkerstats[$slsCode]->nmsls ?? '';
                    $desa = $wilkerstats[$slsCode]->nmdesa ?? '';
                } elseif (isset($slsTargets[$slsCode])) {
                    $namaSls = $slsTargets[$slsCode]->meta['nama_sls'] ?? '';
                    $desa = $slsTargets[$slsCode]->meta['nmdesa'] ?? '';
                }
                $leaderboard[$normalizedName]['sls_details'][] = [
                    'kode_sls' => $slsCode,
                    'nama_sls' => $namaSls,
                    'desa' => $desa,
                    'open' => $p->open,
                    'draft' => $p->draft,
                    'submit_pencacah' => $p->submitted_by_pencacah,
                    'submit_respondent' => $p->submitted_respondent,
                    'approved' => $p->approved_by_pengawas,
                    'rejected' => $p->rejected_by_pengawas,
                    'revoked' => $p->revoked_by_pengawas,
                    'completed' => $p->completed_by_admin_kabupaten,
                    'realisasi' => $realisasi,
                    'target' => $targetVal
                ];
            }
        }
        
        uasort($leaderboard, function($a, $b) {
            return $b['total'] <=> $a['total'];
        });
        
        $targetsArray = \App\Models\Target::where('type', 'user')->pluck('target_value', 'key')->toArray();
        $targets = [];
        foreach ($targetsArray as $k => $v) {
            $targets[strtolower(trim($k))] = $v;
        }

        return view('monitoring_v2.role', compact('role', 'leaderboard', 'targets', 'isPML'));
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

    public function queries()
    {
        $jsonPath = storage_path('app/public/queries.json');
        $queries = [];
        
        if (file_exists($jsonPath)) {
            $queries = json_decode(file_get_contents($jsonPath), true);
            
            $kecamatanNames = \App\Models\Assignment::selectRaw('SUBSTRING(level_6_full_code, 1, 7) as level_3_code, level_3_name')
                ->whereNotNull('level_6_full_code')
                ->whereNotNull('level_3_name')
                ->distinct()
                ->get()
                ->pluck('level_3_name', 'level_3_code')
                ->toArray();
            
            // Group by kecamatan
            $groupedQueries = [];
            foreach ($queries as $q) {
                $kecCode = $q['kecamatan'];
                $kecName = $kecamatanNames[$kecCode] ?? $kecCode;
                
                if (!isset($groupedQueries[$kecCode])) {
                    $groupedQueries[$kecCode] = [
                        'code' => $kecCode,
                        'name' => $kecName,
                        'total_assignment' => 0,
                        'chunks' => []
                    ];
                }
                $groupedQueries[$kecCode]['total_assignment'] += $q['total_assignment'];
                $groupedQueries[$kecCode]['chunks'][] = $q;
            }
            
            // Sort by name
            uasort($groupedQueries, function($a, $b) {
                return $a['name'] <=> $b['name'];
            });
            
            $queries = $groupedQueries;
        }

        return view('monitoring_v2.queries', compact('queries'));
    }
    public function dashboardDesa()
    {
        return view('monitoring_v2.dashboard-desa');
    }

    public function dataPetugas()
    {
        $petugasList = \App\Models\Petugas::all();
        
        $summaries = [
            'total_petugas' => $petugasList->count(),
            'open' => $petugasList->sum('open'),
            'draft' => $petugasList->sum('draft'),
            'submitted_by_pencacah' => $petugasList->sum('submitted_by_pencacah'),
            'approved_by_pengawas' => $petugasList->sum('approved_by_pengawas'),
            'rejected_by_pengawas' => $petugasList->sum('rejected_by_pengawas'),
            'submitted_respondent' => $petugasList->sum('submitted_respondent'),
            'revoked_by_pengawas' => $petugasList->sum('revoked_by_pengawas'),
            'completed_by_admin_kabupaten' => $petugasList->sum('completed_by_admin_kabupaten'),
        ];
        
        return view('monitoring_v2.data-petugas', compact('petugasList', 'summaries'));
    }

    public function uploadPetugas(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:51200',
        ]);

        try {
            Excel::import(new \App\Imports\PetugasImport, $request->file('file'));
            return redirect()->back()->with('success', 'Data petugas berhasil diunggah dan diupdate.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
