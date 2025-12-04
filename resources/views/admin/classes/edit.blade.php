@extends('layouts.admin')

@section('title', 'Edit Kelas')
@section('page-title', 'Edit Data Kelas')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">Form Edit Kelas</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.classes.update', $classRoom) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="class_name" class="form-label">Nama Kelas <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('class_name') is-invalid @enderror"
                                id="class_name" name="class_name" value="{{ old('class_name', $classRoom->class_name) }}"
                                required>
                            @error('class_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">QR Code</label>
                            <input type="text" class="form-control" value="{{ $classRoom->qr_code }}" readonly disabled>
                            <small class="text-muted">QR Code tidak dapat diubah</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="latitude" class="form-label">Latitude <span class="text-danger">*</span></label>
                                <input type="number" step="any" class="form-control @error('latitude') is-invalid @enderror"
                                    id="latitude" name="latitude" value="{{ old('latitude', $classRoom->latitude) }}"
                                    required>
                                @error('latitude')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="longitude" class="form-label">Longitude <span
                                        class="text-danger">*</span></label>
                                <input type="number" step="any"
                                    class="form-control @error('longitude') is-invalid @enderror" id="longitude"
                                    name="longitude" value="{{ old('longitude', $classRoom->longitude) }}" required>
                                @error('longitude')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="radius" class="form-label">Radius (meter) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('radius') is-invalid @enderror" id="radius"
                                name="radius" value="{{ old('radius', $classRoom->radius) }}" required min="1">
                            @error('radius')
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
                                <i class="bi bi-check-lg me-1"></i> Update
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