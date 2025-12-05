<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\AdminKelasController;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return redirect()->route('monitor.index');
});

Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    // Profile Password Update
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // Admin Routes
    Route::prefix('admin')->middleware('role:super_admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

        // Teachers CRUD
        Route::get('/teachers', [AdminController::class, 'teachers'])->name('teachers.index');
        Route::get('/teachers/create', [AdminController::class, 'teacherCreate'])->name('teachers.create');
        Route::post('/teachers', [AdminController::class, 'teacherStore'])->name('teachers.store');
        Route::post('/teachers/import', [AdminController::class, 'teacherImport'])->name('teachers.import');
        Route::get('/teachers/template', [AdminController::class, 'teacherTemplate'])->name('teachers.template');
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
        Route::get('/attendance/export', [AdminController::class, 'attendanceExport'])->name('attendance.export');
        Route::put('/attendance/{attendance}/status', [AdminController::class, 'updateAttendanceStatus'])->name('attendance.update-status');
        Route::delete('/attendance/{attendance}', [AdminController::class, 'attendanceDestroy'])->name('attendance.destroy');

        // Schedules Management (Admin can manage all schedules)
        Route::get('/schedules', [AdminController::class, 'schedules'])->name('schedules.index');
        Route::get('/schedules/create', [AdminController::class, 'scheduleCreate'])->name('schedules.create');
        Route::post('/schedules', [AdminController::class, 'scheduleStore'])->name('schedules.store');
        Route::delete('/schedules/{schedule}', [AdminController::class, 'scheduleDestroy'])->name('schedules.destroy');

        // Subjects Management
        Route::get('/subjects', [AdminController::class, 'subjects'])->name('subjects.index');
        Route::post('/subjects', [AdminController::class, 'subjectStore'])->name('subjects.store');
        Route::post('/subjects/import', [AdminController::class, 'subjectImport'])->name('subjects.import');
        Route::get('/subjects/template', [AdminController::class, 'subjectTemplate'])->name('subjects.template');
        Route::put('/subjects/{subject}', [AdminController::class, 'subjectUpdate'])->name('subjects.update');
        Route::delete('/subjects/{subject}', [AdminController::class, 'subjectDestroy'])->name('subjects.destroy');

        // Manual Attendance
        Route::get('/manual-attendance', [AdminController::class, 'manualAttendance'])->name('manual-attendance.index');
        Route::post('/manual-attendance', [AdminController::class, 'manualAttendanceStore'])->name('manual-attendance.store');
        Route::delete('/manual-attendance/{attendance}', [AdminController::class, 'manualAttendanceDestroy'])->name('manual-attendance.destroy');

        // Reset Password
        Route::put('/users/{user}/reset-password', [AdminController::class, 'resetPassword'])->name('users.reset-password');
    });

    // Teacher Routes
    Route::prefix('teacher')->middleware('role:guru')->name('teacher.')->group(function () {
        Route::get('/dashboard', [TeacherController::class, 'dashboard'])->name('dashboard');
        Route::get('/schedules', [TeacherController::class, 'schedules'])->name('schedules');
        Route::post('/schedules', [TeacherController::class, 'scheduleStore'])->name('schedules.store');
        Route::put('/schedules/{schedule}', [TeacherController::class, 'scheduleUpdate'])->name('schedules.update');
        Route::delete('/schedules/{schedule}', [TeacherController::class, 'scheduleDestroy'])->name('schedules.destroy');
        Route::post('/profile-photo', [TeacherController::class, 'uploadProfilePhoto'])->name('profile-photo.store');
    });

    // Admin Kelas Routes
    Route::prefix('kelas')->middleware('role:admin_kelas')->name('admin_kelas.')->group(function () {
        Route::get('/dashboard', [AdminKelasController::class, 'dashboard'])->name('dashboard');
    });

    // API for QR Scan
    Route::post('/api/scan', [AttendanceController::class, 'store'])->middleware('role:guru');
    Route::post('/api/attendance/{attendance}/photo', [AttendanceController::class, 'uploadPhoto'])->middleware('role:guru');
});

// Public Monitoring
Route::get('/monitor', [MonitoringController::class, 'index'])->name('monitor.index');
Route::get('/api/monitoring', [MonitoringController::class, 'getData']);
Route::get('/api/monitoring/block', [MonitoringController::class, 'getBlockData']);
Route::get('/api/teacher/{user}/subjects', [AdminController::class, 'getTeacherSubjects'])->middleware('auth');
