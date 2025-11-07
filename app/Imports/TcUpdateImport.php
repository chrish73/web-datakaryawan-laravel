<?php

namespace App\Imports;

use App\Models\Employee;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class TcUpdateImport implements ToCollection
{
    private array $updatedNiks = [];
    private array $notFoundNiks = [];
    private array $headers = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            // Baris pertama berisi header
            if ($index === 0) {
                $this->headers = $row->map(fn($value) => strtolower(trim($value)))->toArray();
                continue;
            }

            // Buat associative array dari header dan data
            $data = [];
            foreach ($this->headers as $key => $header) {
                $data[$header] = $row[$key] ?? null;
            }

            // Ambil NIK, TC, dan UNIT (gunakan lowercase karena header sudah diturunkan jadi lowercase)
            $nik  = trim($data['nik'] ?? '');
            $tc   = trim($data['tc'] ?? '');
            $unit = trim($data['unit'] ?? '');

            if (empty($nik)) continue;

            // Cari employee berdasarkan NIK
            $employee = Employee::where('nik', $nik)->first();

            if ($employee) {
                // Update TC dan UNIT sekaligus
                $employee->update([
                    'tc'   => $tc,
                    'unit' => $unit,
                ]);
                $this->updatedNiks[] = $nik;
            } else {
                $this->notFoundNiks[] = $nik;
            }
        }
    }

    public function getUpdatedNiks(): array
    {
        return $this->updatedNiks;
    }

    public function getNotFoundNiks(): array
    {
        return $this->notFoundNiks;
    }
}
