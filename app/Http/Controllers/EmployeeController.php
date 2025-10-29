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

            try {
                \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\EmployeesImport, $request->file('file'));
                return redirect()->route('employees.index')->with('success', 'Berhasil impor atau perbarui data!');
            } catch (\Exception $e) {
                return redirect()->route('employees.index')->with('error', 'Gagal impor: ' . $e->getMessage());
            }
        }


}
