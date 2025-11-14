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
use App\Imports\TrainingsImport;
use App\Models\Training;
use Carbon\Carbon;
use App\Models\Training as TrainingModel;
use App\Exports\TrainingExport;

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
                            WHEN usia <= 30 THEN '< 30'
                            WHEN usia >= 31 AND usia <= 35 THEN '31 - 35'
                            WHEN usia >= 36 AND usia <= 40 THEN '36 - 40'
                            WHEN usia >= 41 AND usia <= 45 THEN '41 - 45'
                            WHEN usia >= 46 AND usia <= 50 THEN '46 - 50'
                            WHEN usia >= 51 AND usia <= 54 THEN '51 - 54'
                            WHEN usia >= 55 THEN '> 55'
                            ELSE 'Lainnya'
                        END";

        // 1. Mendapatkan data UNIT dan kelompok_usia yang dihitung, lalu menghitungnya
        $data = Employee::select('UNIT', DB::raw("{$ageGroupCase} AS calculated_kelompok_usia"), DB::raw('count(*) as count'))
                        ->whereNotNull('usia') // Memastikan kolom usia ada nilainya
                        ->groupBy('UNIT', DB::raw($ageGroupCase))
                        ->orderBy('UNIT')
                        ->get();

        // 2. Tentukan urutan Kelompok Usia yang diinginkan
        $ageGroupsOrder = ['< 30', '31 - 35', '36 - 40', '41 - 45', '46 - 50', '51 - 54' ,'> 55'];

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

    public function bandPositionDurationChartData(Request $request)
    {
        // 1️⃣ Tentukan band posisi yang valid
        $validBands = ['I', 'II', 'III', 'IV', 'V', 'VI'];
        $selectedBands = $request->input('bands', $validBands);
        $filteredBands = array_intersect($selectedBands, $validBands);

        // 2️⃣ Tentukan kelompok durasi yang valid
        $validDurations = ['< 2 Tahun', '2 - 5 Tahun', '5 - 10 Tahun', '> 10 Tahun'];

        // Jika tidak ada filter Band Posisi aktif
        if (empty($filteredBands)) {
            return response()->json([
                'units' => [],
                'duration_groups' => $validDurations,
                'data' => [],
                'all_bands' => $validBands,
                'bands' => [], // Tambahkan bands kosong
                'raw_data' => [] // Tambahkan raw_data kosong
            ]);
        }

        // 3️⃣ Logika CASE untuk Durasi Band
        $durationGroupCase = "CASE
        WHEN lama_band_posisi < 24 THEN '< 2 Tahun'
        WHEN lama_band_posisi BETWEEN 24 AND 60 THEN '2 - 5 Tahun'
        WHEN lama_band_posisi BETWEEN 61 AND 120 THEN '5 - 10 Tahun'
        WHEN lama_band_posisi > 120 THEN '> 10 Tahun'
        ELSE 'Lainnya'
    END";

        // 4️⃣ Query yang mengelompokkan data secara granular
        $data = Employee::query()
            ->whereNotNull('lama_band_posisi')
            ->whereIn('band_posisi', $filteredBands) // Filter Band Posisi (Server-side)
            ->select(
                'UNIT',
                'band_posisi', // Tambahkan grouping Band Posisi
                DB::raw("{$durationGroupCase} AS calculated_duration_group"),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('UNIT', 'band_posisi', DB::raw($durationGroupCase)) // Grouping granular
            ->orderBy('UNIT')
            ->orderBy('band_posisi')
            ->get();

        // 5️⃣ Siapkan data yang diperlukan frontend untuk pivoting
        $units = $data->pluck('UNIT')->unique()->sort()->values();
        $bandsInResult = $data->pluck('band_posisi')->unique()->sort()->values();

        // 6️⃣ Kirim raw data granular ke frontend
        return response()->json([
            'units' => $units,
            'duration_groups' => $validDurations, // Untuk Checkbox Durasi
            'bands' => $bandsInResult,            // Bands yang ditemukan di data
            'all_bands' => $validBands,          // Semua bands yang mungkin (untuk Checkbox Band Posisi)
            'raw_data' => $data->toArray()       // Data granular
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
            '< 30'    => [0, 30],
            '31 - 35' => [31, 35],
            '36 - 40' => [36, 40],
            '41 - 45' => [41, 45],
            '46 - 50' => [46, 50],
            '51 - 54' => [51, 54],
            '> 55'    => [55, 200],
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

    public function getBandDurationDetails($unit, $group, Request $request)
    {
        // Rentang durasi harus sesuai dengan logika CASE di bandPositionDurationChartData
        $durationRanges = [
            '< 2 Tahun'     => [0, 23],
            '2 - 5 Tahun'   => [24, 60],
            '5 - 10 Tahun'  => [61, 120],
            '> 10 Tahun'    => [121, 999],

        ];

        if (!isset($durationRanges[$group])) {
            return response()->json([]);
        }

        [$minDuration, $maxDuration] = $durationRanges[$group];

        // Membangun query dasar (Filter Durasi Band dan Unit)
        $query = \App\Models\Employee::where('UNIT', $unit)
            ->whereBetween('lama_band_posisi', [$minDuration, $maxDuration]);

        // Menerapkan Filter Band Posisi (dari query parameter)
        $selectedBands = $request->input('bands');
        if (!empty($selectedBands) && is_array($selectedBands)) {
            $query->whereIn('band_posisi', $selectedBands);
        }

        $employees = $query->select('nama', 'lama_band_posisi', 'band_posisi', 'nik')
            ->orderBy('nama')
            ->get();

        return response()->json($employees);
    }

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

public function showTrainingInput(Request $request)
    {
        $search = $request->get('search');

        // 1. Ambil data Karyawan dengan Search dan Pagination
        $employees = Employee::when($search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%");
            });
        })->with('trainings')->orderBy('nama')->simplePaginate(10);

        // 2. Ambil semua nama pelatihan unik yang sudah ada
        $uniqueTrainings = Training::select('nama_pelatihan')
                                 ->distinct()
                                 ->orderBy('nama_pelatihan')
                                 ->pluck('nama_pelatihan');

        // BARU: Ambil mapping nama_pelatihan ke id_event (ambil event ID dari record terbaru/terakhir)
        $trainingEventMap = Training::select('nama_pelatihan', 'id_event')
            ->whereNotNull('id_event')
            ->orderBy('tanggal_mulai', 'desc') // Urutkan untuk mendapatkan yang terbaru
            ->get()
            ->groupBy('nama_pelatihan')
            ->mapWithKeys(function ($item) {
                // Ambil id_event dari record pertama dalam grup (yang paling baru/sesuai urutan)
                return [$item->first()->nama_pelatihan => $item->first()->id_event];
            })->toArray();

        // 3. Melewatkan semua variabel ke view, termasuk mapping baru
        return view('employees.training', compact('employees', 'uniqueTrainings', 'search', 'trainingEventMap'));
    }

    public function storeTraining(Request $request)
    {
        $request->validate([
            'employee_id'                    => 'required|exists:employees,id',
            'trainings'                      => 'nullable|array',
            'trainings.*.nama_pelatihan'     => 'required|string|max:255',
            'trainings.*.tanggal_mulai'      => 'nullable|date',
            'trainings.*.tanggal_selesai'    => 'nullable|date|after_or_equal:trainings.*.tanggal_mulai',
            'trainings.*.status_pelatihan'   => 'nullable|in:Online,Offline',
            'trainings.*.id_event'           => 'nullable|string|max:255', // <--- TAMBAH VALIDASI id_event
        ]);

        $employee = Employee::findOrFail($request->employee_id);

        if ($request->has('trainings') && is_array($request->trainings)) {
            $trainingsToSave = [];
            foreach ($request->trainings as $training) {
                if (!empty($training['nama_pelatihan'])) {
                    $trainingsToSave[] = new TrainingModel([
                        'nama_pelatihan'   => $training['nama_pelatihan'],
                        'tanggal_mulai'    => $training['tanggal_mulai'] ?? null,
                        'tanggal_selesai'  => $training['tanggal_selesai'] ?? null,
                        'status_pelatihan' => $training['status_pelatihan'] ?? null,
                        'id_event'         => $training['id_event'] ?? null, // <--- SIMPAN id_event
                    ]);
                }
            }

            if (!empty($trainingsToSave)) {
                $employee->trainings()->saveMany($trainingsToSave);
            }
        }

        return redirect()->back()->with('success', "Data pelatihan untuk {$employee->nama} berhasil disimpan!");
    }

    public function updateTraining(Request $request, Training $training)
    {
        $request->validate([
            'nama_pelatihan' => 'required|string|max:255',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'status_pelatihan' => 'nullable|in:Online,Offline',
            'id_event' => 'nullable|string|max:255',
        ]);

        $training->update($request->only([
            'nama_pelatihan',
            'tanggal_mulai',
            'tanggal_selesai',
            'status_pelatihan',
            'id_event',
        ]));

        return redirect()->route('employees.training_input')->with('success', "Data pelatihan '{$training->nama_pelatihan}' berhasil diperbarui!");
    }

    /**
     * Menghapus catatan pelatihan tertentu.
     * Menggunakan Route Model Binding untuk mendapatkan instance Training.
     */
    public function deleteTraining(Training $training)
    {
        $employeeName = $training->employee->nama;
        $trainingName = $training->nama_pelatihan;

        $training->delete();

        return redirect()->back()->with('success', "Pelatihan '{$trainingName}' milik {$employeeName} berhasil dihapus.");
    }

    public function importTraining(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt,xlsx,xls|max:4096',
        ]);

        try {
            // Panggil kelas import baru
            \Maatwebsite\Excel\Facades\Excel::import(new TrainingsImport(), $request->file('file'));

            return redirect()->route('employees.training_input')->with('success', 'Berhasil mengimpor data pelatihan dari Excel!');

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errors = [];
            foreach ($failures as $failure) {
                // Kumpulkan error validasi per baris
                $errors[] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
            }

            return redirect()->route('employees.training_input')->with('error', 'Gagal impor karena validasi data: ' . implode('; ', $errors));

        } catch (\Exception $e) {
            return redirect()->route('employees.training_input')->with('error', 'Gagal impor: ' . $e->getMessage());
        }
    }

    public function exportTraining()
    {
        // Menggunakan kelas TrainingExport untuk mengambil data dan mengunduhnya sebagai file .xlsx
        $fileName = 'data_pelatihan_'.Carbon::now()->format('Ymd_His').'.xlsx';
        return Excel::download(new TrainingExport(), $fileName);
    }

}
