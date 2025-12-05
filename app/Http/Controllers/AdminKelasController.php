<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminKelasController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();

        if (!$user->class_id) {
            return view('admin_kelas.no_class');
        }

        $class = $user->assignedClass;

        // Generate today's QR code data
        $today = date('Y-m-d');
        $qrData = $class->qr_code . '|' . $today;

        return view('admin_kelas.dashboard', compact('user', 'class', 'qrData', 'today'));
    }
}
