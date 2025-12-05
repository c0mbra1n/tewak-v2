@extends('layouts.admin')

@section('title', 'Tambah Kelas')
@section('page-title', 'Tambah Kelas Baru')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">Form Tambah Kelas</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.classes.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="class_name" class="form-label">Nama Kelas <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('class_name') is-invalid @enderror"
                                id="class_name" name="class_name" value="{{ old('class_name') }}" required
                                placeholder="Contoh: X IPA 1">
                            @error('class_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="latitude" class="form-label">Latitude <span class="text-danger">*</span></label>
                                <input type="number" step="any" class="form-control @error('latitude') is-invalid @enderror"
                                    id="latitude" name="latitude" value="{{ old('latitude') }}" required
                                    placeholder="-6.200000">
                                @error('latitude')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="longitude" class="form-label">Longitude <span
                                        class="text-danger">*</span></label>
                                <input type="number" step="any"
                                    class="form-control @error('longitude') is-invalid @enderror" id="longitude"
                                    name="longitude" value="{{ old('longitude') }}" required placeholder="106.816666">
                                @error('longitude')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="radius" class="form-label">Radius (meter) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('radius') is-invalid @enderror" id="radius"
                                name="radius" value="{{ old('radius', 50) }}" required min="1" placeholder="50">
                            <small class="text-muted">Jarak maksimal dalam meter untuk validasi lokasi absensi</small>
                            @error('radius')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="block" class="form-label">Blok Monitoring</label>
                            <select class="form-select @error('block') is-invalid @enderror" id="block" name="block">
                                <option value="">-- Tidak ada --</option>
                                @for($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ old('block') == $i ? 'selected' : '' }}>
                                        Blok {{ $i }}
                                    </option>
                                @endfor
                            </select>
                            <small class="text-muted">Pilih blok untuk menampilkan kelas ini di halaman monitoring
                                blok</small>
                            @error('block')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <button type="button" class="btn btn-outline-info btn-sm" id="btn-get-location">
                                <i class="bi bi-geo-alt me-1"></i> Gunakan Lokasi Saat Ini
                            </button>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i> Simpan
                            </button>
                            <a href="{{ route('admin.classes.index') }}" class="btn btn-secondary">
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
        document.getElementById('btn-get-location').addEventListener('click', function () {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (position) {
                    document.getElementById('latitude').value = position.coords.latitude;
                    document.getElementById('longitude').value = position.coords.longitude;
                }, function (error) {
                    alert('Gagal mendapatkan lokasi: ' + error.message);
                });
            } else {
                alert('Geolocation tidak didukung browser ini.');
            }
        });
    </script>
@endpush