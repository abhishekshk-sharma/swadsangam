<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class OrdersExport implements FromCollection, WithHeadings, WithMapping, WithEvents
{
    protected $orders;
    protected $gstStats;

    public function __construct($orders, array $gstStats = [])
    {
        $this->orders   = $orders;
        $this->gstStats = $gstStats;
    }

    public function collection()
    {
        return $this->orders;
    }

    public function headings(): array
    {
        return [
            'Order ID',
            'Table',
            'Items',
            'Subtotal (₹)',
            'CGST (₹)',
            'SGST (₹)',
            'Grand Total (₹)',
            'Status',
            'Payment Mode',
            'Created By',
            'Date',
            'Time',
        ];
    }

    public function map($order): array
    {
        $branch  = $order->branch;
        $slab    = $branch?->gstSlab;
        $mode    = $branch?->gst_mode;
        $base    = (float) $order->total_amount;
        $cgst    = 0;
        $sgst    = 0;
        $grand   = $base;

        if ($slab && $mode && $order->status === 'paid') {
            $cgstPct = (float) $slab->cgst_rate;
            $sgstPct = (float) $slab->sgst_rate;
            if ($mode === 'excluded') {
                $cgst  = round($base * $cgstPct / 100, 2);
                $sgst  = round($base * $sgstPct / 100, 2);
                $grand = $base + $cgst + $sgst;
            } else {
                $totalPct = $cgstPct + $sgstPct;
                $base     = round($base * 100 / (100 + $totalPct), 2);
                $cgst     = round($base * $cgstPct / 100, 2);
                $sgst     = round($base * $sgstPct / 100, 2);
                $grand    = (float) $order->total_amount;
            }
        }

        return [
            $order->id,
            $order->is_parcel ? 'Parcel' : 'Table ' . ($order->table?->table_number ?? '-'),
            $order->orderItems->pluck('menuItem.name')->filter()->implode(', '),
            number_format($base, 2),
            $cgst ? number_format($cgst, 2) : '-',
            $sgst ? number_format($sgst, 2) : '-',
            number_format($grand, 2),
            ucfirst($order->status),
            $order->payment_mode ? ucfirst($order->payment_mode) : '-',
            $order->user?->name ?? '-',
            $order->created_at->format('d-m-Y'),
            $order->created_at->format('h:i A'),
        ];
    }

    public function registerEvents(): array
    {
        $gst = $this->gstStats;
        if (empty($gst['enabled'])) return [];

        return [
            AfterSheet::class => function (AfterSheet $event) use ($gst) {
                $sheet     = $event->sheet->getDelegate();
                $lastRow   = $this->orders->count() + 2; // +1 heading +1 for next row
                $summaryRow = $lastRow + 1;

                $sheet->setCellValue('A' . $summaryRow, 'GST Summary');
                $sheet->setCellValue('B' . $summaryRow, 'Total CGST: ₹' . number_format($gst['cgst'], 2));
                $sheet->setCellValue('C' . $summaryRow, 'Total SGST: ₹' . number_format($gst['sgst'], 2));
                $sheet->setCellValue('D' . $summaryRow, 'Total GST: ₹' . number_format($gst['total'], 2));

                $sheet->getStyle('A' . $summaryRow . ':D' . $summaryRow)->getFont()->setBold(true);
            },
        ];
    }
}
