<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Penjadwalan Laporan Transaksi
Schedule::command('report:send-yesterday')->dailyAt('01:00');
