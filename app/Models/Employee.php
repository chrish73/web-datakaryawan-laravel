<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'tahun',
        'bulan',
        'nik',
        'nama',
        'tgl_lahir',
        'jenis_kelamin',
        'nama_agama',
        'usia',
        'kelompok_usia',
        'tgl_capeg',
        'tgl_pegprus',
        'tgl_mulaikerja',
        'tgl_pensiun',
        'nama_employee_group',
        'nama_employee_subgroup',
        'kode_personnel_area',
        'kode_host',
        'kode_function_unit',
        'nama_function_unit',
        'kode_personnel_subarea',
        'nama_personnel_subarea',
        'tgl_psa',
        'kode_payroll_area',
        'nama_payroll_area',
        'kode_divisi',
        'tgl_divisi',
        'nama_divisi',
        'kode_unit',
        'tgl_unit',
        'nama_unit',
        'long_unit',
        'witel',
        'objidposisi',
        'tgl_posisi',
        'kode_posisi',
        'nama_posisi',
        'long_posisi',
        'lama_posisi',
        'nama_action',
        'band_posisi',
        'tgl_band_posisi',
        'lama_band_posisi',
        'flag_pj',
        'lama_pj',
        'tahun_kinerja',
        'nilai_kinerja',
        'tahun_kompetensi',
        'nilai_kompetensi',
        'tahun_behavior',
        'nilai_behavior',
        'level_pendidikan',
        'group_pendidikan',
        'jurusan_pendidikan',
        'nama_institusi',
        'kode_perusahaan',
        'nama_perusahaan',
        'kode_home',
        'nama_home',
        'job_family',
        'job_function',
        'job_role',
        'role_category',
        'flag_chief',
        'email',
        'kode_gedung',
        'nama_gedung',
        'alamat_gedung',
        'kota_gedung',
        'unit',
        'tc',
        'status_eligibility'
    ];

    public function trainings()
    {
        return $this->hasMany(Training::class);
    }
}
