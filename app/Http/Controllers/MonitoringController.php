<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MonitoringController extends Controller
{
    public function index()
    {
        return view('monitor.index');
    }

    public function getData()
    {
        $today = date('Y-m-d');
        $now = Carbon::now();
        $dayName = $now->locale('en')->dayName;

        // Get all teachers
        $teachers = User::where('role', 'guru')->get();

        // Get manual attendances for today (izin, sakit, dinas)
        $manualAttendances = Attendance::where('date', $today)
            ->whereIn('status', ['izin', 'sakit', 'dinas'])
            ->whereNull('class_id')
            ->get()
            ->keyBy('user_id');

        $data = $teachers->map(function ($teacher) use ($today, $dayName, $now, $manualAttendances) {
            // Check for manual attendance first (izin, sakit, dinas)
            $manualAtt = $manualAttendances->get($teacher->id);
            if ($manualAtt) {
                return [
                    'id' => $teacher->id,
                    'name' => $teacher->full_name,
                    'photo' => $teacher->photo ? (file_exists(public_path('storage/profiles/' . $teacher->photo)) ? asset('storage/profiles/' . $teacher->photo) : asset('uploads/profiles/' . $teacher->photo)) : null,
                    'status' => $manualAtt->status,
                    'location' => '-',
                    'time' => Carbon::parse($manualAtt->scan_time)->format('H:i:s'),
                    'subject' => $manualAtt->subject ?? ucfirst($manualAtt->status),
                    'schedule' => null,
                ];
            }

            // Get today's schedule for this teacher
            $schedules = Schedule::with('classRoom')
                ->where('user_id', $teacher->id)
                ->where('day', $dayName)
                ->orderBy('start_time')
                ->get();

            // Get today's attendance for this teacher
            $attendances = Attendance::where('user_id', $teacher->id)
                ->where('date', $today)
                ->whereNotNull('class_id') // Exclude manual attendance
                ->with('classRoom')
                ->get();

            // Determine current status based on schedule
            $currentSchedule = null;
            $status = 'tidak_ada_jadwal';
            $location = '-';
            $time = '-';
            $subject = '-';
            $scheduleInfo = null;

            foreach ($schedules as $schedule) {
                $startTime = Carbon::parse($schedule->start_time);
                $endTime = Carbon::parse($schedule->end_time);
                $lateThreshold = $startTime->copy()->addMinutes(15);

                // Check if current time is within this schedule window (with buffer)
                if ($now->gte($startTime->copy()->subMinutes(15)) && $now->lte($endTime)) {
                    $currentSchedule = $schedule;
                    $scheduleInfo = [
                        'class' => $schedule->classRoom->class_name ?? '-',
                        'subject' => $schedule->subject,
                        'start' => $startTime->format('H:i'),
                        'end' => $endTime->format('H:i'),
                    ];

                    // Check if attendance exists for this schedule
                    $attendance = $attendances->where('class_id', $schedule->class_id)
                        ->where('subject', $schedule->subject)
                        ->first();

                    if ($attendance) {
                        $status = $attendance->status;
                        $location = $attendance->classRoom->class_name ?? '-';
                        $time = Carbon::parse($attendance->scan_time)->format('H:i:s');
                        $subject = $attendance->subject;
                    } else {
                        // Check if past late threshold
                        if ($now->gte($endTime)) {
                            $status = 'tidak_hadir';
                        } elseif ($now->gte($lateThreshold)) {
                            $status = 'belum_hadir_telat'; // Past late threshold but schedule not ended
                        } else {
                            $status = 'belum_hadir';
                        }
                        $location = $schedule->classRoom->class_name ?? '-';
                        $subject = $schedule->subject;
                    }
                    break;
                }
            }

            // If no current schedule, check for most recent attendance today
            if (!$currentSchedule && $attendances->count() > 0) {
                $lastAttendance = $attendances->sortByDesc('scan_time')->first();
                $status = $lastAttendance->status;
                $location = $lastAttendance->classRoom->class_name ?? '-';
                $time = Carbon::parse($lastAttendance->scan_time)->format('H:i:s');
                $subject = $lastAttendance->subject;
            }

            return [
                'id' => $teacher->id,
                'name' => $teacher->full_name,
                'photo' => $teacher->photo ? (file_exists(public_path('storage/profiles/' . $teacher->photo)) ? asset('storage/profiles/' . $teacher->photo) : asset('uploads/profiles/' . $teacher->photo)) : null,
                'status' => $status,
                'location' => $location,
                'time' => $time,
                'subject' => $subject,
                'schedule' => $scheduleInfo,
            ];
        });

        return response()->json($data);
    }

    public function getBlockData(Request $request)
    {
        $block = $request->input('block');
        if (!$block) {
            return response()->json([]);
        }

        $today = date('Y-m-d');
        $now = Carbon::now();
        $dayName = $now->locale('en')->dayName;

        // Get classes in this block
        $classes = \App\Models\ClassRoom::where('block', $block)->get();

        $data = $classes->map(function ($class) use ($today, $dayName, $now) {
            // Get current schedule for this class
            $schedule = Schedule::with('user')
                ->where('class_id', $class->id)
                ->where('day', $dayName)
                ->where('start_time', '<=', $now->format('H:i:s'))
                ->where('end_time', '>=', $now->format('H:i:s'))
                ->first();

            // If no current schedule, find next schedule
            if (!$schedule) {
                $schedule = Schedule::with('user')
                    ->where('class_id', $class->id)
                    ->where('day', $dayName)
                    ->where('start_time', '>', $now->format('H:i:s'))
                    ->orderBy('start_time')
                    ->first();

                $status = 'tidak_ada_jadwal';
                $teacherName = '-';
                $subject = '-';
                $time = '-';
                $teacherPhoto = null;

                if ($schedule) {
                    $status = 'menunggu_jadwal';
                    $subject = $schedule->subject . ' <br><small class="text-muted">(' . Carbon::parse($schedule->start_time)->format('H:i') . ' - ' . Carbon::parse($schedule->end_time)->format('H:i') . ')</small>';
                }
            } else {
                // Found active schedule
                $teacher = $schedule->user;
                $teacherName = $teacher->full_name;
                $subject = $schedule->subject . ' <br><small class="text-muted">(' . Carbon::parse($schedule->start_time)->format('H:i') . ' - ' . Carbon::parse($schedule->end_time)->format('H:i') . ')</small>';
                $teacherPhoto = $teacher->photo ? (file_exists(public_path('storage/profiles/' . $teacher->photo)) ? asset('storage/profiles/' . $teacher->photo) : asset('uploads/profiles/' . $teacher->photo)) : null;

                // Check attendance
                $attendance = Attendance::where('user_id', $teacher->id)
                    ->where('class_id', $class->id)
                    ->where('subject', $schedule->subject)
                    ->where('date', $today)
                    ->first();

                if ($attendance) {
                    $status = $attendance->status;
                    $time = Carbon::parse($attendance->scan_time)->format('H:i:s');
                } else {
                    $endTime = Carbon::parse($schedule->end_time);
                    $lateThreshold = Carbon::parse($schedule->start_time)->addMinutes(15);

                    if ($now->gte($endTime)) {
                        $status = 'tidak_hadir';
                    } elseif ($now->gte($lateThreshold)) {
                        $status = 'belum_hadir_telat';
                    } else {
                        $status = 'belum_hadir';
                    }
                    $time = '-';
                }
            }

            return [
                'class_name' => $class->class_name,
                'subject' => $subject,
                'teacher_name' => $teacherName ?? '-',
                'teacher_photo' => $teacherPhoto ?? null,
                'status' => $status,
                'time' => $time ?? '-',
            ];
        });

        return response()->json($data);
    }
}
