<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\Schedule;
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

        return view('admin.dashboard', compact('totalTeachers', 'totalClasses', 'attendanceToday', 'lateToday', 'recentActivities'));
    }

    // ================================
    // TEACHERS CRUD (with role selection)
    // ================================

    public function teachers()
    {
        $teachers = User::whereIn('role', ['guru', 'admin_kelas'])->latest()->get();
        return view('admin.teachers.index', compact('teachers'));
    }

    public function teacherCreate()
    {
        return view('admin.teachers.create');
    }

    public function teacherStore(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:users,username',
            'full_name' => 'required|string|max:255',
            'role' => 'required|in:guru,admin_kelas',
            'subject' => 'nullable|string|max:255',
            'password' => 'required|min:6',
        ]);

        User::create([
            'username' => $request->username,
            'full_name' => $request->full_name,
            'subject' => $request->subject,
            'role' => $request->role,
            'password' => Hash::make($request->password),
        ]);

        $roleLabel = $request->role === 'guru' ? 'Guru' : 'Admin Kelas';
        return redirect()->route('admin.teachers.index')->with('success', "$roleLabel berhasil ditambahkan!");
    }

    public function teacherEdit(User $user)
    {
        return view('admin.teachers.edit', compact('user'));
    }

    public function teacherUpdate(Request $request, User $user)
    {
        $request->validate([
            'username' => 'required|unique:users,username,' . $user->id,
            'full_name' => 'required|string|max:255',
            'role' => 'required|in:guru,admin_kelas',
            'subject' => 'nullable|string|max:255',
            'password' => 'nullable|min:6',
        ]);

        $data = [
            'username' => $request->username,
            'full_name' => $request->full_name,
            'subject' => $request->subject,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

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
        ]);

        $qrCode = 'CLASS_' . strtoupper(str_replace(' ', '_', $request->class_name)) . '_' . time();

        ClassRoom::create([
            'class_name' => $request->class_name,
            'qr_code' => $qrCode,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'radius' => $request->radius,
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
        ]);

        $classRoom->update([
            'class_name' => $request->class_name,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'radius' => $request->radius,
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
        $date = $request->get('date', date('Y-m-d'));
        $status = $request->get('status');

        $query = Attendance::with(['user', 'classRoom'])->where('date', $date);

        if ($status) {
            $query->where('status', $status);
        }

        $attendances = $query->latest('scan_time')->get();

        // Summary stats
        $summary = [
            'hadir' => Attendance::where('date', $date)->where('status', 'hadir')->count(),
            'telat' => Attendance::where('date', $date)->where('status', 'telat')->count(),
            'izin' => Attendance::where('date', $date)->where('status', 'izin')->count(),
            'sakit' => Attendance::where('date', $date)->where('status', 'sakit')->count(),
            'alpa' => Attendance::where('date', $date)->where('status', 'alpa')->count(),
        ];

        return view('admin.attendance.index', compact('attendances', 'date', 'status', 'summary'));
    }

    public function updateAttendanceStatus(Request $request, Attendance $attendance)
    {
        $request->validate([
            'status' => 'required|in:hadir,telat,izin,sakit,alpa',
        ]);

        $attendance->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Status kehadiran berhasil diubah!');
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

        return view('admin.schedules.index', compact('schedules', 'teachers', 'classes'));
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
}
