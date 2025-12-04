@extends('layouts.admin')

@section('title', 'Laporan Absensi')
@section('page-title', 'Laporan Absensi')

@section('content')
<!-- Filter -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('admin.attendance.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="date" class="form-label">Tanggal</label>
                <input type="date" class="form-control" id="date" name="date" value="{{ $date }}">
            </div>
            <div class="col-md-4">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Semua Status</option>
                    <option value="hadir" {{ $status == 'hadir' ? 'selected' : '' }}>Hadir</option>
                    <option value="telat" {{ $status == 'telat' ? 'selected' : '' }}>Telat</option>
                    <option value="izin" {{ $status == 'izin' ? 'selected' : '' }}>Izin</option>
                    <option value="sakit" {{ $status == 'sakit' ? 'selected' : '' }}>Sakit</option>
                    <option value="alpa" {{ $status == 'alpa' ? 'selected' : '' }}>Alpa</option>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-1"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col">
        <div class="card bg-success text-white text-center">
            <div class="card-body py-3">
                <h4 class="mb-0 fw-bold">{{ $summary['hadir'] }}</h4>
                <small>Hadir</small>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card bg-warning text-white text-center">
            <div class="card-body py-3">
                <h4 class="mb-0 fw-bold">{{ $summary['telat'] }}</h4>
                <small>Telat</small>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card bg-info text-white text-center">
            <div class="card-body py-3">
                <h4 class="mb-0 fw-bold">{{ $summary['izin'] }}</h4>
                <small>Izin</small>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card bg-secondary text-white text-center">
            <div class="card-body py-3">
                <h4 class="mb-0 fw-bold">{{ $summary['sakit'] }}</h4>
                <small>Sakit</small>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card bg-danger text-white text-center">
            <div class="card-body py-3">
                <h4 class="mb-0 fw-bold">{{ $summary['alpa'] }}</h4>
                <small>Alpa</small>
            </div>
        </div>
    </div>
</div>

<!-- Attendance Table -->
<div class="card shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold">Data Absensi - {{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">#</th>
                        <th>Guru</th>
                        <th>Mata Pelajaran</th>
                        <th>Kelas</th>
                        <th>Status</th>
                        <th>Waktu Scan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $index => $attendance)
                        <tr>
                            <td class="ps-4">{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle bg-primary text-white d-flex align-items-center justify-content-center rounded-circle me-3"
                                        style="width: 35px; height: 35px; font-size: 0.8rem;">
                                        {{ strtoupper(substr($attendance->user->full_name ?? 'N', 0, 2)) }}
                                    </div>
                                    <span class="fw-bold">{{ $attendance->user->full_name ?? '-' }}</span>
                                </div>
                            </td>
                            <td>{{ $attendance->subject ?? '-' }}</td>
                            <td>{{ $attendance->classRoom->class_name ?? '-' }}</td>
                            <td>
                                @php
                                    $statusColors = [
                                        'hadir' => 'success',
                                        'telat' => 'warning',
                                        'izin' => 'info',
                                        'sakit' => 'secondary',
                                        'alpa' => 'danger',
                                    ];
                                @endphp
                                <span class="badge rounded-pill bg-{{ $statusColors[$attendance->status] ?? 'secondary' }}">
                                    {{ ucfirst($attendance->status) }}
                                </span>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($attendance->scan_time)->format('H:i:s') }}</td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" 
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        Ubah Status
                                    </button>
                                    <ul class="dropdown-menu">
                                        @foreach(['hadir' => 'Hadir', 'telat' => 'Telat', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alpa' => 'Alpa'] as $key => $label)
                                            @if($attendance->status !== $key)
                                                <li>
                                                    <form action="{{ route('admin.attendance.update-status', $attendance) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="status" value="{{ $key }}">
                                                        <button type="submit" class="dropdown-item">
                                                            <span class="badge bg-{{ $statusColors[$key] }} me-1">&nbsp;</span> {{ $label }}
                                                        </button>
                                                    </form>
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Tidak ada data absensi untuk tanggal ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection