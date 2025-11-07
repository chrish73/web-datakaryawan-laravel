<?php

namespace App\Http\Controllers;

use App\Exports\EmployeesExport;
use App\Imports\BirthdayImport;
use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\EmployeesImport;
use App\Imports\TcUpdateImport;
use Carbon\Carbon;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $employees = Employee::when($search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%");
            });
        })->simplePaginate(9);

        return view('employees.index', compact('employees', 'search'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt,xlsx,xls|max:4096',
        ]);

        // Buat instance dari EmployeesImport
        $import = new EmployeesImport();

        try {
            // Jalankan proses import dan pass instance-nya
            \Maatwebsite\Excel\Facades\Excel::import($import, $request->file('file'));

            // Ambil semua NIK yang berhasil diimpor
            $importedNiks = $import->getImportedNiks();

            if (!empty($importedNiks)) {
                // Hapus semua baris dari tabel 'employees' yang NIK-nya tidak ada dalam array $importedNiks
                Employee::whereNotIn('nik', $importedNiks)->delete(); //
                $message = 'Berhasil sinkronisasi data terbaru!';
            } else {
                $message = 'Impor selesai. Tidak ada data yang dihapus (tidak ditemukan NIK valid di file Excel).';
            }

            return redirect()->route('employees.index')->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->route('employees.index')->with('error', 'Gagal impor: ' . $e->getMessage());
        }
    }

    public function bandPositionChartData()
    {
        // Mendapatkan semua data UNIT dan band_posisi, lalu menghitungnya
        $data = Employee::select('UNIT', 'band_posisi', DB::raw('count(*) as count'))
                        ->groupBy('UNIT', 'band_posisi')
                        ->where('status_eligibility', 'Eligible')
                        ->orderBy('UNIT')
                        ->orderBy('band_posisi')
                        ->get();

        // Mengubah format data menjadi struktur yang lebih mudah untuk grafik
        $chartData = [];
        $units = $data->pluck('UNIT')->unique()->sort()->values();
        $bands = ['I', 'II', 'III', 'IV', 'V', 'VI']; // Urutan band yang diinginkan

        // Inisialisasi data dengan 0
        foreach ($units as $unit) {
            $chartData[$unit] = array_fill_keys($bands, 0);
        }

        // Isi data dengan hasil query
        foreach ($data as $item) {
            if (in_array($item->band_posisi, $bands)) {
                $chartData[$item->UNIT][$item->band_posisi] = $item->count;
            }
        }

        // Mengembalikan data dalam format JSON
        return response()->json([
            'units' => $units,
            'bands' => $bands,
            'data' => $chartData,
        ]);
    }

    public function ageGroupChartData()
    {
        // Logika CASE untuk mengelompokkan usia
        $ageGroupCase = "CASE
                            WHEN usia < 30 THEN '< 30'
                            WHEN usia >= 30 AND usia <= 40 THEN '30 - 40'
                            WHEN usia >= 41 AND usia <= 50 THEN '41 - 50'
                            WHEN usia > 50 THEN '> 50'
                            ELSE 'Lainnya'
                        END";

        // 1. Mendapatkan data UNIT dan kelompok_usia yang dihitung, lalu menghitungnya
        $data = Employee::select('UNIT', DB::raw("{$ageGroupCase} AS calculated_kelompok_usia"), DB::raw('count(*) as count'))
                        ->whereNotNull('usia') // Memastikan kolom usia ada nilainya
                        ->groupBy('UNIT', DB::raw($ageGroupCase))
                        ->orderBy('UNIT')
                        ->get();

        // 2. Tentukan urutan Kelompok Usia yang diinginkan
        $ageGroupsOrder = ['< 30', '30 - 40', '41 - 50', '> 50'];

        $chartData = [];
        $units = $data->pluck('UNIT')->unique()->sort()->values();

        // 3. Inisialisasi data dengan 0 dan mengisi hasil query
        foreach ($units as $unit) {
            $chartData[$unit] = array_fill_keys($ageGroupsOrder, 0);
        }

        foreach ($data as $item) {
            $group = $item->calculated_kelompok_usia;
            if (in_array($group, $ageGroupsOrder)) {
                $chartData[$item->UNIT][$group] = $item->count;
            }
        }

        // 4. Mengembalikan data dalam format JSON
        return response()->json([
            'units' => $units,
            'age_groups' => $ageGroupsOrder,
            'data' => $chartData,
        ]);
    }

    public function bandPositionDurationChartData()
    {
        // Logika CASE untuk mengelompokkan lama_bandposisi (dalam bulan)
        // < 2 tahun = < 24 bulan
        // 2 - 5 tahun = 24 - 60 bulan
        // > 5 tahun = > 60 bulan
        $durationGroupCase = "CASE
                                WHEN lama_band_posisi < 24 THEN '< 2 Tahun'
                                WHEN lama_band_posisi >= 24 AND lama_band_posisi <= 60 THEN '2 - 5 Tahun'
                                WHEN lama_band_posisi > 60 AND lama_band_posisi <= 120 THEN '5 - 10 Tahun'
                                WHEN lama_band_posisi > 121 THEN '> 10 Tahun'
                                ELSE 'Lainnya'
                            END";

        // 1. Mendapatkan data UNIT dan kelompok durasi yang dihitung, lalu menghitungnya
        $data = Employee::select('UNIT', DB::raw("{$durationGroupCase} AS calculated_duration_group"), DB::raw('count(*) as count'))
                        ->whereNotNull('lama_band_posisi') // Memastikan kolom lama_bandposisi ada nilainya
                        ->groupBy('UNIT', DB::raw($durationGroupCase))
                        ->orderBy('UNIT')
                        ->get();

        // 2. Tentukan urutan Durasi yang diinginkan
        $durationGroupsOrder = ['< 2 Tahun', '2 - 5 Tahun', '5 - 10 Tahun', '> 10 Tahun',];

        $chartData = [];
        $units = $data->pluck('UNIT')->unique()->sort()->values();

        // 3. Inisialisasi data dengan 0 dan mengisi hasil query
        foreach ($units as $unit) {
            $chartData[$unit] = array_fill_keys($durationGroupsOrder, 0);
        }

        foreach ($data as $item) {
            $group = $item->calculated_duration_group;
            if (in_array($group, $durationGroupsOrder)) {
                $chartData[$item->UNIT][$group] = $item->count;
            }
        }

        // 4. Mengembalikan data dalam format JSON
        return response()->json([
            'units' => $units,
            'duration_groups' => $durationGroupsOrder,
            'data' => $chartData,
        ]);
    }

    public function importBirthday(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt,xlsx,xls|max:4096',
        ]);

        $import = new BirthdayImport();

        try {
            Excel::import($import, $request->file('file'));

            $updatedCount = count($import->getUpdatedNiks());
            $notFoundCount = count($import->getNotFoundNiks());

            $message = "Berhasil update {$updatedCount} data ulang tahun.";

            if ($notFoundCount > 0) {
                $message .= " {$notFoundCount} NIK tidak ditemukan di database.";
            }

            return redirect()->route('employees.index')->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->route('employees.index')->with('error', 'Gagal import data ulang tahun: ' . $e->getMessage());
        }
    }

    public function todayBirthdays()
    {
        $today = Carbon::today();

        // Ambil karyawan yang ulang tahun hari ini (bulan dan tanggal sama)
        $employees = Employee::whereNotNull('tgl_lahir')
            ->whereRaw('MONTH(tgl_lahir) = ?', [$today->month])
            ->whereRaw('DAY(tgl_lahir) = ?', [$today->day])
            ->orderBy('nama')
            ->get();

        // Hitung umur untuk setiap karyawan
        foreach ($employees as $employee) {
            $birthDate = Carbon::parse($employee->tgl_lahir);
            $employee->age = $birthDate->age;
        }

        return view('employees.birthdays', compact('employees'));
    }

    /**
     * Get data ulang tahun hari ini untuk notifikasi (JSON)
     */
    public function getTodayBirthdaysNotification()
    {
        $today = Carbon::today();

        $employees = Employee::whereNotNull('tgl_lahir')
            ->whereRaw('MONTH(tgl_lahir) = ?', [$today->month])
            ->whereRaw('DAY(tgl_lahir) = ?', [$today->day])
            ->orderBy('nama')
            ->get(['nik', 'nama', 'tgl_lahir', 'kota_lahir', 'nama_unit']);

        // Hitung umur
        foreach ($employees as $employee) {
            $birthDate = Carbon::parse($employee->tgl_lahir);
            $employee->age = $birthDate->age;
        }

        return response()->json([
            'count' => $employees->count(),
            'employees' => $employees
        ]);
    }

    /**
     * Upcoming birthdays (7 hari ke depan)
     */
    public function upcomingBirthdays()
    {
        $today = Carbon::today();
        $nextWeek = Carbon::today()->addDays(7);

        $employees = Employee::whereNotNull('tgl_lahir')
            ->get()
            ->filter(function ($employee) use ($today, $nextWeek) {
                $birthDate = Carbon::parse($employee->tgl_lahir);
                $thisBirthday = $birthDate->copy()->setYear($today->year);

                // Jika ulang tahun sudah lewat tahun ini, gunakan tahun depan
                if ($thisBirthday->lt($today)) {
                    $thisBirthday->addYear();
                }

                return $thisBirthday->between($today, $nextWeek);
            })
            ->sortBy(function ($employee) use ($today) {
                $birthDate = Carbon::parse($employee->tgl_lahir);
                $thisBirthday = $birthDate->copy()->setYear($today->year);

                if ($thisBirthday->lt($today)) {
                    $thisBirthday->addYear();
                }

                return $thisBirthday->format('md');
            });

        // Hitung umur dan hari tersisa
        foreach ($employees as $employee) {
            $birthDate = Carbon::parse($employee->tgl_lahir);
            $employee->age = $birthDate->age + 1; // Umur yang akan datang

            $thisBirthday = $birthDate->copy()->setYear($today->year);
            if ($thisBirthday->lt($today)) {
                $thisBirthday->addYear();
            }
            $employee->days_until = $today->diffInDays($thisBirthday);
        }

        return view('employees.upcoming_birthdays', compact('employees'));
    }


    public function getAgeGroupDetails($unit, $group)
    {
        $ageRanges = [
            '< 30'    => [0, 29],
            '30 - 40' => [30, 40],
            '41 - 50' => [41, 50],
            '> 50'    => [51, 200],
        ];

        if (!isset($ageRanges[$group])) {
            return response()->json([]);
        }

        [$minAge, $maxAge] = $ageRanges[$group];

        $employees = \App\Models\Employee::where('UNIT', $unit)
            ->whereBetween('usia', [$minAge, $maxAge])
            ->select('nama', 'usia')
            ->orderBy('nama')
            ->get();

        return response()->json($employees);
    }

    /**
     * Get detail karyawan berdasarkan UNIT dan kelompok Durasi Band Posisi (lama_band_posisi dalam bulan).
     */
    public function getBandDurationDetails($unit, $group)
    {
        $durationRanges = [
            // Disesuaikan dengan logika di bandPositionDurationChartData
            '< 2 Tahun'     => [0, 23],  // < 24 bulan
            '2 - 5 Tahun'   => [24, 60], // 24 hingga 60 bulan
            '5 - 10 Tahun'     => [61, 119], // > 60 bulan
            '> 10 Tahun'     => [120, 999], // > 120 bulan

        ];

        if (!isset($durationRanges[$group])) {
            return response()->json([]);
        }

        [$minDuration, $maxDuration] = $durationRanges[$group];

        $employees = \App\Models\Employee::where('UNIT', $unit)
            ->whereBetween('lama_band_posisi', [$minDuration, $maxDuration])
            ->select('nama', 'lama_band_posisi')
            ->orderBy('nama')
            ->get();

        return response()->json($employees);
    }


    //  Get detail karyawan berdasarkan UNIT saja. (BARU)

    public function getUnitDetails($unit)
    {
        $employees = \App\Models\Employee::where('UNIT', $unit)
            ->select('nama', 'band_posisi', 'nama_posisi', 'status_eligibility')
            ->orderBy('nama')
            ->get();

        return response()->json($employees);
    }


    public function getBandPositionDetails($unit, $band)
    {
        $validBands = ['I', 'II', 'III', 'IV', 'V', 'VI'];

        if (!in_array($band, $validBands)) {
            return response()->json([]);
        }

        $employees = \App\Models\Employee::where('UNIT', $unit)
            ->where('band_posisi', $band)
            ->where('status_eligibility', 'Eligible')
            ->select('nama', 'band_posisi', 'nama_posisi', 'status_eligibility')
            ->orderBy('nama')
            ->get();

        return response()->json($employees);
    }

    public function export()
    {
        // Menggunakan kelas EmployeesExport untuk mengambil data dan mengunduhnya sebagai file .xlsx
        $fileName = 'data_karyawan_all_'.Carbon::now()->format('Ymd_His').'.xlsx';
        return Excel::download(new EmployeesExport(), $fileName);
    }

    public function importTc(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt,xlsx,xls|max:4096',
        ]);

        $import = new TcUpdateImport();

        try {
            Excel::import($import, $request->file('file'));

            $updatedCount = count($import->getUpdatedNiks());
            $notFoundCount = count($import->getNotFoundNiks());

            $message = "Berhasil update kolom TC untuk {$updatedCount} karyawan.";

            if ($notFoundCount > 0) {
                $message .= " {$notFoundCount} NIK tidak ditemukan di database.";
            }

            return redirect()->route('employees.index')->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->route('employees.index')->with('error', 'Gagal import data TC: ' . $e->getMessage());
        }
    }

    public function getAllUnitEmployeeCounts()
    {
        // Mendapatkan total hitungan karyawan untuk setiap UNIT tanpa filter status_eligibility
        $data = Employee::select('UNIT', \Illuminate\Support\Facades\DB::raw('count(*) as total_count'))
                        ->groupBy('UNIT')
                        ->orderBy('UNIT')
                        ->get();

        // Mengubah format menjadi associative array: ['UNIT A' => 100, 'UNIT B' => 50, ...]
        $unitCounts = $data->pluck('total_count', 'UNIT')->all();

        // Mengembalikan data dalam format JSON
        return response()->json($unitCounts);
    }

}
