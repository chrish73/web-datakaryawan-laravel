<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\EmployeesImport;


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


}
