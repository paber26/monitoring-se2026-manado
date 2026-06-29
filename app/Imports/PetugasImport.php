<?php

namespace App\Imports;

use App\Models\Petugas;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PetugasImport implements ToCollection, WithHeadingRow
{
    protected $role;

    public function __construct(string $role = 'Pencacah')
    {
        $this->role = $role;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $kodeIdentitas = $row['kode_identitas'] ?? null;
            $nama = $row['nama'] ?? null;
            $email = $row['email'] ?? null;

            if (empty($kodeIdentitas) && empty($email) && empty($nama)) {
                continue;
            }

            if ($this->role === 'Pengawas') {
                // For Pengawas: find existing row by kode_identitas and only update the pengawas name/email
                if (!empty($kodeIdentitas)) {
                    Petugas::where('kode_identitas', $kodeIdentitas)->update([
                        'nama_pengawas' => $nama,
                        'email_pengawas' => $email,
                    ]);
                }
            } else {
                // For Pencacah: upsert the full row
                $identifier = [];
                if (!empty($kodeIdentitas)) {
                    $identifier['kode_identitas'] = $kodeIdentitas;
                } elseif (!empty($email)) {
                    $identifier['email'] = $email;
                } elseif (!empty($nama)) {
                    $identifier['nama'] = $nama;
                } else {
                    continue;
                }

                Petugas::updateOrCreate(
                    $identifier,
                    [
                        'kode_identitas' => $kodeIdentitas,
                        'nama'           => $nama,
                        'email'          => $email,
                        'role'           => 'Pencacah',
                        'open'           => (int) ($row['open'] ?? 0),
                        'draft'          => (int) ($row['draft'] ?? 0),
                        'submitted_by_pencacah'        => (int) ($row['submitted_by_pencacah'] ?? 0),
                        'approved_by_pengawas'         => (int) ($row['approved_by_pengawas'] ?? 0),
                        'rejected_by_pengawas'         => (int) ($row['rejected_by_pengawas'] ?? 0),
                        'submitted_respondent'         => (int) ($row['submitted_respondent'] ?? 0),
                        'revoked_by_pengawas'          => (int) ($row['revoked_by_pengawas'] ?? 0),
                        'completed_by_admin_kabupaten' => (int) ($row['completed_by_admin_kabupaten'] ?? 0),
                    ]
                );
            }
        }
    }
}
