@extends('layouts.admin')

@section('title', 'Absen Manual')
@section('page-title', 'Setting Absen Manual')

@section('content')
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="mb-0 fw-bold"><i class="bi bi-pencil-square me-2"></i>Absen Manual Guru</h5>
                <form class="d-flex gap-2" method="GET">
                    <input type="date" name="date" class="form-control form-control-sm" value="{{ $date }}">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
            </div>
        </div>
        <div class="card-body">
            <div class="alert alert-info mb-4">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Petunjuk:</strong> Gunakan halaman ini untuk mengatur status kehadiran guru yang izin, sakit, atau
                dinas luar.
                Status ini akan tampil di halaman monitoring.
            </div>

            <!-- Mobile View -->
            <div class="d-lg-none">
                @foreach($teachers as $teacher)
                    @php
                        $manualAtt = $manualAttendances->get($teacher->id);
                    @endphp
                    <div class="card mb-3">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar-circle bg-primary text-white d-flex align-items-center justify-content-center rounded-circle me-2"
                                    style="width: 40px; height: 40px; font-size: 0.8rem;">
                                    {{ strtoupper(substr($teacher->full_name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="fw-bold">{{ $teacher->full_name }}</div>
                                    <small class="text-muted">{{ $teacher->subject ?? '-' }}</small>
                                </div>
                            </div>

                            @if($manualAtt)
                                <div class="d-flex justify-content-between align-items-center">
                                    <span
                                        class="badge bg-{{ $manualAtt->status == 'izin' ? 'warning' : ($manualAtt->status == 'sakit' ? 'danger' : 'info') }} fs-6">
                                        {{ ucfirst($manualAtt->status) }}
                                    </span>
                                    <form action="{{ route('admin.manual-attendance.destroy', $manualAtt) }}" method="POST"
                                        onsubmit="return confirm('Hapus status ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-x-lg"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            @else
                                <form action="{{ route('admin.manual-attendance.store') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{ $teacher->id }}">
                                    <input type="hidden" name="date" value="{{ $date }}">
                                    <div class="d-flex gap-2">
                                        <select name="status" class="form-select form-select-sm" required>
                                            <option value="">Pilih Status</option>
                                            <option value="izin">Izin</option>
                                            <option value="sakit">Sakit</option>
                                            <option value="dinas">Dinas Luar</option>
                                        </select>
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="bi bi-check"></i>
                                        </button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Desktop View -->
            <div class="table-responsive d-none d-lg-block">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>#</th>
                            <th>Nama Guru</th>
                            <th>Mata Pelajaran</th>
                            <th>Status Manual</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($teachers as $index => $teacher)
                            @php
                                $manualAtt = $manualAttendances->get($teacher->id);
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-primary text-white d-flex align-items-center justify-content-center rounded-circle me-2"
                                            style="width: 35px; height: 35px; font-size: 0.8rem;">
                                            {{ strtoupper(substr($teacher->full_name, 0, 2)) }}
                                        </div>
                                        <span class="fw-bold">{{ $teacher->full_name }}</span>
                                    </div>
                                </td>
                                <td>{{ $teacher->subject ?? '-' }}</td>
                                <td>
                                    @if($manualAtt)
                                        <span
                                            class="badge bg-{{ $manualAtt->status == 'izin' ? 'warning' : ($manualAtt->status == 'sakit' ? 'danger' : 'info') }}">
                                            {{ ucfirst($manualAtt->status) }}
                                        </span>
                                        @if($manualAtt->subject && $manualAtt->subject != ucfirst($manualAtt->status))
                                            <small class="text-muted d-block">{{ $manualAtt->subject }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($manualAtt)
                                        <form action="{{ route('admin.manual-attendance.destroy', $manualAtt) }}" method="POST"
                                            class="d-inline" onsubmit="return confirm('Hapus status manual ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-x-lg me-1"></i> Hapus
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.manual-attendance.store') }}" method="POST"
                                            class="d-flex gap-2 align-items-center">
                                            @csrf
                                            <input type="hidden" name="user_id" value="{{ $teacher->id }}">
                                            <input type="hidden" name="date" value="{{ $date }}">
                                            <select name="status" class="form-select form-select-sm" style="width: 120px;" required>
                                                <option value="">Pilih</option>
                                                <option value="izin">Izin</option>
                                                <option value="sakit">Sakit</option>
                                                <option value="dinas">Dinas Luar</option>
                                            </select>
                                            <input type="text" name="notes" class="form-control form-control-sm"
                                                placeholder="Keterangan (opsional)" style="width: 150px;">
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                <i class="bi bi-check-lg"></i> Set
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Tidak ada data guru.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Summary -->
    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center py-3">
                    <h3 class="fw-bold mb-0">{{ $manualAttendances->where('status', 'izin')->count() }}</h3>
                    <small>Izin</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card bg-danger text-white">
                <div class="card-body text-center py-3">
                    <h3 class="fw-bold mb-0">{{ $manualAttendances->where('status', 'sakit')->count() }}</h3>
                    <small>Sakit</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center py-3">
                    <h3 class="fw-bold mb-0">{{ $manualAttendances->where('status', 'dinas')->count() }}</h3>
                    <small>Dinas Luar</small>
                </div>
            </div>
        </div>
    </div>
@endsection