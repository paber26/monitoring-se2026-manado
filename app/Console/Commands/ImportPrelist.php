<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ImportPrelist extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:prelist {path}';

    protected $description = 'Import total assignment FASIH (Prelist target) from Excel';

    public function handle()
    {
        $path = $this->argument('path');
        if (!file_exists($path)) {
            $this->error("File tidak ditemukan: {$path}");
            return;
        }
        
        $this->info("Membaca file excel: {$path}");

        try {
            // Using IOFactory to read the excel file data array directly to avoid memory/header issues
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray();
            
            $this->info("Ditemukan " . count($data) . " baris. Memulai import...");
            
            $bar = $this->output->createProgressBar(count($data));
            
            foreach ($data as $index => $row) {
                // Skip headers (usually first 2 rows)
                if ($index < 2) {
                    $bar->advance();
                    continue;
                }
                
                // Column 3 is IDSUBSLS_25_2
                // Column 29 is TOTAL ASSIGNMENT FASIH
                $idsubsls = $row[3] ?? null;
                $totalAssignment = $row[29] ?? 0;
                
                if (empty($idsubsls) || !is_numeric($totalAssignment)) {
                    $bar->advance();
                    continue;
                }
                
                \App\Models\Wilkerstat::updateOrCreate(
                    ['idsubsls' => (string) $idsubsls],
                    ['target_fasih' => (int) $totalAssignment]
                );
                
                $bar->advance();
            }
            
            $bar->finish();
            $this->newLine();
            $this->info("Import Prelist selesai!");
            
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }
}
