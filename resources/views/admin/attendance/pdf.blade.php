<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Absensi</title>
    <style>
        * {
            font-family: Arial, sans-serif;
        }

        body {
            margin: 20px;
        }

        h1 {
            text-align: center;
            color: #4361ee;
            font-size: 24px;
            margin-bottom: 5px;
        }

        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #4361ee;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .status-hadir {
            color: #10b981;
            font-weight: bold;
        }

        .status-telat {
            color: #f59e0b;
            font-weight: bold;
        }

        .status-izin {
            color: #06b6d4;
            font-weight: bold;
        }

        .status-sakit {
            color: #8b5cf6;
            font-weight: bold;
        }

        .status-dinas {
            color: #0ea5e9;
            font-weight: bold;
        }

        .status-alpa {
            color: #ef4444;
            font-weight: bold;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #999;
        }

        .print-btn {
            background: #4361ee;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="no-print">
        <button class="print-btn" onclick="window.print()">🖨️ Print / Save as PDF</button>
    </div>

    <h1>Laporan Absensi Guru</h1>
    <p class="subtitle">Dicetak pada: {{ date('d/m/Y H:i:s') }}</p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Nama Guru</th>
                <th>Mata Pelajaran</th>
                <th>Kelas</th>
                <th>Status</th>
                <th>Waktu Scan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $index => $attendance)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($attendance->date)->format('d/m/Y') }}</td>
                    <td>{{ $attendance->user->full_name ?? '-' }}</td>
                    <td>{{ $attendance->subject ?? '-' }}</td>
                    <td>{{ $attendance->classRoom->class_name ?? '-' }}</td>
                    <td class="status-{{ $attendance->status }}">{{ ucfirst($attendance->status) }}</td>
                    <td>{{ $attendance->scan_time ? \Carbon\Carbon::parse($attendance->scan_time)->format('H:i:s') : '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center;">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p class="footer">
        Tewak - Sistem Monitoring Kehadiran Guru<br>
        Total Data: {{ $attendances->count() }} record
    </p>
</body>

</html>