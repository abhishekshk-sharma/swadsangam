<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MenuOcrExport implements FromArray, WithHeadings, WithStyles
{
    public function __construct(private array $items) {}

    public function array(): array
    {
        return array_map(fn($item) => [
            $item['name'],
            $item['price'],
        ], $this->items);
    }

    public function headings(): array
    {
        return ['Item Name', 'Price (₹)'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
