<?php

namespace App\Imports;

use App\Models\Employee;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class EmployeesImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            // Lewati baris header pertama
            if ($index === 0) continue;

            $nik = trim($row[2] ?? '');
            if (empty($nik)) continue;

            $nilaiKinerja = strtoupper(trim($row[44] ?? ''));
            $nilaiKompetensi = strtoupper(trim($row[46] ?? ''));

            $status = $this->hitungStatus($nilaiKinerja, $nilaiKompetensi);

            Employee::updateOrCreate(
                ['nik' => $nik],
                [
                    'nama'              => $row[3] ?? null,
                    'nama_unit'         => $row[28] ?? null,
                    'lama_band_posisi'  => $row[40] ?? null,
                    'nilai_kinerja'     => $nilaiKinerja,
                    'nilai_kompetensi'  => $nilaiKompetensi,
                    'nilai_behavior'    => $row[48] ?? null,
                    'tc'                => $row[68] ?? null,
                    // 'status'            => $status,
                ]
            );
        }
    }

    private function hitungStatus($nilaiKinerja, $nilaiKompetensi)
    {
        // Mapping huruf ke angka
        $rank = [
            'P1' => 1, 'P2' => 2, 'P3' => 3, 'P4' => 4, 'P5' => 5,
            'K1' => 1, 'K2' => 2, 'K3' => 3, 'K4' => 4, 'K5' => 5,
        ];

        $p = $rank[$nilaiKinerja] ?? null;
        $k = $rank[$nilaiKompetensi] ?? null;

        if (is_null($p) || is_null($k)) {
            return 'Data tidak valid';
        }

        if ($p <= 3 && $k <= 3) {
            return 'Eligible';
        } elseif ($p >= 4 && $k >= 4) {
            return 'Not Eligible';
        } else {
            return 'Perlu Review';
        }
    }
}
