<?php

namespace App\Imports;

use App\Models\Petugas;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PetugasImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if (empty($row['kode_identitas'])) {
                continue; 
            }

            Petugas::updateOrCreate(
                ['kode_identitas' => $row['kode_identitas']],
                [
                    'nama' => $row['nama'] ?? null,
                    'email' => $row['email'] ?? null,
                    'open' => (int) ($row['open'] ?? 0),
                    'draft' => (int) ($row['draft'] ?? 0),
                    'submitted_by_pencacah' => (int) ($row['submitted_by_pencacah'] ?? 0),
                    'approved_by_pengawas' => (int) ($row['approved_by_pengawas'] ?? 0),
                    'rejected_by_pengawas' => (int) ($row['rejected_by_pengawas'] ?? 0),
                    'submitted_respondent' => (int) ($row['submitted_respondent'] ?? 0),
                    'revoked_by_pengawas' => (int) ($row['revoked_by_pengawas'] ?? 0),
                    'completed_by_admin_kabupaten' => (int) ($row['completed_by_admin_kabupaten'] ?? 0),
                ]
            );
        }
    }
}
