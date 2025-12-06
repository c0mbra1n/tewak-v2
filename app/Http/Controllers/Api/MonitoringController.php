<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\LocationLog;
use App\Models\GeofenceViolation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MonitoringController extends Controller
{
    public function getActiveSchedule(Request $request)
    {
        $user = $request->user();
        $now = Carbon::now();
        $currentTime = $now->format('H:i:s');
        $currentDay = $now->locale('en')->dayName;

        // Find active schedule
        \Illuminate\Support\Facades\Log::info("Checking schedule for User ID: {$user->id}, Day: {$currentDay}, Time: {$currentTime}");

        \Illuminate\Support\Facades\Log::info("Querying schedule for User {$user->id} on {$currentDay} at {$currentTime}");

        $query = Schedule::where('user_id', $user->id)
            ->where('day', $currentDay)
            ->where('start_time', '<=', $currentTime)
            ->where('end_time', '>=', $currentTime);

        \Illuminate\Support\Facades\Log::info("SQL: " . $query->toSql());
        \Illuminate\Support\Facades\Log::info("Bindings: " . json_encode($query->getBindings()));

        $schedule = $query->with(['classRoom'])->first();

        if ($schedule) {
            \Illuminate\Support\Facades\Log::info("Schedule Found: {$schedule->id}");
        } else {
            \Illuminate\Support\Facades\Log::info("No Schedule Found");
        }

        if (!$schedule) {
            return response()->json([
                'status' => 'no_schedule',
                'message' => 'Tidak ada jadwal aktif saat ini.',
                'data' => null
            ]);
        }

        return response()->json([
            'status' => 'active_schedule',
            'message' => 'Jadwal aktif ditemukan.',
            'data' => [
                'schedule_id' => $schedule->id,
                'class_name' => $schedule->classRoom->class_name,
                'class_lat' => $schedule->classRoom->latitude,
                'class_lng' => $schedule->classRoom->longitude,
                'radius' => $schedule->classRoom->radius,
                'subject' => $schedule->subject,
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
            ]
        ]);
    }

    public function updateLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'status' => 'nullable|string'
        ]);

        $user = $request->user();

        $log = LocationLog::create([
            'user_id' => $user->id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'status' => $request->status ?? 'unknown'
        ]);

        return response()->json([
            'message' => 'Location updated successfully',
            'data' => $log
        ]);
    }

    public function getAttendanceHistory(Request $request)
    {
        $user = $request->user();
        $today = date('Y-m-d');

        $attendances = \App\Models\Attendance::with('classRoom')
            ->where('user_id', $user->id)
            ->where('date', $today)
            ->orderBy('scan_time', 'desc')
            ->get()
            ->map(function ($att) {
                return [
                    'id' => $att->id,
                    'class_name' => $att->classRoom->class_name ?? '-',
                    'subject' => $att->subject,
                    'status' => $att->status,
                    'time' => \Carbon\Carbon::parse($att->scan_time)->format('H:i'),
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $attendances
        ]);
    }

    /**
     * Report geofence violation (teacher left class area)
     */
    public function reportGeofenceViolation(Request $request)
    {
        $request->validate([
            'schedule_id' => 'nullable|integer',
            'class_name' => 'required|string',
            'teacher_lat' => 'required|numeric',
            'teacher_lng' => 'required|numeric',
            'class_lat' => 'required|numeric',
            'class_lng' => 'required|numeric',
            'distance' => 'required|numeric',
            'radius' => 'required|numeric',
        ]);

        $user = $request->user();

        // Check if similar violation was reported in last 5 minutes (avoid spam)
        $recentViolation = GeofenceViolation::where('user_id', $user->id)
            ->where('created_at', '>=', Carbon::now()->subMinutes(5))
            ->first();

        if ($recentViolation) {
            return response()->json([
                'status' => 'skipped',
                'message' => 'Violation already reported recently'
            ]);
        }

        $violation = GeofenceViolation::create([
            'user_id' => $user->id,
            'schedule_id' => $request->schedule_id,
            'class_name' => $request->class_name,
            'teacher_lat' => $request->teacher_lat,
            'teacher_lng' => $request->teacher_lng,
            'class_lat' => $request->class_lat,
            'class_lng' => $request->class_lng,
            'distance' => $request->distance,
            'radius' => $request->radius,
            'is_read' => false,
        ]);

        return response()->json([
            'status' => 'reported',
            'message' => 'Geofence violation reported successfully',
            'data' => $violation
        ]);
    }
}

