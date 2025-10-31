<?php
// chrish73/web-datakaryawan-laravel/web-datakaryawan-laravel-a9e5ba7e92801b6f13d23cc83551ccb244f61a1a/routes/web.php

use App\Http\Controllers\EmployeeController;
use Illuminate\Support\Facades\Route;


Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
Route::post('/employees/import', [EmployeeController::class, 'import'])->name('employees.import');

// Route Band Posisi
Route::get('/employees/chart-data', [EmployeeController::class, 'bandPositionChartData'])->name('employees.chart_data');
Route::get('/', function () {
    return view('employees.chart'); // Route view band posisi
})->name('employees.chart');

// --- ROUTE KELOMPOK USIA (SUDAH DIPERBAIKI) ---

// Route untuk MENGAMBIL DATA (API)
Route::get('/employees/age-data', [EmployeeController::class, 'ageGroupChartData'])->name('employees.age_group_chart_data');
// Route untuk MEMUAT TAMPILAN (VIEW)
Route::get('/employees/age', function () {
    return view('employees.age_group_chart');
})->name('employees.age_group_chart');


Route::get('/employees/band-duration-data', [EmployeeController::class, 'bandPositionDurationChartData'])->name('employees.band_duration_data');

// Route untuk MEMUAT TAMPILAN (VIEW)
Route::get('/employees/band', function () {
    return view('employees.band_duration_chart');
})->name('employees.band_duration_chart');
