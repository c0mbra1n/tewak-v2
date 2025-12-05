<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UsersTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function array(): array
    {
        return [
            ['guru1', 'Nama Guru Lengkap', '123456', 'guru', 'Matematika'],
            ['guru2', 'Nama Guru Contoh', '123456', 'guru', 'Bahasa Indonesia'],
            ['admin_kelas_x', 'Nama Siswa Admin Kelas', '123456', 'admin_kelas', ''],
        ];
    }

    public function headings(): array
    {
        return [
            'username',
            'nama_lengkap',
            'password',
            'role',
            'mata_pelajaran',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Set header style
        $sheet->getStyle('A1:E1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4361EE'],
            ],
        ]);

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(25);

        return [];
    }
}
