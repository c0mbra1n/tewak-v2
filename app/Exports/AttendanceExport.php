<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $attendances;
    protected $filters;

    public function __construct($attendances, $filters = [])
    {
        $this->attendances = $attendances;
        $this->filters = $filters;
    }

    public function collection()
    {
        return $this->attendances;
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Nama Guru',
            'Mata Pelajaran',
            'Kelas',
            'Status',
            'Waktu Scan',
        ];
    }

    public function map($attendance): array
    {
        static $no = 0;
        $no++;

        $statusLabels = [
            'hadir' => 'Hadir',
            'telat' => 'Telat',
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            'alpa' => 'Alpa',
            'dinas' => 'Dinas Luar',
        ];

        return [
            $no,
            Carbon::parse($attendance->date)->format('d/m/Y'),
            $attendance->user->full_name ?? '-',
            $attendance->subject ?? '-',
            $attendance->classRoom->class_name ?? '-',
            $statusLabels[$attendance->status] ?? ucfirst($attendance->status),
            $attendance->scan_time ? Carbon::parse($attendance->scan_time)->format('H:i:s') : '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
