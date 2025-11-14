<?php
// chrish73/web-datakaryawan-laravel/web-datakaryawan-laravel-a9e5ba7e92801b6f13d23cc83551ccb244f61a1a/routes/web.php
use App\Http\Controllers\EmployeeController;
use Illuminate\Support\Facades\Route;


Route::get('/', [EmployeeController::class, 'index'])->name('employees.index');
Route::post('/employees/import', [EmployeeController::class, 'import'])->name('employees.import');

// Route Band Posisi
Route::get('/employees/chart-data', [EmployeeController::class, 'bandPositionChartData'])->name('employees.chart_data');
Route::get('/employees', function () {return view('employees.chart');})->name('employees.chart');


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


// --- START: NEW BIRTHDAY ROUTES ---
Route::post('/employees/import-birthday', [EmployeeController::class, 'importBirthday'])->name('employees.import_birthday');
Route::get('/employees/birthdays/today', [EmployeeController::class, 'todayBirthdays'])->name('employees.today_birthdays');
Route::get('/employees/birthdays/upcoming', [EmployeeController::class, 'upcomingBirthdays'])->name('employees.upcoming_birthdays');
Route::get('/employees/birthdays/notification', [EmployeeController::class, 'getTodayBirthdaysNotification'])->name('employees.birthdays_notification');

Route::get('/employees/all-unit-counts', [EmployeeController::class, 'getAllUnitEmployeeCounts'])->name('employees.all_unit_counts');

Route::get('/employees/age-group-detail/{unit}/{group}', [EmployeeController::class, 'getAgeGroupDetails'])
    ->name('employees.age_group_detail');
Route::get('employees/band-duration-detail/{unit}/{group}', [EmployeeController::class, 'getBandDurationDetails']);
Route::get('employees/unit-detail/{unit}', [EmployeeController::class, 'getUnitDetails']);
Route::get('employees/band-position-detail/{unit}/{band}', [EmployeeController::class, 'getBandPositionDetails']);

Route::get('/employees/export', [EmployeeController::class, 'export'])->name('employees.export');
Route::post('/employees/import-tc', [EmployeeController::class, 'importTc'])->name('employees.import_tc');

Route::post('/employees/training/import-excel', [EmployeeController::class, 'importTraining'])->name('trainings.import_excel');
Route::get('/employees/training', [EmployeeController::class, 'showTrainingInput'])->name('employees.training_input');
Route::post('/employees/training', [EmployeeController::class, 'storeTraining'])->name('employees.store_training');
Route::put('/trainings/{training}', [EmployeeController::class, 'updateTraining'])->name('trainings.update');
Route::delete('/trainings/{training}', [EmployeeController::class, 'deleteTraining'])->name('trainings.delete');
Route::get('/employees/training/export', [EmployeeController::class, 'exportTraining'])->name('trainings.export');
