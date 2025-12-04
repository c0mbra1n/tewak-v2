<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'subject' => 'nullable|string',
        ]);

        $user = Auth::user();
        if ($user->role !== 'guru') {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        // Parse QR Code
        $parts = explode('|', $request->qr_code);
        $static_code = trim($parts[0]);
        $qr_date = trim($parts[1] ?? '');

        if ($qr_date !== date('Y-m-d')) {
            return response()->json(['status' => 'error', 'message' => 'QR Code kadaluarsa atau tidak valid. Pastikan scan QR Code hari ini.'], 400);
        }

        // Find Class
        $class = ClassRoom::where('qr_code', $static_code)->first();
        if (!$class) {
            return response()->json(['status' => 'error', 'message' => "Kelas tidak ditemukan."], 404);
        }

        // Location Validation
        if ($class->latitude && $class->longitude) {
            if (!$request->latitude || !$request->longitude) {
                return response()->json(['status' => 'error', 'message' => 'Lokasi tidak terdeteksi. Pastikan GPS aktif.'], 400);
            }

            $distance = $this->calculateDistance(
                $request->latitude,
                $request->longitude,
                $class->latitude,
                $class->longitude
            );

            if ($distance > $class->radius) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Anda berada di luar radius kelas. Jarak: " . round($distance) . "m (Maks: {$class->radius} m)."
                ], 400);
            }
        }

        // Check Existing Attendance
        $today = date('Y-m-d');
        $subject = $request->subject;

        // Fallback subject
        if (!$subject) {
            $user_subjects = explode(',', $user->subject);
            $subject = trim($user_subjects[0] ?? 'Umum');
        }

        $existing = Attendance::where('user_id', $user->id)
            ->where('class_id', $class->id)
            ->where('date', $today)
            ->where('subject', $subject)
            ->first();

        if ($existing) {
            return response()->json(['status' => 'error', 'message' => "Anda sudah melakukan absensi untuk mata pelajaran '$subject' di kelas ini hari ini"], 400);
        }

        // Determine Status (Late or Present)
        $dayName = Carbon::now()->format('l');
        $schedule = Schedule::where('user_id', $user->id)
            ->where('class_id', $class->id)
            ->where('day', $dayName)
            ->first();

        $status = 'hadir';
        if ($schedule) {
            $lateThreshold = Carbon::parse($schedule->start_time)->addMinutes(15);
            if (Carbon::now()->gt($lateThreshold)) {
                $status = 'telat';
            }
        }

        // Record Attendance
        Attendance::create([
            'user_id' => $user->id,
            'class_id' => $class->id,
            'status' => $status,
            'date' => $today,
            'subject' => $subject,
            'scan_time' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil absen di kelas ' . $class->class_name,
            'data' => [
                'status' => $status,
                'subject' => $subject,
                'class_lat' => $class->latitude,
                'class_lng' => $class->longitude,
                'radius' => $class->radius
            ]
        ]);
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
