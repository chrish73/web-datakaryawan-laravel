<?php

namespace App\Http\Controllers;

use App\Imports\BirthdayImport;
use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\EmployeesImport;
use Carbon\Carbon;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $employees = Employee::when($search, function ($query, $search) {
            $query->where('nama', 'like', "%{$search}%");
        })->paginate(10);

        return view('employees.index', compact('employees', 'search'));
    }


    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt,xlsx,xls|max:4096',
        ]);

        // Buat instance dari EmployeesImport
        $import = new EmployeesImport;

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
                                WHEN lama_band_posisi > 60 THEN '> 5 Tahun'
                                ELSE 'Lainnya'
                            END";

        // 1. Mendapatkan data UNIT dan kelompok durasi yang dihitung, lalu menghitungnya
        $data = Employee::select('UNIT', DB::raw("{$durationGroupCase} AS calculated_duration_group"), DB::raw('count(*) as count'))
                        ->whereNotNull('lama_band_posisi') // Memastikan kolom lama_bandposisi ada nilainya
                        ->groupBy('UNIT', DB::raw($durationGroupCase))
                        ->orderBy('UNIT')
                        ->get();

        // 2. Tentukan urutan Durasi yang diinginkan
        $durationGroupsOrder = ['< 2 Tahun', '2 - 5 Tahun', '> 5 Tahun'];

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

    $import = new BirthdayImport;

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
        ->filter(function($employee) use ($today, $nextWeek) {
            $birthDate = Carbon::parse($employee->tgl_lahir);
            $thisBirthday = $birthDate->copy()->setYear($today->year);

            // Jika ulang tahun sudah lewat tahun ini, gunakan tahun depan
            if ($thisBirthday->lt($today)) {
                $thisBirthday->addYear();
            }

            return $thisBirthday->between($today, $nextWeek);
        })
        ->sortBy(function($employee) use ($today) {
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
}
