@extends('layouts.admin')

@section('title', 'Data User')
@section('page-title', 'Manajemen Data User')

@section('content')
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">Daftar Guru & Admin Kelas</h5>
            <a href="{{ route('admin.teachers.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Tambah User
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Nama Lengkap</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Mata Pelajaran</th>
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
                                <td>{{ $teacher->subject ?? '-' }}</td>
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
@endsection