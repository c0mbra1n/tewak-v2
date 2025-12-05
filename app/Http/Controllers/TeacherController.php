<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\Schedule;
use App\Models\Subject;
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
        $user = Auth::user()->load('subjects');
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

    public function scheduleUpdate(Request $request, Schedule $schedule)
    {
        $user = Auth::user();

        if ($schedule->user_id !== $user->id) {
            return redirect()->route('teacher.schedules')->with('error', 'Anda tidak memiliki akses!');
        }

        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'day' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'subject' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'lesson_hours' => 'required|integer|min:1|max:8',
        ]);

        $startTime = Carbon::createFromFormat('H:i', $request->start_time);
        $endTime = $startTime->copy()->addMinutes($request->lesson_hours * 45);

        $schedule->update([
            'class_id' => $request->class_id,
            'day' => $request->day,
            'subject' => $request->subject,
            'start_time' => $request->start_time,
            'end_time' => $endTime->format('H:i'),
            'lesson_hours' => $request->lesson_hours,
        ]);

        return redirect()->route('teacher.schedules')->with('success', 'Jadwal berhasil diperbarui!');
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

    public function uploadProfilePhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|string',
        ]);

        $user = Auth::user();
        $imageData = $request->photo;

        // Remove data:image/xxx;base64, prefix
        if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
            $imageData = substr($imageData, strpos($imageData, ',') + 1);
            $type = strtolower($type[1]);

            if (!in_array($type, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                return response()->json(['status' => 'error', 'message' => 'Format gambar tidak valid'], 400);
            }

            $imageData = base64_decode($imageData);
            if ($imageData === false) {
                return response()->json(['status' => 'error', 'message' => 'Gagal decode gambar'], 400);
            }
        } else {
            return response()->json(['status' => 'error', 'message' => 'Format tidak valid'], 400);
        }

        // Generate filename
        $filename = 'user_' . $user->id . '_' . time() . '.' . $type;
        $path = 'profiles/' . $filename;

        // Save to storage/app/public/profiles
        \Illuminate\Support\Facades\Storage::disk('public')->put($path, $imageData);

        // Delete old photo if exists
        if ($user->photo) {
            // Check storage first
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists('profiles/' . $user->photo)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete('profiles/' . $user->photo);
            }
            // Check legacy path
            if (file_exists(public_path('uploads/profiles/' . $user->photo))) {
                @unlink(public_path('uploads/profiles/' . $user->photo));
            }
        }

        // Update user
        $user->update(['photo' => $filename]);

        return response()->json([
            'status' => 'success',
            'message' => 'Foto profil berhasil diupload!',
            'photo_url' => asset('storage/profiles/' . $filename)
        ]);
    }
}
