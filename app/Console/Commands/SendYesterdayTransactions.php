<?php

namespace App\Console\Commands;

use App\Exports\TransactionsExport;
use App\Mail\TransactionReportMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use App\Services\ActivityLoggerService;

class SendYesterdayTransactions extends Command
{
    protected $signature = 'report:send-yesterday';
    protected $description = 'Kirim laporan transaksi ke email dalam format Excel';

    public function handle()
    {
        $yesterday = Carbon::yesterday();
        $dateString = $yesterday->format('Y-m-d');
        $fileName = 'temp/laporan_' . $dateString . '.xlsx';

        // 1. Simpan Excel ke storage sementara
        Excel::store(new TransactionsExport($yesterday), $fileName, 'local');

        // 2. Kirim Email
        if (Storage::disk('local')->exists($fileName)) {
            Mail::to('interview.deltasurya@yopmail.com')
                ->send(new TransactionReportMail($fileName, $dateString));

            $this->info('Laporan transaksi kemarin berhasil dikirim ke email.');
        } else {
            $this->error('File laporan tidak ditemukan di storage local: ' . $fileName);
        }

        app(ActivityLoggerService::class)->log(
            'Cronjob: Send Transaction Report',
            'Sistem mengirim laporan transaksi tanggal ' . $dateString . ' melalui email ke interview.deltasurya@yopmail.com.',
            1 
        );
    }
}
