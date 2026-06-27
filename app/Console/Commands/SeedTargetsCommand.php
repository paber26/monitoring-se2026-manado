<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Target;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SeedTargetsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:targets {file?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed targets table from Alokasi Tugas Excel file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = $this->argument('file');
        if (!$file) {
            $file = base_path('../monitoring-se2026_v1/Alokasi Tugas Per SLS SE2026.xlsx');
            if (!file_exists($file)) {
                $file = base_path('../Alokasi Tugas Per SLS SE2026.xlsx');
            }
            if (!file_exists($file)) {
                $this->error("File not found: " . $file);
                return;
            }
        }

        $this->info("Reading file: " . $file);
        $spreadsheet = IOFactory::load($file);

        // 1. Load Petugas mapping
        $mapping = [];
        $petugasSheet = $spreadsheet->getSheetByName('petugas');
        if ($petugasSheet) {
            $rows = $petugasSheet->toArray();
            $headers = array_shift($rows);
            $emailIdx = array_search('email', array_map('strtolower', $headers));
            $namaIdx = array_search('nama lengkap', array_map('strtolower', $headers));
            if ($namaIdx === false) {
                // Fallback to 'nama' if 'nama lengkap' not found
                $namaIdx = array_search('nama', array_map('strtolower', $headers));
            }
            if ($emailIdx !== false && $namaIdx !== false) {
                foreach ($rows as $r) {
                    $email = strtolower(trim($r[$emailIdx] ?? ''));
                    if ($email) {
                        $mapping[$email] = trim($r[$namaIdx] ?? '');
                    }
                }
            }
        }

        // 2. Load Alokasi Tugas per SLS
        $alokasiSheet = $spreadsheet->getSheetByName('alokasi tugas per sls');
        if (!$alokasiSheet) {
            $this->error("Sheet 'alokasi tugas per sls' not found");
            return;
        }

        $rows = $alokasiSheet->toArray();
        $headers = array_shift($rows);
        $headers = array_map(function($h) { return trim(strtolower($h)); }, $headers);

        $kecIdx = array_search('nmkec', $headers);
        $desaIdx = array_search('nmdesa', $headers);
        $namaSlsIdx = array_search('nama sls', $headers);
        $slsIdx = array_search('idsubsls_25_2', $headers);
        $totalIdx = array_search('total assignment fasih', $headers);
        $flagIdx = array_search('flag sls open pbi', $headers);
        $kkIdx = array_search('kk open pbi', $headers);
        $pplIdx = array_search('email ppl', $headers);
        $pmlIdx = array_search('email pml', $headers);

        $regionTargets = [];
        $userTargets = [];
        $slsTargets = [];

        foreach ($rows as $r) {
            $total = (int)($r[$totalIdx] ?? 0);
            if ($total <= 0) continue;

            $kec = trim($r[$kecIdx] ?? '');
            if ($kec) {
                if (!isset($regionTargets[$kec])) $regionTargets[$kec] = 0;
                $regionTargets[$kec] += $total;
            }

            $sls = trim($r[$slsIdx] ?? '');
            if ($sls) {
                // remove .0 if any
                if (substr($sls, -2) === '.0') $sls = substr($sls, 0, -2);
                if (!isset($slsTargets[$sls])) {
                    $slsTargets[$sls] = [
                        'total' => 0,
                        'flag' => 0,
                        'kk' => 0,
                        'ppl_name' => '',
                        'pml_name' => '',
                        'nmkec' => trim($r[$kecIdx] ?? ''),
                        'nmdesa' => trim($r[$desaIdx] ?? ''),
                        'nama_sls' => trim($r[$namaSlsIdx] ?? '')
                    ];
                }
                $slsTargets[$sls]['total'] += $total;
                $slsTargets[$sls]['flag'] += (int)($r[$flagIdx] ?? 0);
                $slsTargets[$sls]['kk'] += (int)($r[$kkIdx] ?? 0);

                $pplEmail = strtolower(trim($r[$pplIdx] ?? ''));
                $pmlEmail = strtolower(trim($r[$pmlIdx] ?? ''));
                $pplName = $mapping[$pplEmail] ?? $pplEmail;
                $pmlName = $mapping[$pmlEmail] ?? $pmlEmail;

                $slsTargets[$sls]['ppl_name'] = $pplName;
                $slsTargets[$sls]['pml_name'] = $pmlName;

                if ($pplName) {
                    if (!isset($userTargets[$pplName])) $userTargets[$pplName] = 0;
                    $userTargets[$pplName] += $total;
                }
                if ($pmlName) {
                    if (!isset($userTargets[$pmlName])) $userTargets[$pmlName] = 0;
                    $userTargets[$pmlName] += $total;
                }
            }
        }

        Target::truncate();

        foreach ($regionTargets as $kec => $val) {
            Target::create([
                'type' => 'region',
                'key' => $kec,
                'target_value' => $val
            ]);
        }

        foreach ($userTargets as $user => $val) {
            Target::create([
                'type' => 'user',
                'key' => $user,
                'target_value' => $val
            ]);
        }

        foreach ($slsTargets as $sls => $data) {
            Target::create([
                'type' => 'sls',
                'key' => $sls,
                'target_value' => $data['total'],
                'meta' => [
                    'flag_sls_open_pbi' => $data['flag'],
                    'kk_open_pbi' => $data['kk'],
                    'ppl_name' => $data['ppl_name'],
                    'pml_name' => $data['pml_name'],
                    'nmkec' => $data['nmkec'],
                    'nmdesa' => $data['nmdesa'],
                    'nama_sls' => $data['nama_sls']
                ]
            ]);
        }

        $this->info("Seeded Targets: " . count($regionTargets) . " regions, " . count($userTargets) . " users, " . count($slsTargets) . " SLS.");
    }
}
