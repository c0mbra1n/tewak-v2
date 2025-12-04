<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\MonitoringController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    // Admin Routes
    Route::prefix('admin')->middleware('role:super_admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

        // Teachers CRUD
        Route::get('/teachers', [AdminController::class, 'teachers'])->name('teachers.index');
        Route::get('/teachers/create', [AdminController::class, 'teacherCreate'])->name('teachers.create');
        Route::post('/teachers', [AdminController::class, 'teacherStore'])->name('teachers.store');
        Route::get('/teachers/{user}/edit', [AdminController::class, 'teacherEdit'])->name('teachers.edit');
        Route::put('/teachers/{user}', [AdminController::class, 'teacherUpdate'])->name('teachers.update');
        Route::delete('/teachers/{user}', [AdminController::class, 'teacherDestroy'])->name('teachers.destroy');

        // Classes CRUD
        Route::get('/classes', [AdminController::class, 'classes'])->name('classes.index');
        Route::get('/classes/create', [AdminController::class, 'classCreate'])->name('classes.create');
        Route::post('/classes', [AdminController::class, 'classStore'])->name('classes.store');
        Route::get('/classes/{classRoom}/edit', [AdminController::class, 'classEdit'])->name('classes.edit');
        Route::put('/classes/{classRoom}', [AdminController::class, 'classUpdate'])->name('classes.update');
        Route::delete('/classes/{classRoom}', [AdminController::class, 'classDestroy'])->name('classes.destroy');

        // Attendance Report
        Route::get('/attendance', [AdminController::class, 'attendance'])->name('attendance.index');
        Route::put('/attendance/{attendance}/status', [AdminController::class, 'updateAttendanceStatus'])->name('attendance.update-status');

        // Schedules Management (Admin can manage all schedules)
        Route::get('/schedules', [AdminController::class, 'schedules'])->name('schedules.index');
        Route::get('/schedules/create', [AdminController::class, 'scheduleCreate'])->name('schedules.create');
        Route::post('/schedules', [AdminController::class, 'scheduleStore'])->name('schedules.store');
        Route::delete('/schedules/{schedule}', [AdminController::class, 'scheduleDestroy'])->name('schedules.destroy');
    });

    // Teacher Routes
    Route::prefix('teacher')->middleware('role:guru')->name('teacher.')->group(function () {
        Route::get('/dashboard', [TeacherController::class, 'dashboard'])->name('dashboard');
        Route::get('/schedules', [TeacherController::class, 'schedules'])->name('schedules');
        Route::post('/schedules', [TeacherController::class, 'scheduleStore'])->name('schedules.store');
        Route::delete('/schedules/{schedule}', [TeacherController::class, 'scheduleDestroy'])->name('schedules.destroy');
    });

    // API for QR Scan
    Route::post('/api/scan', [AttendanceController::class, 'store'])->middleware('role:guru');

    // Class Admin Routes
    Route::get('/admin/class/dashboard', function () {
        return "Class Admin Dashboard";
    })->middleware('role:admin_kelas');
});

// Public Monitoring
Route::get('/monitor', [MonitoringController::class, 'index'])->name('monitor.index');
Route::get('/api/monitoring', [MonitoringController::class, 'getData']);
