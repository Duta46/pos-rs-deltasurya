<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kwitansi - {{ $transaction->invoice_number }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .info { margin-bottom: 20px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f2 f2 f2; }
        .footer { text-align: right; }
        .total-row td { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2>BUKTI PEMBAYARAN</h2>
        <p>Rumah Sakit Delta Surya</p>
    </div>

    <div class="info">
        <table style="width: 100%">
            <tr>
                <td width="150">No. Invoice</td>
                <td>: {{ $transaction->invoice_number }}</td>
                <td width="150">Tanggal</td>
                <td>: {{ $transaction->paid_at ? $transaction->paid_at->format('d/m/Y H:i') : '-' }}</td>
            </tr>
            <tr>
                <td>Nama Pasien</td>
                <td>: {{ $transaction->patient_name }}</td>
                <td>Kasir</td>
                <td>: {{ $transaction->user->name }}</td>
            </tr>
            <tr>
                <td>Asuransi</td>
                <td>: {{ $transaction->insurance_name }}</td>
                <td>Status</td>
                <td>: {{ $transaction->status === \App\Models\Transaction::STATUS_PAID ? 'LUNAS' : 'DRAF' }}</td>
            </tr>
        </table>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Tindakan / Jasa Medis</th>
                <th>Harga</th>
                <th>Qty</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transaction->details as $index => $detail)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $detail->procedure_name }}</td>
                <td>Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                <td>{{ $detail->qty }}</td>
                <td>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4" style="text-align: right">Total Harga</td>
                <td>Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="4" style="text-align: right">Diskon Voucher</td>
                <td>Rp {{ number_format($transaction->total_discount, 0, ',', '.') }}</td>
            </tr>
            <tr class="total-row" style="font-size: 14px; background-color: #eee;">
                <td colspan="4" style="text-align: right">GRAND TOTAL</td>
                <td>Rp {{ number_format($transaction->grand_total, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Sidoarjo, {{ date('d F Y') }}</p>
        <br><br><br>
        <p>( ____________________ )</p>
        <p>Tanda Tangan Kasir</p>
    </div>
</body>
</html>
