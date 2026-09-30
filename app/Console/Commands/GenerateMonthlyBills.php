<?php

namespace App\Console\Commands;

use App\Services\MonthlyBillingService;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('bill:generate-monthly {--period= : Periode YYYY-MM, default bulan berjalan}')]
#[Description('Generate tagihan iuran bulanan otomatis (default: bulan berjalan)')]
class GenerateMonthlyBills extends Command
{
    public function handle(MonthlyBillingService $billingService): int
    {
        $period = $this->option('period') ?: Carbon::now()->format('Y-m');

        $result = $billingService->generateMonthlyBillsUpTo($period);

        $this->info("Generate tagihan s.d. periode {$result['billing_period']} selesai.");
        $this->line("Dibuat: {$result['generated_count']}");
        $this->line("Skip (sudah ada/belum mulai): {$result['skipped_count']}");

        return self::SUCCESS;
    }
}