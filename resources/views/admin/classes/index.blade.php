@extends('layouts.admin')

@section('title', 'Data Kelas')
@section('page-title', 'Manajemen Data Kelas')

@section('content')
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">Daftar Kelas</h5>
            <a href="{{ route('admin.classes.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Tambah Kelas
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Nama Kelas</th>
                            <th>QR Code</th>
                            <th>Koordinat</th>
                            <th>Radius (m)</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($classes as $index => $class)
                            <tr>
                                <td class="ps-4">{{ $index + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-info text-white d-flex align-items-center justify-content-center rounded me-3"
                                            style="width: 40px; height: 40px;">
                                            <i class="bi bi-building"></i>
                                        </div>
                                        <span class="fw-bold">{{ $class->class_name }}</span>
                                    </div>
                                </td>
                                <td><code class="text-muted small">{{ Str::limit($class->qr_code, 20) }}</code></td>
                                <td class="small">{{ $class->latitude }}, {{ $class->longitude }}</td>
                                <td>{{ $class->radius }} m</td>
                                <td>
                                    <a href="{{ route('admin.classes.edit', $class) }}"
                                        class="btn btn-sm btn-outline-primary me-1">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.classes.destroy', $class) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Yakin ingin menghapus kelas ini?')">
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
                                <td colspan="6" class="text-center py-4 text-muted">Belum ada data kelas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection