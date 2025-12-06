<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\GeofenceViolation;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function index()
    {
        $totalTeachers = User::where('role', 'guru')->count();
        $totalClasses = ClassRoom::count();

        $today = date('Y-m-d');
        $attendanceToday = Attendance::where('date', $today)->where('status', 'hadir')->count();
        $lateToday = Attendance::where('date', $today)->where('status', 'telat')->count();

        // Recent Activities
        $recentActivities = Attendance::with(['user', 'classRoom'])
            ->where('date', $today)
            ->latest('scan_time')
            ->take(5)
            ->get();

        // Geofence Violations (unread)
        $geofenceViolations = GeofenceViolation::with('user')
            ->where('is_read', false)
            ->latest()
            ->take(10)
            ->get();

        $unreadViolationsCount = GeofenceViolation::where('is_read', false)->count();

        return view('admin.dashboard', compact(
            'totalTeachers',
            'totalClasses',
            'attendanceToday',
            'lateToday',
            'recentActivities',
            'geofenceViolations',
            'unreadViolationsCount'
        ));
    }

    // ================================
    // TEACHERS CRUD (with role selection)
    // ================================

    public function teachers()
    {
        $teachers = User::with(['assignedClass', 'subjects'])->whereIn('role', ['guru', 'admin_kelas'])->latest()->get();
        return view('admin.teachers.index', compact('teachers'));
    }

    public function teacherImport(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\UsersImport, $request->file('file'));
            return redirect()->route('admin.teachers.index')->with('success', 'Data user berhasil diimport!');
        } catch (\Exception $e) {
            return redirect()->route('admin.teachers.index')->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    public function teacherTemplate()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\UsersTemplateExport,
            'template_import_user.xlsx'
        );
    }

    public function teacherCreate()
    {
        $classes = ClassRoom::all();
        $subjects = Subject::orderBy('name')->get();
        return view('admin.teachers.create', compact('classes', 'subjects'));
    }

    public function teacherStore(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:users,username',
            'full_name' => 'required|string|max:255',
            'role' => 'required|in:guru,admin_kelas',
            'subjects' => 'nullable|array',
            'subjects.*' => 'exists:subjects,id',
            'password' => 'required|min:6',
        ]);

        $user = User::create([
            'username' => $request->username,
            'full_name' => $request->full_name,
            'role' => $request->role,
            'class_id' => $request->role === 'admin_kelas' ? $request->class_id : null,
            'password' => Hash::make($request->password),
        ]);

        // Attach subjects for guru
        if ($request->role === 'guru' && $request->subjects) {
            $user->subjects()->attach($request->subjects);
        }

        $roleLabel = $request->role === 'guru' ? 'Guru' : 'Admin Kelas';
        return redirect()->route('admin.teachers.index')->with('success', "$roleLabel berhasil ditambahkan!");
    }

    public function teacherEdit(User $user)
    {
        $classes = ClassRoom::all();
        $subjects = Subject::orderBy('name')->get();
        return view('admin.teachers.edit', compact('user', 'classes', 'subjects'));
    }

    public function teacherUpdate(Request $request, User $user)
    {
        $request->validate([
            'username' => 'required|unique:users,username,' . $user->id,
            'full_name' => 'required|string|max:255',
            'role' => 'required|in:guru,admin_kelas',
            'subjects' => 'nullable|array',
            'subjects.*' => 'exists:subjects,id',
            'password' => 'nullable|min:6',
        ]);

        $data = [
            'username' => $request->username,
            'full_name' => $request->full_name,
            'role' => $request->role,
            'class_id' => $request->role === 'admin_kelas' ? $request->class_id : null,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // Sync subjects for guru
        if ($request->role === 'guru') {
            $user->subjects()->sync($request->subjects ?? []);
        } else {
            $user->subjects()->detach();
        }

        return redirect()->route('admin.teachers.index')->with('success', 'Data user berhasil diperbarui!');
    }

    public function teacherDestroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.teachers.index')->with('success', 'User berhasil dihapus!');
    }

    // ================================
    // CLASSES CRUD
    // ================================

    public function classes()
    {
        $classes = ClassRoom::latest()->get();
        return view('admin.classes.index', compact('classes'));
    }

    public function classCreate()
    {
        return view('admin.classes.create');
    }

    public function classStore(Request $request)
    {
        $request->validate([
            'class_name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'required|numeric|min:1',
            'block' => 'nullable|integer|between:1,10',
        ]);

        $qrCode = 'CLASS_' . strtoupper(str_replace(' ', '_', $request->class_name)) . '_' . time();

        ClassRoom::create([
            'class_name' => $request->class_name,
            'qr_code' => $qrCode,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'radius' => $request->radius,
            'block' => $request->block,
        ]);

        return redirect()->route('admin.classes.index')->with('success', 'Kelas berhasil ditambahkan!');
    }

    public function classEdit(ClassRoom $classRoom)
    {
        return view('admin.classes.edit', compact('classRoom'));
    }

    public function classUpdate(Request $request, ClassRoom $classRoom)
    {
        $request->validate([
            'class_name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'required|numeric|min:1',
            'block' => 'nullable|integer|between:1,10',
        ]);

        $classRoom->update([
            'class_name' => $request->class_name,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'radius' => $request->radius,
            'block' => $request->block,
        ]);

        return redirect()->route('admin.classes.index')->with('success', 'Data kelas berhasil diperbarui!');
    }

    public function classDestroy(ClassRoom $classRoom)
    {
        $classRoom->delete();
        return redirect()->route('admin.classes.index')->with('success', 'Kelas berhasil dihapus!');
    }

    // ================================
    // ATTENDANCE REPORT & STATUS UPDATE
    // ================================

    public function attendance(Request $request)
    {
        $filterType = $request->get('filter_type', 'day');
        $date = $request->get('date', date('Y-m-d'));
        $month = $request->get('month', date('m'));
        $year = $request->get('year', date('Y'));
        $status = $request->get('status');
        $userId = $request->get('user_id');
        $classId = $request->get('class_id');

        $query = Attendance::with(['user', 'classRoom']);

        // Apply date filter based on filter type
        if ($filterType === 'day') {
            $query->where('date', $date);
        } elseif ($filterType === 'month') {
            $query->whereMonth('date', $month)->whereYear('date', $year);
        } elseif ($filterType === 'year') {
            $query->whereYear('date', $year);
        }

        // Apply other filters
        if ($status) {
            $query->where('status', $status);
        }
        if ($userId) {
            $query->where('user_id', $userId);
        }
        if ($classId) {
            $query->where('class_id', $classId);
        }

        $attendances = $query->latest('date')->latest('scan_time')->get();

        // Summary stats based on current filter
        $summaryQuery = clone $query;
        $summary = [
            'hadir' => (clone $summaryQuery)->where('status', 'hadir')->count(),
            'telat' => (clone $summaryQuery)->where('status', 'telat')->count(),
            'izin' => (clone $summaryQuery)->where('status', 'izin')->count(),
            'sakit' => (clone $summaryQuery)->where('status', 'sakit')->count(),
            'alpa' => (clone $summaryQuery)->where('status', 'alpa')->count(),
            'dinas' => (clone $summaryQuery)->where('status', 'dinas')->count(),
        ];

        // Get teachers and classes for filter dropdowns
        $teachers = User::where('role', 'guru')->orderBy('full_name')->get();
        $classes = ClassRoom::orderBy('class_name')->get();

        return view('admin.attendance.index', compact(
            'attendances',
            'date',
            'month',
            'year',
            'status',
            'filterType',
            'userId',
            'classId',
            'summary',
            'teachers',
            'classes'
        ));
    }

    public function attendanceExport(Request $request)
    {
        $filterType = $request->get('filter_type', 'day');
        $date = $request->get('date', date('Y-m-d'));
        $month = $request->get('month', date('m'));
        $year = $request->get('year', date('Y'));
        $status = $request->get('status');
        $userId = $request->get('user_id');
        $classId = $request->get('class_id');
        $format = $request->get('format', 'xlsx');

        $query = Attendance::with(['user', 'classRoom']);

        // Apply date filter based on filter type
        if ($filterType === 'day') {
            $query->where('date', $date);
        } elseif ($filterType === 'month') {
            $query->whereMonth('date', $month)->whereYear('date', $year);
        } elseif ($filterType === 'year') {
            $query->whereYear('date', $year);
        }

        // Apply other filters
        if ($status) {
            $query->where('status', $status);
        }
        if ($userId) {
            $query->where('user_id', $userId);
        }
        if ($classId) {
            $query->where('class_id', $classId);
        }

        $attendances = $query->latest('date')->latest('scan_time')->get();

        $filename = 'laporan_absensi_' . date('Y-m-d_His');

        if ($format === 'pdf') {
            return $this->exportPdf($attendances, $filename);
        }

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\AttendanceExport($attendances),
            $filename . '.xlsx'
        );
    }

    private function exportPdf($attendances, $filename)
    {
        $html = view('admin.attendance.pdf', compact('attendances'))->render();

        // Using simple HTML to PDF approach
        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '.html"');
    }

    public function updateAttendanceStatus(Request $request, Attendance $attendance)
    {
        $request->validate([
            'status' => 'required|in:hadir,telat,izin,sakit,alpa,dinas',
        ]);

        $attendance->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Status kehadiran berhasil diubah!');
    }

    public function attendanceDestroy(Attendance $attendance)
    {
        $attendance->delete();
        return redirect()->back()->with('success', 'Data absensi berhasil dihapus!');
    }

    // ================================
    // SCHEDULES MANAGEMENT
    // ================================

    public function schedules(Request $request)
    {
        $query = Schedule::with(['user', 'classRoom']);

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $schedules = $query->orderByRaw("FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')")
            ->orderBy('start_time')
            ->get();

        $teachers = User::where('role', 'guru')->get();
        $classes = ClassRoom::all();
        $subjects = Subject::orderBy('name')->get();

        return view('admin.schedules.index', compact('schedules', 'teachers', 'classes', 'subjects'));
    }

    public function scheduleCreate()
    {
        $teachers = User::where('role', 'guru')->get();
        $classes = ClassRoom::all();
        return view('admin.schedules.create', compact('teachers', 'classes'));
    }

    public function scheduleStore(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'class_id' => 'required|exists:classes,id',
            'day' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'subject' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'lesson_hours' => 'required|integer|min:1|max:8',
        ]);

        $startTime = Carbon::createFromFormat('H:i', $request->start_time);
        $endTime = $startTime->copy()->addMinutes($request->lesson_hours * 45);

        Schedule::create([
            'user_id' => $request->user_id,
            'class_id' => $request->class_id,
            'day' => $request->day,
            'subject' => $request->subject,
            'start_time' => $request->start_time,
            'end_time' => $endTime->format('H:i'),
            'lesson_hours' => $request->lesson_hours,
        ]);

        return redirect()->route('admin.schedules.index')->with('success', 'Jadwal berhasil ditambahkan!');
    }

    public function scheduleDestroy(Schedule $schedule)
    {
        $schedule->delete();
        return redirect()->route('admin.schedules.index')->with('success', 'Jadwal berhasil dihapus!');
    }

    public function getTeacherSubjects(User $user)
    {
        $subjects = $user->subjects()->orderBy('name')->get(['subjects.id', 'subjects.name']);
        return response()->json($subjects);
    }

    // ================================
    // SUBJECTS MANAGEMENT
    // ================================

    public function subjects()
    {
        $subjects = Subject::withCount('users')->orderBy('name')->get();
        return view('admin.subjects.index', compact('subjects'));
    }

    public function subjectStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:subjects,name',
            'code' => 'nullable|string|max:20',
        ]);

        Subject::create([
            'name' => $request->name,
            'code' => $request->code,
        ]);

        return redirect()->route('admin.subjects.index')->with('success', 'Mata pelajaran berhasil ditambahkan!');
    }

    public function subjectUpdate(Request $request, Subject $subject)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:subjects,name,' . $subject->id,
            'code' => 'nullable|string|max:20',
        ]);

        $subject->update([
            'name' => $request->name,
            'code' => $request->code,
        ]);

        return redirect()->route('admin.subjects.index')->with('success', 'Mata pelajaran berhasil diperbarui!');
    }

    public function subjectDestroy(Subject $subject)
    {
        if ($subject->users()->count() > 0) {
            return redirect()->route('admin.subjects.index')->with('error', 'Tidak bisa menghapus mapel yang masih digunakan guru!');
        }

        $subject->delete();
        return redirect()->route('admin.subjects.index')->with('success', 'Mata pelajaran berhasil dihapus!');
    }

    public function subjectImport(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\SubjectsImport, $request->file('file'));
            return redirect()->route('admin.subjects.index')->with('success', 'Data mapel berhasil diimport!');
        } catch (\Exception $e) {
            return redirect()->route('admin.subjects.index')->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    public function subjectTemplate()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\SubjectsTemplateExport,
            'template_import_mapel.xlsx'
        );
    }

    // ================================
    // MANUAL ATTENDANCE
    // ================================

    public function manualAttendance(Request $request)
    {
        $date = $request->get('date', date('Y-m-d'));

        // Get teachers with their manual attendance for today
        $teachers = User::where('role', 'guru')->get();

        // Get today's manual attendances (izin, sakit, dinas)
        $manualAttendances = Attendance::with('user')
            ->where('date', $date)
            ->whereIn('status', ['izin', 'sakit', 'dinas'])
            ->whereNull('class_id') // Manual attendance has no class
            ->get()
            ->keyBy('user_id');

        return view('admin.manual-attendance.index', compact('teachers', 'manualAttendances', 'date'));
    }

    public function manualAttendanceStore(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'status' => 'required|in:izin,sakit,dinas',
            'date' => 'required|date',
            'notes' => 'nullable|string|max:255',
        ]);

        $today = $request->date;

        // Check if already has manual attendance
        $existing = Attendance::where('user_id', $request->user_id)
            ->where('date', $today)
            ->whereIn('status', ['izin', 'sakit', 'dinas'])
            ->whereNull('class_id')
            ->first();

        if ($existing) {
            // Update existing
            $existing->update([
                'status' => $request->status,
                'subject' => $request->notes ?? ucfirst($request->status),
            ]);
        } else {
            // Create new manual attendance
            Attendance::create([
                'user_id' => $request->user_id,
                'class_id' => null,
                'status' => $request->status,
                'date' => $today,
                'subject' => $request->notes ?? ucfirst($request->status),
                'scan_time' => now(),
            ]);
        }

        $statusLabel = ['izin' => 'Izin', 'sakit' => 'Sakit', 'dinas' => 'Dinas Luar'];
        return redirect()->route('admin.manual-attendance.index', ['date' => $today])
            ->with('success', 'Status guru berhasil diubah menjadi ' . $statusLabel[$request->status]);
    }

    public function manualAttendanceDestroy(Attendance $attendance)
    {
        $date = $attendance->date;
        $attendance->delete();
        return redirect()->route('admin.manual-attendance.index', ['date' => $date])
            ->with('success', 'Status manual berhasil dihapus!');
    }

    // ================================
    // PASSWORD MANAGEMENT
    // ================================

    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'password' => ['required', 'min:6'],
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password user berhasil direset!');
    }

    // ================================
    // GEOFENCE VIOLATIONS
    // ================================

    public function markGeofenceRead()
    {
        GeofenceViolation::where('is_read', false)->update(['is_read' => true]);
        return back()->with('success', 'Semua peringatan geofence telah ditandai sudah dibaca.');
    }
}

