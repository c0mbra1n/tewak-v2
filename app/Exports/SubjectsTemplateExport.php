<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SubjectsTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function array(): array
    {
        return [
            ['Matematika', 'MTK'],
            ['Bahasa Indonesia', 'BIND'],
            ['Bahasa Inggris', 'BING'],
            ['Ilmu Pengetahuan Alam', 'IPA'],
            ['Ilmu Pengetahuan Sosial', 'IPS'],
        ];
    }

    public function headings(): array
    {
        return [
            'nama_mapel',
            'kode',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:B1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4361EE'],
            ],
        ]);

        $sheet->getColumnDimension('A')->setWidth(35);
        $sheet->getColumnDimension('B')->setWidth(15);

        return [];
    }
}
