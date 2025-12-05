@extends('layouts.admin')

@section('title', 'Data Pengguna')
@section('page-title', 'Manajemen Data Pengguna')

@section('content')
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="mb-0 fw-bold">Daftar Guru & Admin Kelas</h5>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                        data-bs-target="#importModal">
                        <i class="bi bi-upload me-1"></i> Import
                    </button>
                    <a href="{{ route('admin.teachers.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg me-1"></i> Tambah User
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <!-- Mobile View -->
            <div class="d-lg-none">
                @forelse($teachers as $teacher)
                    <div class="p-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle bg-{{ $teacher->role === 'guru' ? 'primary' : 'success' }} text-white d-flex align-items-center justify-content-center rounded-circle me-2"
                                    style="width: 40px; height: 40px; font-size: 0.8rem;">
                                    {{ strtoupper(substr($teacher->full_name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="fw-bold">{{ $teacher->full_name }}</div>
                                    <small class="text-muted">{{ $teacher->username }}</small>
                                </div>
                            </div>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.teachers.edit', $teacher) }}"
                                    class="btn btn-sm btn-outline-primary py-0 px-2">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.teachers.destroy', $teacher) }}" method="POST"
                                    onsubmit="return confirm('Yakin?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-1">
                            @if($teacher->role === 'guru')
                                <span class="badge bg-primary">Guru</span>
                                @forelse($teacher->subjects as $subject)
                                    <span class="badge bg-light text-dark">{{ $subject->name }}</span>
                                @empty
                                    <span class="badge bg-secondary">Belum ada mapel</span>
                                @endforelse
                            @else
                                <span class="badge bg-success">Admin Kelas</span>
                                @if($teacher->assignedClass)
                                    <span class="badge bg-info">{{ $teacher->assignedClass->class_name }}</span>
                                @endif
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 text-muted">Belum ada data user.</div>
                @endforelse
            </div>

            <!-- Desktop View -->
            <div class="table-responsive d-none d-lg-block">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Nama Lengkap</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Mapel / Kelas</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($teachers as $index => $teacher)
                            <tr>
                                <td class="ps-4">{{ $index + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-{{ $teacher->role === 'guru' ? 'primary' : 'success' }} text-white d-flex align-items-center justify-content-center rounded-circle me-3"
                                            style="width: 40px; height: 40px; font-size: 0.9rem;">
                                            {{ strtoupper(substr($teacher->full_name, 0, 2)) }}
                                        </div>
                                        <span class="fw-bold">{{ $teacher->full_name }}</span>
                                    </div>
                                </td>
                                <td>{{ $teacher->username }}</td>
                                <td>
                                    @if($teacher->role === 'guru')
                                        <span class="badge bg-primary">Guru</span>
                                    @else
                                        <span class="badge bg-success">Admin Kelas</span>
                                    @endif
                                </td>
                                <td>
                                    @if($teacher->role === 'guru')
                                        @forelse($teacher->subjects as $subject)
                                            <span class="badge bg-secondary me-1">{{ $subject->name }}</span>
                                        @empty
                                            <span class="text-muted">Belum ada mapel</span>
                                        @endforelse
                                    @else
                                        @if($teacher->assignedClass)
                                            <span class="badge bg-info">{{ $teacher->assignedClass->class_name }}</span>
                                        @else
                                            <span class="text-muted">Belum ada kelas</span>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.teachers.edit', $teacher) }}"
                                        class="btn btn-sm btn-outline-primary me-1">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.teachers.destroy', $teacher) }}" method="POST"
                                        class="d-inline" onsubmit="return confirm('Yakin ingin menghapus user ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Belum ada data user.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-upload me-2"></i>Import Data User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.teachers.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Petunjuk:</strong>
                            <ol class="mb-0 mt-2 small">
                                <li>Download template terlebih dahulu</li>
                                <li>Isi data sesuai format template</li>
                                <li>Upload file Excel atau CSV</li>
                                <li>Username yang sudah ada akan dilewati</li>
                            </ol>
                        </div>

                        <div class="mb-3">
                            <a href="{{ route('admin.teachers.template') }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-download me-1"></i> Download Template
                            </a>
                        </div>

                        <div class="mb-3">
                            <label for="file" class="form-label">File Import <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="file" name="file" accept=".xlsx,.xls,.csv" required>
                            <div class="form-text">Format: .xlsx, .xls, atau .csv (Max: 2MB)</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-upload me-1"></i> Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection