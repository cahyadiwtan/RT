<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\House;
use App\Models\MonthlyBill;
use App\Models\MonthlyFeeSetting;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\Resident;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MonthlyBillingService
{
    /**
     * Generate monthly bills for all active houses for a given period (format YYYY-MM).
     * Idempotent: skipped if bill for house + period already exists.
     */
    public function generateMonthlyBills(string $billingPeriod): array
    {
        $generatedCount = 0;
        $skippedCount = 0;

        $houses = House::where('status', 'ditempati')->get();

        DB::transaction(function () use ($houses, $billingPeriod, &$generatedCount, &$skippedCount) {
            foreach ($houses as $house) {
                if ($this->createBillForHouse($house, $billingPeriod)) {
                    $generatedCount++;
                } else {
                    $skippedCount++;
                }
            }
        });

        return [
            'billing_period' => $billingPeriod,
            'fee_amount' => $this->getFeeAmountFor($billingPeriod),
            'generated_count' => $generatedCount,
            'skipped_count' => $skippedCount,
        ];
    }

    /**
     * Generate bills for every month from each house's billing start
     * (bulan setelah tanggal_tinggal kepala keluarga) up to the target period.
     * Rumah tanpa tanggal_tinggal hanya ditagih untuk bulan target (perilaku lama).
     * Idempotent per (house, period).
     */
    public function generateMonthlyBillsUpTo(string $targetPeriod): array
    {
        $targetDate = Carbon::parse($targetPeriod.'-01');
        $generatedCount = 0;
        $skippedCount = 0;

        $houses = House::where('status', 'ditempati')->get();

        DB::transaction(function () use ($houses, $targetPeriod, $targetDate, &$generatedCount, &$skippedCount) {
            foreach ($houses as $house) {
                $startMonth = $this->getBillingStartMonth($house);

                if ($startMonth === null) {
                    // Tidak ada tanggal_tinggal -> hanya periode target
                    if ($this->createBillForHouse($house, $targetPeriod)) {
                        $generatedCount++;
                    } else {
                        $skippedCount++;
                    }

                    continue;
                }

                if ($startMonth->gt($targetDate)) {
                    // Belum mulai ditagih sampai dengan bulan target
                    $skippedCount++;

                    continue;
                }

                for ($month = $startMonth->copy(); $month->lte($targetDate); $month->addMonth()) {
                    if ($this->createBillForHouse($house, $month->format('Y-m'))) {
                        $generatedCount++;
                    } else {
                        $skippedCount++;
                    }
                }
            }
        });

        return [
            'billing_period' => $targetPeriod,
            'fee_amount' => $this->getFeeAmountFor($targetPeriod),
            'generated_count' => $generatedCount,
            'skipped_count' => $skippedCount,
        ];
    }

    /**
     * Generate bills for a period range (inclusive), from "YYYY-MM" to "YYYY-MM",
     * for every active house. Idempotent per (house, period). For each house the
     * move-in constraint (skip the move-in month) still applies, and any deposit
     * (saldo kredit) is auto-allocated to settle newly-issued bills.
     */
    public function generateMonthlyBillsRange(string $fromPeriod, string $toPeriod): array
    {
        $fromDate = Carbon::parse($fromPeriod.'-01')->startOfMonth();
        $toDate = Carbon::parse($toPeriod.'-01')->startOfMonth();

        if ($toDate->lt($fromDate)) {
            throw new \InvalidArgumentException('Periode akhir tidak boleh sebelum periode awal.');
        }

        $generatedCount = 0;
        $skippedCount = 0;

        $houses = House::where('status', 'ditempati')->get();

        DB::transaction(function () use ($houses, $fromDate, $toDate, &$generatedCount, &$skippedCount) {
            for ($month = $fromDate->copy(); $month->lte($toDate); $month->addMonth()) {
                $period = $month->format('Y-m');

                foreach ($houses as $house) {
                    if ($this->createBillForHouse($house, $period)) {
                        $generatedCount++;
                    } else {
                        $skippedCount++;
                    }
                }
            }
        });

        return [
            'billing_period' => $toPeriod,
            'fee_amount' => $this->getFeeAmountFor($toPeriod),
            'generated_count' => $generatedCount,
            'skipped_count' => $skippedCount,
        ];
    }

    /**
     * Create one bill for a house + period. Returns true if created, false if skipped.
     */
    private function createBillForHouse(House $house, string $billingPeriod): bool
    {
        $exists = MonthlyBill::where('house_id', $house->id)
            ->where('billing_period', $billingPeriod)
            ->exists();

        if ($exists) {
            return false;
        }

        $periodDate = Carbon::parse($billingPeriod.'-01');

        // Tagihan bulan pertama (bulan saat tanggal_tinggal) dilewati
        $billingStartDate = $this->getBillingStartDate($house);
        if ($billingStartDate && $billingStartDate->format('Y-m') === $periodDate->format('Y-m')) {
            return false;
        }

        // Jangan menagih periode sebelum bulan mulai tagih (bulan setelah tanggal_tinggal KK)
        $billingStartMonth = $this->getBillingStartMonth($house);
        if ($billingStartMonth && $periodDate->lt($billingStartMonth)) {
            return false;
        }

        $feeAmount = $this->getFeeAmountFor($billingPeriod);
        $dueDate = $periodDate->copy()->endOfMonth();

        MonthlyBill::create([
            'house_id' => $house->id,
            'billing_period' => $billingPeriod,
            'amount' => $feeAmount,
            'paid_amount' => 0,
            'remaining_amount' => $feeAmount,
            'status' => 'unpaid',
            'due_date' => $dueDate,
        ]);

        $this->applyDepositAllocations($house->id, $billingPeriod);

        return true;
    }

    /**
     * Auto-allocate leftover credit (saldo deposit / kelebihan bayar) of a house
     * to a newly-issued bill, FIFO over the payment source, until the bill is
     * settled or the deposit runs out.
     */
    private function applyDepositAllocations(int $houseId, string $billingPeriod): void
    {
        $bill = MonthlyBill::where('house_id', $houseId)
            ->where('billing_period', $billingPeriod)
            ->first();

        if (! $bill || (float) $bill->remaining_amount <= 0) {
            return;
        }

        $remainingToCover = (float) $bill->remaining_amount;
        $allocated = 0;

        // Payments with leftover credit, FIFO by date then id
        $payments = Payment::where('house_id', $houseId)
            ->where('status', 'active')
            ->orderBy('payment_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        foreach ($payments as $payment) {
            $alreadyAllocated = (float) PaymentAllocation::where('payment_id', $payment->id)
                ->sum('amount');

            $paymentCredit = (float) $payment->amount - $alreadyAllocated;

            if ($paymentCredit <= 0) {
                continue;
            }

            $allocation = min($remainingToCover, $paymentCredit);

            PaymentAllocation::create([
                'payment_id' => $payment->id,
                'bill_id' => $bill->id,
                'amount' => $allocation,
            ]);

            $allocated += $allocation;
            $remainingToCover -= $allocation;

            if ($remainingToCover <= 0) {
                break;
            }
        }

        if ($allocated <= 0) {
            return;
        }

        $newPaid = (float) $bill->paid_amount + $allocated;
        $newRemaining = max(0, (float) $bill->amount - $newPaid);
        $newStatus = ($newRemaining <= 0) ? 'paid' : 'partial';

        $bill->update([
            'paid_amount' => $newPaid,
            'remaining_amount' => $newRemaining,
            'status' => $newStatus,
        ]);

        AuditLog::create([
            'user_id' => null,
            'action' => 'AUTO_ALLOCATE_DEPOSIT',
            'auditable_type' => MonthlyBill::class,
            'auditable_id' => $bill->id,
            'new_values' => [
                'house_id' => $houseId,
                'billing_period' => $billingPeriod,
                'allocated' => $allocated,
                'source' => 'saldo_deposit',
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Fee amount applicable for a given period (YYYY-MM).
     */
    public function getFeeAmountFor(string $billingPeriod): float
    {
        $periodDate = Carbon::parse($billingPeriod.'-01');

        $setting = MonthlyFeeSetting::where('is_active', true)
            ->where('effective_from', '<=', $periodDate->format('Y-m-d'))
            ->where(function ($query) use ($periodDate) {
                $query->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', $periodDate->format('Y-m-d'));
            })
            ->orderBy('effective_from', 'desc')
            ->first();

        return $setting ? (float) $setting->amount : 30000.00;
    }

    /**
     * Tanggal tinggal kepala keluarga (bulan tagihan mulai = bulan setelahnya).
     */
    private function getBillingStartDate(House $house): ?Carbon
    {
        $kepalaKeluarga = $this->getKepalaKeluarga($house);

        return $kepalaKeluarga?->tanggal_tinggal;
    }

    /**
     * Bulan pertama yang ditagih untuk rumah = bulan setelah tanggal_tinggal KK.
     */
    private function getBillingStartMonth(House $house): ?Carbon
    {
        $tanggalTinggal = $this->getBillingStartDate($house);

        if ($tanggalTinggal === null) {
            return null;
        }

        return $tanggalTinggal->copy()->addMonth()->startOfMonth();
    }

    private function getKepalaKeluarga(House $house): ?Resident
    {
        return $house->residents()
            ->where('hubungan_dalam_keluarga', 'kepala_keluarga')
            ->where('status_warga', 'aktif')
            ->orderBy('tanggal_tinggal')
            ->first();
    }
}