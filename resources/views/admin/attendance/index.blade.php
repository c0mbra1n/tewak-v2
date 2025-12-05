@extends('layouts.admin')

@section('title', 'Laporan Absensi')
@section('page-title', 'Laporan Absensi')

@section('content')
<!-- Filter -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold"><i class="bi bi-funnel me-2"></i>Filter Data</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.attendance.index') }}" method="GET" id="filterForm">
            <div class="row g-2 g-lg-3 mb-3">
                <!-- Filter Type -->
                <div class="col-12 col-lg-3">
                    <label class="form-label small">Tipe Filter</label>
                    <select class="form-select" name="filter_type" id="filterType">
                        <option value="day" {{ $filterType == 'day' ? 'selected' : '' }}>Per Hari</option>
                        <option value="month" {{ $filterType == 'month' ? 'selected' : '' }}>Per Bulan</option>
                        <option value="year" {{ $filterType == 'year' ? 'selected' : '' }}>Per Tahun</option>
                    </select>
                </div>

                <!-- Date Filter (for day) -->
                <div class="col-6 col-lg-3 filter-day {{ $filterType != 'day' ? 'd-none' : '' }}">
                    <label class="form-label small">Tanggal</label>
                    <input type="date" class="form-control" name="date" value="{{ $date }}">
                </div>

                <!-- Month Filter -->
                <div class="col-6 col-lg-2 filter-month {{ $filterType == 'day' ? 'd-none' : '' }}">
                    <label class="form-label small">Bulan</label>
                    <select class="form-select" name="month">
                        @foreach(range(1, 12) as $m)
                            <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}" {{ $month == str_pad($m, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Year Filter -->
                <div class="col-6 col-lg-2 filter-year {{ $filterType == 'day' ? 'd-none' : '' }}">
                    <label class="form-label small">Tahun</label>
                    <select class="form-select" name="year">
                        @foreach(range(date('Y'), date('Y') - 5) as $y)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Teacher Filter -->
                <div class="col-6 col-lg-3">
                    <label class="form-label small">Guru</label>
                    <select class="form-select" name="user_id">
                        <option value="">Semua Guru</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ $userId == $teacher->id ? 'selected' : '' }}>
                                {{ $teacher->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Class Filter -->
                <div class="col-6 col-lg-3">
                    <label class="form-label small">Kelas</label>
                    <select class="form-select" name="class_id">
                        <option value="">Semua Kelas</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>
                                {{ $class->class_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="col-6 col-lg-3">
                    <label class="form-label small">Status</label>
                    <select class="form-select" name="status">
                        <option value="">Semua Status</option>
                        <option value="hadir" {{ $status == 'hadir' ? 'selected' : '' }}>Hadir</option>
                        <option value="telat" {{ $status == 'telat' ? 'selected' : '' }}>Telat</option>
                        <option value="izin" {{ $status == 'izin' ? 'selected' : '' }}>Izin</option>
                        <option value="sakit" {{ $status == 'sakit' ? 'selected' : '' }}>Sakit</option>
                        <option value="dinas" {{ $status == 'dinas' ? 'selected' : '' }}>Dinas Luar</option>
                        <option value="alpa" {{ $status == 'alpa' ? 'selected' : '' }}>Alpa</option>
                    </select>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search me-1"></i> Filter
                </button>
                <a href="{{ route('admin.attendance.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise me-1"></i> Reset
                </a>
                <div class="dropdown">
                    <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-download me-1"></i> Export
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="#" onclick="exportData('xlsx')">
                                <i class="bi bi-file-earmark-excel me-2"></i> Excel (.xlsx)
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#" onclick="exportData('pdf')">
                                <i class="bi bi-file-earmark-pdf me-2"></i> PDF
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row g-2 g-lg-3 mb-4">
    <div class="col">
        <div class="card bg-success text-white text-center">
            <div class="card-body py-2 py-lg-3 px-2">
                <h4 class="mb-0 fw-bold fs-5 fs-lg-4">{{ $summary['hadir'] }}</h4>
                <small class="d-none d-sm-inline">Hadir</small>
                <small class="d-sm-none" style="font-size: 0.65rem;">Hadir</small>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card bg-warning text-white text-center">
            <div class="card-body py-2 py-lg-3 px-2">
                <h4 class="mb-0 fw-bold fs-5 fs-lg-4">{{ $summary['telat'] }}</h4>
                <small class="d-none d-sm-inline">Telat</small>
                <small class="d-sm-none" style="font-size: 0.65rem;">Telat</small>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card bg-info text-white text-center">
            <div class="card-body py-2 py-lg-3 px-2">
                <h4 class="mb-0 fw-bold fs-5 fs-lg-4">{{ $summary['izin'] }}</h4>
                <small class="d-none d-sm-inline">Izin</small>
                <small class="d-sm-none" style="font-size: 0.65rem;">Izin</small>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card bg-secondary text-white text-center">
            <div class="card-body py-2 py-lg-3 px-2">
                <h4 class="mb-0 fw-bold fs-5 fs-lg-4">{{ $summary['sakit'] }}</h4>
                <small class="d-none d-sm-inline">Sakit</small>
                <small class="d-sm-none" style="font-size: 0.65rem;">Sakit</small>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card text-white text-center" style="background-color: #0ea5e9;">
            <div class="card-body py-2 py-lg-3 px-2">
                <h4 class="mb-0 fw-bold fs-5 fs-lg-4">{{ $summary['dinas'] }}</h4>
                <small class="d-none d-sm-inline">Dinas</small>
                <small class="d-sm-none" style="font-size: 0.65rem;">Dinas</small>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card bg-danger text-white text-center">
            <div class="card-body py-2 py-lg-3 px-2">
                <h4 class="mb-0 fw-bold fs-5 fs-lg-4">{{ $summary['alpa'] }}</h4>
                <small class="d-none d-sm-inline">Alpa</small>
                <small class="d-sm-none" style="font-size: 0.65rem;">Alpa</small>
            </div>
        </div>
    </div>
</div>

<!-- Attendance Data -->
<div class="card shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold">
            Data Absensi - 
            @if($filterType == 'day')
                {{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}
            @elseif($filterType == 'month')
                {{ \Carbon\Carbon::create()->month((int)$month)->translatedFormat('F') }} {{ $year }}
            @else
                Tahun {{ $year }}
            @endif
            <span class="badge bg-primary ms-2">{{ $attendances->count() }} Data</span>
        </h5>
    </div>
    <div class="card-body p-0">
        <!-- Mobile View -->
        <div class="d-lg-none">
            @forelse($attendances as $attendance)
                <div class="p-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle bg-primary text-white d-flex align-items-center justify-content-center rounded-circle me-2"
                                style="width: 35px; height: 35px; font-size: 0.8rem;">
                                {{ strtoupper(substr($attendance->user->full_name ?? 'N', 0, 2)) }}
                            </div>
                            <div>
                                <div class="fw-bold small">{{ $attendance->user->full_name ?? '-' }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">{{ $attendance->subject ?? '-' }}</div>
                            </div>
                        </div>
                        @php
                            $statusColors = [
                                'hadir' => 'success',
                                'telat' => 'warning',
                                'izin' => 'info',
                                'sakit' => 'secondary',
                                'dinas' => 'primary',
                                'alpa' => 'danger',
                            ];
                        @endphp
                        <span class="badge rounded-pill bg-{{ $statusColors[$attendance->status] ?? 'secondary' }}">
                            {{ ucfirst($attendance->status) }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            <i class="bi bi-calendar me-1"></i>{{ \Carbon\Carbon::parse($attendance->date)->format('d/m/Y') }}
                            <i class="bi bi-building ms-2 me-1"></i>{{ $attendance->classRoom->class_name ?? '-' }}
                            <i class="bi bi-clock ms-2 me-1"></i>{{ \Carbon\Carbon::parse($attendance->scan_time)->format('H:i') }}
                        </div>
                        <div class="d-flex gap-1">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle py-0 px-2" type="button" 
                                    data-bs-toggle="dropdown" style="font-size: 0.75rem;">
                                    Ubah
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    @foreach(['hadir' => 'Hadir', 'telat' => 'Telat', 'izin' => 'Izin', 'sakit' => 'Sakit', 'dinas' => 'Dinas', 'alpa' => 'Alpa'] as $key => $label)
                                        @if($attendance->status !== $key)
                                            <li>
                                                <form action="{{ route('admin.attendance.update-status', $attendance) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="status" value="{{ $key }}">
                                                    <button type="submit" class="dropdown-item small">
                                                        <span class="badge bg-{{ $statusColors[$key] }} me-1">&nbsp;</span> {{ $label }}
                                                    </button>
                                                </form>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                            <form action="{{ route('admin.attendance.destroy', $attendance) }}" method="POST"
                                onsubmit="return confirm('Hapus data absensi ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size: 0.75rem;">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-4 text-muted">Tidak ada data absensi untuk filter ini.</div>
            @endforelse
        </div>

        <!-- Desktop View -->
        <div class="table-responsive d-none d-lg-block">
            <table class="table table-hover mb-0 align-middle">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">#</th>
                        <th>Tanggal</th>
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
                            <td>{{ \Carbon\Carbon::parse($attendance->date)->format('d/m/Y') }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle bg-primary text-white d-flex align-items-center justify-content-center rounded-circle me-2"
                                        style="width: 30px; height: 30px; font-size: 0.7rem;">
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
                                        'dinas' => 'primary',
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
                                        Ubah
                                    </button>
                                    <ul class="dropdown-menu">
                                        @foreach(['hadir' => 'Hadir', 'telat' => 'Telat', 'izin' => 'Izin', 'sakit' => 'Sakit', 'dinas' => 'Dinas', 'alpa' => 'Alpa'] as $key => $label)
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
                                <form action="{{ route('admin.attendance.destroy', $attendance) }}" method="POST"
                                    class="d-inline" onsubmit="return confirm('Hapus data absensi ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger ms-1">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">Tidak ada data absensi untuk filter ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.getElementById('filterType').addEventListener('change', function() {
        const val = this.value;
        const dayFilters = document.querySelectorAll('.filter-day');
        const monthFilters = document.querySelectorAll('.filter-month');
        const yearFilters = document.querySelectorAll('.filter-year');
        
        if (val === 'day') {
            dayFilters.forEach(el => el.classList.remove('d-none'));
            monthFilters.forEach(el => el.classList.add('d-none'));
            yearFilters.forEach(el => el.classList.add('d-none'));
        } else {
            dayFilters.forEach(el => el.classList.add('d-none'));
            monthFilters.forEach(el => el.classList.remove('d-none'));
            yearFilters.forEach(el => el.classList.remove('d-none'));
        }
    });

    function exportData(format) {
        const form = document.getElementById('filterForm');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        params.append('format', format);
        window.location.href = "{{ route('admin.attendance.export') }}?" + params.toString();
    }
</script>
@endpush