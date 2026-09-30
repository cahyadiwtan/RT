<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Generate tagihan iuran bulanan otomatis tiap tanggal 1 pukul 01:00
Schedule::command('bill:generate-monthly')
    ->monthlyOn(1, '01:00')
    ->withoutOverlapping()
    ->onSuccess(function () {
        logger()->info('Tagihan bulanan berhasil digenerate otomatis.');
    })
    ->onFailure(function () {
        logger()->error('Gagal generate tagihan bulanan otomatis.');
    });
