<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'nik',
        'nama',
        'nama_unit',
        'lama_band_posisi',
        'nilai_kinerja',
        'nilai_kompetensi',
        'nilai_behavior',
        'tc',
        'status',
    ];
}
