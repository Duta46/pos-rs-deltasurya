<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TransactionsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $date;

    public function __construct($date)
    {
        $this->date = $date;
    }

    public function collection()
    {
        return Transaction::with('user')
            ->whereDate('paid_at', $this->date)
            ->where('status', Transaction::STATUS_PAID)
            ->get();
    }

    public function headings(): array
    {
        return [
            'No. Invoice',
            'Nama Pasien',
            'Asuransi',
            'Total Harga',
            'Diskon',
            'Grand Total',
            'Tanggal Bayar',
            'Kasir',
        ];
    }

    public function map($transaction): array
    {
        return [
            $transaction->invoice_number,
            $transaction->patient_name,
            $transaction->insurance_name,
            $transaction->total_price,
            $transaction->total_discount,
            $transaction->grand_total,
            $transaction->paid_at->format('d/m/Y H:i'),
            $transaction->user->name ?? '-',
        ];
    }
}
