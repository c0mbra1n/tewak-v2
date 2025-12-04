@extends('layouts.admin')

@section('title', 'Jadwal Mengajar')
@section('page-title', 'Manajemen Jadwal Mengajar')

@section('content')
    <div class="row">
        <!-- Form Tambah Jadwal -->
        <div class="col-lg-5 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Tambah Jadwal</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.schedules.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="user_id" class="form-label">Guru <span class="text-danger">*</span></label>
                            <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id"
                                required>
                                <option value="">Pilih Guru</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" {{ old('user_id') == $teacher->id ? 'selected' : '' }}>
                                        {{ $teacher->full_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="day" class="form-label">Hari <span class="text-danger">*</span></label>
                            <select class="form-select @error('day') is-invalid @enderror" id="day" name="day" required>
                                <option value="">Pilih Hari</option>
                                <option value="Monday" {{ old('day') == 'Monday' ? 'selected' : '' }}>Senin</option>
                                <option value="Tuesday" {{ old('day') == 'Tuesday' ? 'selected' : '' }}>Selasa</option>
                                <option value="Wednesday" {{ old('day') == 'Wednesday' ? 'selected' : '' }}>Rabu</option>
                                <option value="Thursday" {{ old('day') == 'Thursday' ? 'selected' : '' }}>Kamis</option>
                                <option value="Friday" {{ old('day') == 'Friday' ? 'selected' : '' }}>Jumat</option>
                                <option value="Saturday" {{ old('day') == 'Saturday' ? 'selected' : '' }}>Sabtu</option>
                            </select>
                            @error('day')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="class_id" class="form-label">Kelas <span class="text-danger">*</span></label>
                            <select class="form-select @error('class_id') is-invalid @enderror" id="class_id"
                                name="class_id" required>
                                <option value="">Pilih Kelas</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                        {{ $class->class_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('class_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="subject" class="form-label">Mata Pelajaran <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('subject') is-invalid @enderror" id="subject"
                                name="subject" value="{{ old('subject') }}" required>
                            @error('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-6 mb-3">
                                <label for="start_time" class="form-label">Jam Mulai <span
                                        class="text-danger">*</span></label>
                                <input type="time" class="form-control @error('start_time') is-invalid @enderror"
                                    id="start_time" name="start_time" value="{{ old('start_time') }}" required>
                                @error('start_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6 mb-3">
                                <label for="lesson_hours" class="form-label">Jumlah JP <span
                                        class="text-danger">*</span></label>
                                <select class="form-select @error('lesson_hours') is-invalid @enderror" id="lesson_hours"
                                    name="lesson_hours" required>
                                    @for($i = 1; $i <= 8; $i++)
                                        <option value="{{ $i }}" {{ old('lesson_hours', 2) == $i ? 'selected' : '' }}>
                                            {{ $i }} JP ({{ $i * 45 }} menit)
                                        </option>
                                    @endfor
                                </select>
                                @error('lesson_hours')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-check-lg me-1"></i> Simpan Jadwal
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Daftar Jadwal -->
        <div class="col-lg-7 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-calendar3 me-2"></i>Semua Jadwal</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Hari</th>
                                    <th>Guru</th>
                                    <th>Kelas</th>
                                    <th>Mapel</th>
                                    <th>Waktu</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($schedules as $schedule)
                                    <tr>
                                        <td>
                                            @php
                                                $days = ['Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
                                            @endphp
                                            {{ $days[$schedule->day] ?? $schedule->day }}
                                        </td>
                                        <td>{{ $schedule->user->full_name ?? '-' }}</td>
                                        <td>{{ $schedule->classRoom->class_name ?? '-' }}</td>
                                        <td>{{ $schedule->subject }}</td>
                                        <td>
                                            <small>
                                                {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} -
                                                {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                                <span class="badge bg-secondary ms-1">{{ $schedule->lesson_hours }} JP</span>
                                            </small>
                                        </td>
                                        <td>
                                            <form action="{{ route('admin.schedules.destroy', $schedule) }}" method="POST"
                                                onsubmit="return confirm('Hapus jadwal ini?')">
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
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            Belum ada jadwal.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection