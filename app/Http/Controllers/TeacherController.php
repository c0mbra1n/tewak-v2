<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TeacherController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $today = date('Y-m-d');
        $dayName = Carbon::now()->locale('en')->dayName;

        // Today's schedules
        $schedules = Schedule::with('classRoom')
            ->where('user_id', $user->id)
            ->where('day', $dayName)
            ->orderBy('start_time')
            ->get();

        // Today's attendance
        $attendances = Attendance::with('classRoom')
            ->where('user_id', $user->id)
            ->where('date', $today)
            ->get();

        // All classes for manual selection
        $classes = ClassRoom::all();

        return view('teacher.dashboard', compact('user', 'schedules', 'attendances', 'classes'));
    }

    // ================================
    // SCHEDULE MANAGEMENT
    // ================================

    public function schedules()
    {
        $user = Auth::user();
        $schedules = Schedule::with('classRoom')
            ->where('user_id', $user->id)
            ->orderByRaw("FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')")
            ->orderBy('start_time')
            ->get();

        $classes = ClassRoom::all();

        return view('teacher.schedules', compact('user', 'schedules', 'classes'));
    }

    public function scheduleStore(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'day' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'subject' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'lesson_hours' => 'required|integer|min:1|max:8',
        ]);

        $user = Auth::user();

        // Calculate end_time based on lesson_hours (45 min each)
        $startTime = Carbon::createFromFormat('H:i', $request->start_time);
        $endTime = $startTime->copy()->addMinutes($request->lesson_hours * 45);

        Schedule::create([
            'user_id' => $user->id,
            'class_id' => $request->class_id,
            'day' => $request->day,
            'subject' => $request->subject,
            'start_time' => $request->start_time,
            'end_time' => $endTime->format('H:i'),
            'lesson_hours' => $request->lesson_hours,
        ]);

        return redirect()->route('teacher.schedules')->with('success', 'Jadwal berhasil ditambahkan!');
    }

    public function scheduleDestroy(Schedule $schedule)
    {
        $user = Auth::user();

        if ($schedule->user_id !== $user->id) {
            return redirect()->route('teacher.schedules')->with('error', 'Anda tidak memiliki akses!');
        }

        $schedule->delete();
        return redirect()->route('teacher.schedules')->with('success', 'Jadwal berhasil dihapus!');
    }
}
