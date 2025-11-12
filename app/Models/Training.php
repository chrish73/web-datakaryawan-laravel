<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'nama_pelatihan',
        'tanggal_mulai',
        'tanggal_selesai',
        'status_pelatihan',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
