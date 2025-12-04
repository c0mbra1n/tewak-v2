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

        $data = $teachers->map(function ($teacher) use ($today, $dayName, $now) {
            // Get today's schedule for this teacher
            $schedules = Schedule::with('classRoom')
                ->where('user_id', $teacher->id)
                ->where('day', $dayName)
                ->orderBy('start_time')
                ->get();

            // Get today's attendance for this teacher
            $attendances = Attendance::where('user_id', $teacher->id)
                ->where('date', $today)
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
                'photo' => null,
                'status' => $status,
                'location' => $location,
                'time' => $time,
                'subject' => $subject,
                'schedule' => $scheduleInfo,
            ];
        });

        return response()->json($data);
    }
}
