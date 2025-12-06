@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard Overview')

@section('content')
    <!-- Stats Cards -->
    <div class="row g-3 g-lg-4 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body p-3 p-lg-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 opacity-75 small">Total Guru</h6>
                            <h2 class="mb-0 fw-bold fs-3 fs-lg-2">{{ $totalTeachers }}</h2>
                        </div>
                        <i class="bi bi-people-fill fs-2 fs-lg-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body p-3 p-lg-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 opacity-75 small">Hadir Hari Ini</h6>
                            <h2 class="mb-0 fw-bold fs-3 fs-lg-2">{{ $attendanceToday }}</h2>
                        </div>
                        <i class="bi bi-check-circle-fill fs-2 fs-lg-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card bg-warning text-white h-100">
                <div class="card-body p-3 p-lg-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 opacity-75 small">Telat Hari Ini</h6>
                            <h2 class="mb-0 fw-bold fs-3 fs-lg-2">{{ $lateToday }}</h2>
                        </div>
                        <i class="bi bi-exclamation-circle-fill fs-2 fs-lg-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body p-3 p-lg-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 opacity-75 small">Total Kelas</h6>
                            <h2 class="mb-0 fw-bold fs-3 fs-lg-2">{{ $totalClasses }}</h2>
                        </div>
                        <i class="bi bi-building fs-2 fs-lg-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions (Mobile) -->
    <div class="d-lg-none mb-4">
        <div class="card">
            <div class="card-body p-3">
                <div class="d-flex gap-2 overflow-auto">
                    <a href="{{ route('admin.teachers.create') }}" class="btn btn-outline-primary btn-sm flex-shrink-0">
                        <i class="bi bi-person-plus me-1"></i> Tambah Guru
                    </a>
                    <a href="{{ route('admin.classes.create') }}" class="btn btn-outline-info btn-sm flex-shrink-0">
                        <i class="bi bi-building me-1"></i> Tambah Kelas
                    </a>
                    <a href="{{ route('monitor.index') }}" class="btn btn-outline-success btn-sm flex-shrink-0"
                        target="_blank">
                        <i class="bi bi-display me-1"></i> Monitoring
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Geofence Violations Alert -->
    @if($unreadViolationsCount > 0)
        <div class="card border-danger mb-4 shadow-sm">
            <div class="card-header bg-danger text-white py-3 d-flex justify-content-between align-items-center">
                <div>
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <span class="fw-bold">Peringatan Geofence</span>
                    <span class="badge bg-white text-danger ms-2">{{ $unreadViolationsCount }} baru</span>
                </div>
                <form action="{{ route('admin.geofence.mark-read') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-light">
                        <i class="bi bi-check-all"></i> Tandai Sudah Dibaca
                    </button>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Guru</th>
                                <th>Kelas</th>
                                <th>Jarak</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($geofenceViolations as $violation)
                                <tr class="table-danger">
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-person-exclamation text-danger me-2 fs-5"></i>
                                            <strong>{{ $violation->user->full_name ?? 'Unknown' }}</strong>
                                        </div>
                                    </td>
                                    <td>{{ $violation->class_name }}</td>
                                    <td>
                                        <span class="badge bg-danger">
                                            {{ number_format($violation->distance, 0) }}m
                                            <small>(maks {{ number_format($violation->radius, 0) }}m)</small>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-muted">
                                            {{ $violation->created_at->diffForHumans() }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Recent Activity Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold">Aktivitas Terbaru</h5>
        </div>
        <div class="card-body p-0">
            <!-- Mobile View -->
            <div class="d-lg-none">
                @forelse($recentActivities as $activity)
                    <div class="p-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle bg-light text-secondary d-flex align-items-center justify-content-center rounded-circle me-2"
                                    style="width: 35px; height: 35px; font-size: 0.8rem;">
                                    {{ substr($activity->user->full_name, 0, 2) }}
                                </div>
                                <div>
                                    <div class="fw-bold small">{{ $activity->user->full_name }}</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">{{ $activity->subject }}</div>
                                </div>
                            </div>
                            <span
                                class="badge rounded-pill bg-{{ $activity->status == 'hadir' ? 'success' : ($activity->status == 'telat' ? 'warning' : 'danger') }}">
                                {{ ucfirst($activity->status) }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between text-muted small">
                            <span><i class="bi bi-building me-1"></i>{{ $activity->classRoom->class_name ?? '-' }}</span>
                            <span><i
                                    class="bi bi-clock me-1"></i>{{ \Carbon\Carbon::parse($activity->scan_time)->format('H:i') }}</span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 text-muted">Belum ada aktivitas hari ini.</div>
                @endforelse
            </div>

            <!-- Desktop View -->
            <div class="table-responsive d-none d-lg-block">
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