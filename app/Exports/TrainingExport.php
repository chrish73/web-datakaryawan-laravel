<?php

namespace App\Exports;

use App\Models\Training;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class TrainingExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // Mengambil semua data pelatihan dan melakukan JOIN ke tabel employees
        return Training::select(
            'employees.nik',
            'employees.nama',
            'employees.unit',
            'trainings.id_event',
            'trainings.nama_pelatihan',
            'trainings.tanggal_mulai',
            'trainings.tanggal_selesai',
            'trainings.status_pelatihan'
        )
        // Pastikan relasi employee_id benar
        ->join('employees', 'trainings.employee_id', '=', 'employees.id')
        ->orderBy('employees.nama')
        ->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'NIK',
            'Nama Karyawan',
            'Unit',
            'ID Event',
            'Nama Event',
            'Tanggal Mulai',
            'Tanggal Selesai',
            'Status',
        ];
    }
}
