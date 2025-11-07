<?php

namespace App\Imports;

use App\Models\Employee;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Carbon\Carbon;

class EmployeesImport implements ToCollection
{
    private array $importedNiks = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            // Lewati baris header pertama
            if ($index === 0) {
                continue;
            }

            $nik = trim($row[2] ?? '');
            if (empty($nik)) {
                continue;
            }

            // Daftar kota yang valid
            $validCities = [
                'BATAM',
                'PEKANBARU',
                'PADANG',
                'MEDAN',
                'BENGKULU',
                'PALEMBANG',
                'BANDA ACEH',
                'PEMATANGSIANTAR',
                'BANDAR LAMPUNG',
                'PANGKAL PINANG',
                'JAMBI'
            ];

            $validCompany = [
                'PT. TELKOM INFRASTRUKTUR INDONESIA'
            ];


            // Ambil kota gedung dari Excel, trim & uppercase agar konsisten
            $namaPersonelSubarea = strtoupper(trim($row[19] ?? ''));
            // Lewati baris jika kota_gedung tidak valid
            if (!in_array($namaPersonelSubarea, $validCities)) {
                continue; // skip baris ini
            }

            $namaPerusahaan = strtoupper(trim($row[54] ?? ''));

            if (!in_array($namaPerusahaan, $validCompany)) {
                continue;
            }

            $this->importedNiks[] = $nik;

            // Ambil data utama
            $nilaiKinerja     = strtoupper(trim($row[44] ?? ''));
            $nilaiKompetensi  = strtoupper(trim($row[46] ?? ''));
            $nilaiBehavior    = strtoupper(trim($row[48] ?? ''));
            $d = $row[40] ?? null;

            // Hitung status eligibility
            $status = $this->hitungStatus($nilaiKinerja, $nilaiKompetensi, $d);

            // Simpan atau update data
            Employee::updateOrCreate(
                ['nik' => $nik],
                [
                    'tahun'                 => $row[0] ?? null,
                    'bulan'                 => $row[1] ?? null,
                    'nama'                  => $row[3] ?? null,
                    // 'tgl_lahir'             => $row[6] ?? null,
                    'jenis_kelamin'         => $row[4] ?? null,
                    'nama_agama'            => $row[5] ?? null,
                    'usia'                  => $row[6] ?? null,
                    'kelompok_usia'         => $row[7] ?? null,
                    'tgl_capeg'             => $this->parseDate($row[8] ?? null),
                    'tgl_pegprus'           => $this->parseDate($row[9] ?? null),
                    'tgl_mulaikerja'        => $this->parseDate($row[10] ?? null),
                    'tgl_pensiun'           => $this->parseDate($row[11] ?? null),
                    'nama_employee_group'   => $row[12] ?? null,
                    'nama_employee_subgroup' => $row[13] ?? null,
                    'kode_personnel_area'   => $row[14] ?? null,
                    'kode_host'             => $row[15] ?? null,
                    'kode_function_unit'    => $row[16] ?? null,
                    'nama_function_unit'    => $row[17] ?? null,
                    'kode_personnel_subarea' => $row[18] ?? null,
                    'nama_personnel_subarea' => $namaPersonelSubarea,
                    'tgl_psa'               => $this->parseDate($row[20] ?? null),
                    'kode_payroll_area'     => $row[21] ?? null,
                    'nama_payroll_area'     => $row[22] ?? null,
                    'kode_divisi'           => $row[23] ?? null,
                    'tgl_divisi'            => $this->parseDate($row[24] ?? null),
                    'nama_divisi'           => $row[25] ?? null,
                    'kode_unit'             => $row[26] ?? null,
                    'tgl_unit'              => $this->parseDate($row[27] ?? null),
                    'nama_unit'             => $row[28] ?? null,
                    'long_unit'             => $row[29] ?? null,
                    'witel'                 => $row[30] ?? null,
                    'objidposisi'           => $row[31] ?? null,
                    'tgl_posisi'            => $this->parseDate($row[32] ?? null),
                    'kode_posisi'           => $row[33] ?? null,
                    'nama_posisi'           => $row[34] ?? null,
                    'long_posisi'           => $row[35] ?? null,
                    'lama_posisi'           => $row[36] ?? null,
                    'nama_action'           => $row[37] ?? null,
                    'band_posisi'           => $row[38] ?? null,
                    'tgl_band_posisi'       => $this->parseDate($row[39] ?? null),
                    'lama_band_posisi'      => $row[40] ?? null,
                    'flag_pj'               => $row[41] ?? null,
                    'lama_pj'               => $row[42] ?? null,
                    'tahun_kinerja'         => $row[43] ?? null,
                    'nilai_kinerja'         => $nilaiKinerja,
                    'tahun_kompetensi'      => $row[45] ?? null,
                    'nilai_kompetensi'      => $nilaiKompetensi,
                    'tahun_behavior'        => $row[47] ?? null,
                    'nilai_behavior'        => $nilaiBehavior,
                    'level_pendidikan'      => $row[49] ?? null,
                    'group_pendidikan'      => $row[50] ?? null,
                    'jurusan_pendidikan'    => $row[51] ?? null,
                    'nama_institusi'        => $row[52] ?? null,
                    'kode_perusahaan'       => $row[53] ?? null,
                    'nama_perusahaan'       => $namaPerusahaan,
                    'kode_home'             => $row[55] ?? null,
                    'nama_home'             => $row[56] ?? null,
                    'job_family'            => $row[57] ?? null,
                    'job_function'          => $row[58] ?? null,
                    'job_role'              => $row[59] ?? null,
                    'role_category'         => $row[60] ?? null,
                    'flag_chief'            => $row[61] ?? null,
                    'email'                 => $row[62] ?? null,
                    'kode_gedung'           => $row[63] ?? null,
                    'nama_gedung'           => $row[64] ?? null,
                    'alamat_gedung'         => $row[65] ?? null,
                    'kota_gedung'           => $row[66] ?? null,
                    'unit'                  => null,
                    'tc'                    => null,
                    'status_eligibility'    => $status,
                ]
            );
        }
    }

    public function getImportedNiks(): array
    {
        return $this->importedNiks;
    }

    private function hitungStatus($nilaiKinerja, $nilaiKompetensi, $lamaBandPosisi)
    {
        $rank = [
            'P1' => 1, 'P2' => 2, 'P3' => 3, 'P4' => 4, 'P5' => 5,
            'K1' => 1, 'K2' => 2, 'K3' => 3, 'K4' => 4, 'K5' => 5,
        ];

        $p = $rank[$nilaiKinerja] ?? null;
        $k = $rank[$nilaiKompetensi] ?? null;
        $d = is_numeric($lamaBandPosisi) ? (int) $lamaBandPosisi : null;

        if (is_null($p) || is_null($k) || is_null($d)) {
            return null;
        }

        if (($p <= 2 || $k <= 2) && $d >= 6) {
            return 'Eligible';
        } elseif (($p >= 3 && $k >= 3) || $d < 6) {
            return 'Not Eligible';
        } else {
            return 'Perlu Review';
        }
    }


    private function parseDate($date)
    {
        if (empty($date)) {
            return null;
        }

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
}
