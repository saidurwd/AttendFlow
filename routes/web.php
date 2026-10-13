<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ZkTeco\AdmsController;
use App\Http\Controllers\ZkTeco\AttendanceLogController;
use App\Http\Controllers\ZkTeco\DeviceController;
use App\Http\Controllers\ZkTeco\EmployeeController;
use App\Http\Controllers\ZkTeco\MappingController;
use App\Http\Controllers\ZkTeco\ReportController;
use App\Http\Controllers\Hikvision\DeviceController as HikvisionDeviceController;
use App\Http\Controllers\Hikvision\EventController as HikvisionEventController;
use App\Http\Controllers\SqlServer\ReportController as SqlServerReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return redirect()->route('home');
})->middleware('auth');

Route::get('/iclock/cdata', [AdmsController::class, 'handshake']);
Route::post('/iclock/cdata', [AdmsController::class, 'attendance']);
Route::get('/iclock/getrequest', [AdmsController::class, 'getRequest']);
Route::post('/iclock/devicecmd', [AdmsController::class, 'deviceCommand']);

Route::middleware(['web', 'auth'])->prefix('zkteco')->name('zkteco.')->group(function () {
    Route::resource('devices', DeviceController::class);
    Route::patch('devices/{device}/toggle', [DeviceController::class, 'toggleActive'])->name('devices.toggle');

    Route::prefix('devices/{device}/mappings')->name('devices.mappings.')->group(function () {
        Route::get('/', [MappingController::class, 'index'])->name('index');
        Route::get('/create', [MappingController::class, 'create'])->name('create');
        Route::post('/', [MappingController::class, 'store'])->name('store');
        Route::get('/{mapping}/edit', [MappingController::class, 'edit'])->name('edit');
        Route::put('/{mapping}', [MappingController::class, 'update'])->name('update');
        Route::delete('/{mapping}', [MappingController::class, 'destroy'])->name('destroy');
    });

    Route::resource('attendance-logs', AttendanceLogController::class);
    Route::resource('employees', EmployeeController::class);
    Route::get('reports/attendance', [ReportController::class, 'attendance'])->name('reports.attendance');
});

Auth::routes();

Route::middleware('auth')->group(function () {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/profile', [SettingsController::class, 'edit'])->name('edit');
        Route::put('/profile', [SettingsController::class, 'update'])->name('update');
        Route::get('/password', [SettingsController::class, 'password'])->name('password');
        Route::put('/password', [SettingsController::class, 'updatePassword'])->name('password.update');
    });

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::get('users/{user}/change-password', [UserController::class, 'changePassword'])->name('users.change-password');
        Route::put('users/{user}/change-password', [UserController::class, 'updatePassword'])->name('users.change-password.update');
    });

    Route::prefix('sqlserver')->name('sqlserver.')->group(function () {
        Route::get('reports/attendance', [SqlServerReportController::class, 'attendance'])->name('reports.attendance');
    });

    Route::prefix('hikvision')->name('hikvision.')->group(function () {
        Route::resource('devices', HikvisionDeviceController::class);
        Route::resource('events', HikvisionEventController::class)->only(['index', 'show']);
        Route::get('reports/attendance', [HikvisionEventController::class, 'attendance'])->name('reports.attendance');
    });
});
