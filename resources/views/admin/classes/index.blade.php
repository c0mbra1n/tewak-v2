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
                            <th>Blok</th>
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
                                <td>
                                    @if($class->block)
                                        <span class="badge bg-primary">Blok {{ $class->block }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal"
                                        data-bs-target="#qrModal{{ $class->id }}">
                                        <i class="bi bi-qr-code me-1"></i> Lihat QR
                                    </button>
                                </td>
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

                            <!-- QR Code Modal -->
                            <div class="modal fade" id="qrModal{{ $class->id }}" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">QR Code - {{ $class->class_name }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body text-center">
                                            <div id="qrcode-{{ $class->id }}" class="d-inline-block mb-3"></div>
                                            <p class="text-muted small mb-2">Scan QR Code ini di depan kelas</p>
                                            <code class="d-block bg-light p-2 rounded small">{{ $class->qr_code }}</code>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Tutup</button>
                                            <button type="button" class="btn btn-primary"
                                                onclick="printQR('{{ $class->id }}', '{{ $class->class_name }}')">
                                                <i class="bi bi-printer me-1"></i> Print
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
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

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @foreach($classes as $class)
                new QRCode(document.getElementById("qrcode-{{ $class->id }}"), {
                    text: "{{ $class->qr_code }}|{{ date('Y-m-d') }}",
                    width: 200,
                    height: 200,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H
                });
            @endforeach
            });

        function printQR(classId, className) {
            const qrElement = document.getElementById('qrcode-' + classId);
            const qrImage = qrElement.querySelector('img') || qrElement.querySelector('canvas');

            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                    <html>
                    <head>
                        <title>QR Code - ${className}</title>
                        <style>
                            body { 
                                font-family: Arial, sans-serif; 
                                text-align: center; 
                                padding: 40px;
                            }
                            h1 { margin-bottom: 20px; }
                            .qr-container { margin: 20px auto; }
                            .note { color: #666; margin-top: 20px; }
                        </style>
                    </head>
                    <body>
                        <h1>${className}</h1>
                        <div class="qr-container">
                            <img src="${qrImage.src || qrImage.toDataURL()}" width="300" height="300">
                        </div>
                        <p class="note">Scan QR Code ini untuk absensi</p>
                        <p class="note">Tanggal: ${new Date().toLocaleDateString('id-ID')}</p>
                    </body>
                    </html>
                `);
            printWindow.document.close();
            printWindow.onload = function () {
                printWindow.print();
            };
        }
    </script>
@endpush