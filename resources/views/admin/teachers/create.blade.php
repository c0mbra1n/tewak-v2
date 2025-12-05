@extends('layouts.admin')

@section('title', 'Tambah User')
@section('page-title', 'Tambah User Baru')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('admin.teachers.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                                <select class="form-select @error('role') is-invalid @enderror" id="role" name="role"
                                    required>
                                    <option value="">Pilih Role</option>
                                    <option value="guru" {{ old('role') == 'guru' ? 'selected' : '' }}>Guru</option>
                                    <option value="admin_kelas" {{ old('role') == 'admin_kelas' ? 'selected' : '' }}>Admin
                                        Kelas</option>
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">Username <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('username') is-invalid @enderror"
                                    id="username" name="username" value="{{ old('username') }}" required>
                                @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="full_name" class="form-label">Nama Lengkap <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('full_name') is-invalid @enderror"
                                id="full_name" name="full_name" value="{{ old('full_name') }}" required>
                            @error('full_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Subjects for Guru - Multiple Select -->
                        <div class="mb-3" id="subjects-group" style="display: none;">
                            <label class="form-label">Mata Pelajaran</label>
                            <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                @forelse($subjects as $subject)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="subjects[]" 
                                            value="{{ $subject->id }}" id="subject{{ $subject->id }}"
                                            {{ in_array($subject->id, old('subjects', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="subject{{ $subject->id }}">
                                            {{ $subject->name }}
                                            @if($subject->code)
                                                <span class="text-muted">({{ $subject->code }})</span>
                                            @endif
                                        </label>
                                    </div>
                                @empty
                                    <p class="text-muted mb-0">Belum ada data mapel. 
                                        <a href="{{ route('admin.subjects.index') }}">Tambah mapel dulu</a>
                                    </p>
                                @endforelse
                            </div>
                            <small class="text-muted">Centang mapel yang diajarkan (bisa lebih dari 1)</small>
                        </div>

                        <!-- Class for Admin Kelas -->
                        <div class="mb-3" id="class-group" style="display: none;">
                            <label for="class_id" class="form-label">Kelas yang Dikelola</label>
                            <select class="form-select @error('class_id') is-invalid @enderror" id="class_id"
                                name="class_id">
                                <option value="">Pilih Kelas</option>
                                @foreach ($classes as $class)
                                    <option value="{{ $class->id }}"
                                        {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                        {{ $class->class_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('class_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                id="password" name="password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i> Simpan
                            </button>
                            <a href="{{ route('admin.teachers.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Kembali
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('role').addEventListener('change', function() {
            const subjectsGroup = document.getElementById('subjects-group');
            const classGroup = document.getElementById('class-group');

            if (this.value === 'guru') {
                subjectsGroup.style.display = 'block';
                classGroup.style.display = 'none';
            } else if (this.value === 'admin_kelas') {
                subjectsGroup.style.display = 'none';
                classGroup.style.display = 'block';
            } else {
                subjectsGroup.style.display = 'none';
                classGroup.style.display = 'none';
            }
        });

        // Trigger on page load
        document.getElementById('role').dispatchEvent(new Event('change'));
    </script>
@endpush