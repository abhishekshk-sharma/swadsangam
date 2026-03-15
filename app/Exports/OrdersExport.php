<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrdersExport implements FromCollection, WithHeadings, WithMapping
{
    protected $orders;

    public function __construct($orders)
    {
        $this->orders = $orders;
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
            'Total Amount (₹)',
            'Status',
            'Payment Mode',
            'Created By',
            'Date',
            'Time',
        ];
    }

    public function map($order): array
    {
        return [
            $order->id,
            $order->is_parcel ? '📦 Parcel' : 'Table ' . ($order->table->table_number ?? '-'),
            $order->orderItems->pluck('menuItem.name')->implode(', '),
            number_format($order->total_amount, 2),
            ucfirst($order->status),
            $order->payment_mode ? ucfirst($order->payment_mode) : '-',
            $order->user->name ?? '-',
            $order->created_at->format('d-m-Y'),
            $order->created_at->format('h:i A'),
        ];
    }
}
