<?php

namespace App\Imports;

use App\Models\Assignment;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class AssignmentImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading
{
    public function model(array $row)
    {
        return new Assignment([
            'date_created'                  => isset($row['date_created']) ? $this->parseDate($row['date_created']) : null,
            'date_modified'                 => isset($row['date_modified']) ? $this->parseDate($row['date_modified']) : null,
            'assignment_status_alias'       => $row['assignment_status_alias'] ?? null,
            'level_3_name'                  => $row['level_3_name'] ?? null,
            'level_4_name'                  => $row['level_4_name'] ?? null,
            'level_5_name'                  => $row['level_5_name'] ?? null,
            'level_6_full_code'             => $row['level_6_full_code'] ?? null,
            'sum_clean'                     => $row['sum_clean'] ?? 0,
            'sum_error'                     => $row['sum_error'] ?? 0,
            'sum_remark'                    => $row['sum_remark'] ?? 0,
            'kk_open_pbi'                   => $row['kk_open_pbi'] ?? 0,
            'assigned_ppl_name'             => $row['assigned_ppl_name'] ?? null,
            'assigned_pml_name'             => $row['assigned_pml_name'] ?? null,
            'current_user_survey_role_name' => $row['current_user_survey_role_name'] ?? null,
            'source_from'                   => $row['source_from'] ?? null,
            'real_name'                     => $row['real_name'] ?? null,
            'current_user_username'         => $row['current_user_username'] ?? null,
            'email'                         => $row['email'] ?? null,
        ]);
    }

    private function parseDate($value)
    {
        if (is_numeric($value)) {
            return Date::excelToDateTimeObject($value)->format('Y-m-d H:i:s');
        }
        return $value;
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
