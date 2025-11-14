<?php

namespace App\Imports;

use App\Models\Training;
use App\Models\Employee;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;
use Carbon\Carbon;

// KELAS UTAMA (WRAPPER)
class TrainingsImport implements WithMultipleSheets, SkipsUnknownSheets
{
    public function sheets(): array
    {
        // Hanya sheet bernama 'pelatihan hadir' yang akan diproses
        return [
            'pelatihan hadir' => new TrainingSheetImport(),
        ];
    }

    public function onUnknownSheet($sheetName)
    {
        // Abaikan sheets lain
    }
}

// KELAS LOGIKA IMPORT DENGAN LOGIKA PENCEGAHAN DUPLIKAT
class TrainingSheetImport implements ToModel, WithHeadingRow, WithValidation
{
    // BARU: Method untuk memastikan tipe data id_event sudah benar sebelum validasi
    public function prepareForValidation($data, $index)
    {
        // 1. Cek apakah kolom id_event ada di data yang diimpor
        if (isset($data['id_event'])) {
            $value = $data['id_event'];

            // 2. Konversi nilai apa pun menjadi string, lalu trim
            $sanitizedValue = trim((string) $value);

            // 3. Jika hasilnya adalah string kosong, ubah menjadi null
            if ($sanitizedValue === '') {
                 $data['id_event'] = null; // Ini akan lolos validasi 'nullable'
            } else {
                 $data['id_event'] = $sanitizedValue; // Tetap string
            }
        }
        // Jika kolom id_event tidak ada, 'nullable' akan menanganinya

        return $data;
    }


    public function model(array $row)
    {
        // 1. SANITASI NIK dan cari Karyawan
        $nikValue = trim((string) ($row['nik'] ?? null));
        $namaEvent = trim((string) ($row['nama_event'] ?? null));

        // Cek jika NIK atau Nama Event kosong atau terlalu pendek
        if (empty($nikValue) || empty($namaEvent)) {
            return null; // Skip baris jika data utama kosong
        }

        $employee = Employee::where('nik', $nikValue)->first();

        // Skip jika NIK tidak ditemukan di database
        if (!$employee) {
            return null;
        }

        // ** LOGIKA PENCEGAHAN DUPLIKAT BARU **
        // Cek apakah karyawan sudah memiliki pelatihan dengan nama yang sama
        $existingTraining = Training::where('employee_id', $employee->id)
                                     ->where('nama_pelatihan', $namaEvent)
                                      ->first();

        if ($existingTraining) {
            // Jika pelatihan sudah ada, SKIPPING baris ini (tidak membuat duplikat)
            return null;
        }

        // 2. TRANSFORMASI DATA LAINNYA
        $tanggalMulai = $this->transformDate($row['tanggal_mulai'] ?? null);
        $tanggalSelesai = $this->transformDate($row['tanggal_selesai'] ?? null);
        $statusPelatihan = ucwords(strtolower($row['delivery'] ?? null));

        // 3. BUAT MODEL TRAINING BARU
        // Nilai $row['id_event'] di sini sudah dijamin string atau null karena prepareForValidation
        return new Training([
            'id_event'          => $row['id_event'] ?? null,
            'employee_id'       => $employee->id,
            'nama_pelatihan'    => $namaEvent,
            'tanggal_mulai'     => $tanggalMulai,
            'tanggal_selesai'   => $tanggalSelesai,
            'status_pelatihan'  => in_array($statusPelatihan, ['Online', 'Offline']) ? $statusPelatihan : null,
        ]);
    }

    /**
     * Helper untuk mengubah nilai dari Excel menjadi format Y-m-d.
     */
    private function transformDate($value, $outputFormat = 'Y-m-d')
    {
        if (empty($value)) {
            return null;
        }

        $value = trim($value);
        $date = null;

        // 1. TANGANI ANGKA SERIAL EXCEL
        if (is_numeric($value)) {
            try {
                $date = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value));
            } catch (\Exception $e) {
            }
        }

        // 2. TANGANI STRING
        if (!$date) {
            $possibleFormats = ['m/d/Y', 'm-d-Y', 'Y-m-d', 'd/m/Y', 'd-m-Y'];

            foreach ($possibleFormats as $format) {
                try {
                    $parsedDate = Carbon::createFromFormat($format, $value);
                    if ($parsedDate instanceof Carbon) {
                        $date = $parsedDate;
                        break;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        // 3. FALLBACK: Coba generic parsing PHP
        if (!$date) {
            try {
                $date = Carbon::parse($value);
            } catch (\Exception $e) {
                return null;
            }
        }

        return $date ? $date->format($outputFormat) : null;
    }

    public function rules(): array
    {
        // Aturan validasi yang tetap
        return [
            'id_event' => 'nullable|string|max:255',
            'nik' => 'required',
            'nama_event' => 'required|string|max:255',
            'tanggal_mulai' => 'nullable',
            'tanggal_selesai' => 'nullable',
            'delivery' => 'nullable|in:Online,Offline,online,offline',
        ];
    }
}
