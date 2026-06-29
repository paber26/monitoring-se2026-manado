<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ImportWilkerstat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:wilkerstat {path}';

    protected $description = 'Import Wilkerstat references from Excel file';

    public function handle()
    {
        $path = $this->argument('path');
        if (!file_exists($path)) {
            $this->error("File tidak ditemukan: {$path}");
            return;
        }
        
        $this->info("Membaca file excel: {$path}");

        try {
            $data = \Maatwebsite\Excel\Facades\Excel::toArray(new class implements \Maatwebsite\Excel\Concerns\ToModel, \Maatwebsite\Excel\Concerns\WithHeadingRow {
                public function model(array $row) { return null; }
            }, $path);
            
            $rows = $data[0] ?? [];
            $this->info("Ditemukan " . count($rows) . " baris. Memulai import...");
            
            $bar = $this->output->createProgressBar(count($rows));
            
            foreach ($rows as $row) {
                if (empty($row['idsubsls_25_2'])) continue;
                
                \App\Models\Wilkerstat::updateOrCreate(
                    ['idsubsls' => (string) $row['idsubsls_25_2']],
                    [
                        'nmprov' => $row['nmprov'] ?? null,
                        'nmkab' => $row['nmkab'] ?? null,
                        'nmkec' => $row['nmkec'] ?? null,
                        'nmdesa' => $row['nmdesa'] ?? null,
                        'nmsls' => $row['nmsls'] ?? null,
                        'nmsubsls' => $row['nmsubsls'] ?? null,
                    ]
                );
                $bar->advance();
            }
            
            $bar->finish();
            $this->newLine();
            $this->info("Import selesai!");
            
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }
}
