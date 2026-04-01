<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TransactionReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $filePath;
    public $reportDate;

    public function __construct($filePath, $reportDate)
    {
        $this->filePath = $filePath;
        $this->reportDate = $reportDate;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Laporan Transaksi Pembayaran - ' . $this->reportDate,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.transaction_report',
            with: [
                'date' => $this->reportDate,
                'fileName' => 'laporan_transaksi_' . $this->reportDate . '.xlsx'
            ]
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromStorageDisk('local', $this->filePath)
                ->as('laporan_transaksi_' . $this->reportDate . '.xlsx'),
        ];
    }
}
