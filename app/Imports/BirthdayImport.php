<?php

namespace App\Imports;

use App\Models\Employee;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Carbon\Carbon;

class BirthdayImport implements ToCollection
{
    private array $updatedNiks = [];
    private array $notFoundNiks = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            // Lewati baris header
            if ($index === 0) continue;

            // NIK ada di kolom index 4 (kolom ke-5)
            $nik = trim($row[4] ?? '');
            if (empty($nik)) continue;

            // Cari employee berdasarkan NIK
            $employee = Employee::where('nik', $nik)->first();

            if ($employee) {
                // Update data ulang tahun
                $tglLahir = $this->parseDate($row[6] ?? null); // Kolom Tgl Lahir

                $employee->update([
                    'tgl_lahir' => $tglLahir,
                    'kota_lahir' => $row[7] ?? null, // Kota Lahir
                    'nama_jalan' => $row[8] ?? null, // Nama Jalan
                ]);

                $this->updatedNiks[] = $nik;
            } else {
                $this->notFoundNiks[] = $nik;
            }
        }
    }

    /**
     * Parse tanggal dari berbagai format
     */
    private function parseDate($date)
    {
        if (empty($date)) return null;

        try {
            // Jika format "1970-07-07 00:00:00"
            if (is_string($date) && strpos($date, '-') !== false) {
                return Carbon::parse($date)->format('Y-m-d');
            }

            // Jika format Excel serial date
            if (is_numeric($date)) {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date))->format('Y-m-d');
            }

            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
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
