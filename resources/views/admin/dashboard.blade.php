@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard Overview')

@section('content')
    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 opacity-75">Total Guru</h6>
                            <h2 class="mb-0 fw-bold">{{ $totalTeachers }}</h2>
                        </div>
                        <i class="bi bi-people-fill fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 opacity-75">Hadir Hari Ini</h6>
                            <h2 class="mb-0 fw-bold">{{ $attendanceToday }}</h2>
                        </div>
                        <i class="bi bi-check-circle-fill fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 opacity-75">Telat Hari Ini</h6>
                            <h2 class="mb-0 fw-bold">{{ $lateToday }}</h2>
                        </div>
                        <i class="bi bi-exclamation-circle-fill fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 opacity-75">Total Kelas</h6>
                            <h2 class="mb-0 fw-bold">{{ $totalClasses }}</h2>
                        </div>
                        <i class="bi bi-building fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold">Aktivitas Terbaru</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Guru</th>
                            <th>Kelas</th>
                            <th>Status</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentActivities as $activity)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-light text-secondary d-flex align-items-center justify-content-center rounded-circle me-3"
                                            style="width: 35px; height: 35px; font-size: 0.8rem;">
                                            {{ substr($activity->user->full_name, 0, 2) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $activity->user->full_name }}</div>
                                            <div class="small text-muted">{{ $activity->subject }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $activity->classRoom->class_name ?? '-' }}</td>
                                <td>
                                    <span
                                        class="badge rounded-pill bg-{{ $activity->status == 'hadir' ? 'success' : ($activity->status == 'telat' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($activity->status) }}
                                    </span>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($activity->scan_time)->format('H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">Belum ada aktivitas hari ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection