@extends('layouts.admin')

@section('title', 'Data Mata Pelajaran')
@section('page-title', 'Data Mata Pelajaran')

@section('content')
    <div class="row">
        <!-- Form Add Subject -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-plus-circle me-2"></i>Tambah Mapel</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.subjects.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Mapel <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="code" class="form-label">Kode Singkat</label>
                            <input type="text" class="form-control @error('code') is-invalid @enderror" id="code"
                                name="code" value="{{ old('code') }}" placeholder="cth: MTK, IPA">
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-plus-lg me-1"></i> Tambah
                        </button>
                    </form>

                    <hr>

                    <button type="button" class="btn btn-success w-100" data-bs-toggle="modal"
                        data-bs-target="#importModal">
                        <i class="bi bi-upload me-1"></i> Import dari Excel
                    </button>
                </div>
            </div>
        </div>

        <!-- List Subjects -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-book me-2"></i>Daftar Mata Pelajaran</h6>
                </div>
                <div class="card-body p-0">
                    <!-- Mobile View -->
                    <div class="d-lg-none">
                        @forelse($subjects as $subject)
                            <div class="p-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-bold">{{ $subject->name }}</div>
                                        <small class="text-muted">
                                            {{ $subject->code ? "($subject->code)" : '' }}
                                            • {{ $subject->users_count }} guru
                                        </small>
                                    </div>
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2"
                                            data-bs-toggle="modal" data-bs-target="#editModal{{ $subject->id }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form action="{{ route('admin.subjects.destroy', $subject) }}" method="POST"
                                            onsubmit="return confirm('Yakin hapus mapel ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted">Belum ada data mapel.</div>
                        @endforelse
                    </div>

                    <!-- Desktop View -->
                    <div class="table-responsive d-none d-lg-block">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">#</th>
                                    <th>Nama Mapel</th>
                                    <th>Kode</th>
                                    <th>Jumlah Guru</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($subjects as $index => $subject)
                                    <tr>
                                        <td class="ps-4">{{ $index + 1 }}</td>
                                        <td class="fw-bold">{{ $subject->name }}</td>
                                        <td>
                                            @if($subject->code)
                                                <span class="badge bg-secondary">{{ $subject->code }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">{{ $subject->users_count }} guru</span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-primary me-1"
                                                data-bs-toggle="modal" data-bs-target="#editModal{{ $subject->id }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form action="{{ route('admin.subjects.destroy', $subject) }}" method="POST"
                                                class="d-inline" onsubmit="return confirm('Yakin hapus mapel ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>

                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editModal{{ $subject->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="{{ route('admin.subjects.update', $subject) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Mapel</h5>
                                                        <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">Nama Mapel</label>
                                                            <input type="text" class="form-control" name="name"
                                                                value="{{ $subject->name }}" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Kode</label>
                                                            <input type="text" class="form-control" name="code"
                                                                value="{{ $subject->code }}">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-primary">Simpan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Belum ada data mata pelajaran.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-upload me-2"></i>Import Data Mapel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.subjects.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Petunjuk:</strong>
                            <ol class="mb-0 mt-2 small">
                                <li>Download template terlebih dahulu</li>
                                <li>Isi nama mapel dan kode (opsional)</li>
                                <li>Upload file Excel</li>
                                <li>Mapel yang sudah ada akan dilewati</li>
                            </ol>
                        </div>

                        <div class="mb-3">
                            <a href="{{ route('admin.subjects.template') }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-download me-1"></i> Download Template
                            </a>
                        </div>

                        <div class="mb-3">
                            <label for="file" class="form-label">File Import <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="file" name="file" accept=".xlsx,.xls,.csv" required>
                            <div class="form-text">Format: .xlsx, .xls (Max: 2MB)</div>
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