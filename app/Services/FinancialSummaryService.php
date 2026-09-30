<?php

namespace App\Services;

use App\Models\House;
use App\Models\MonthlyBill;
use App\Models\Payment;
use App\Models\PaymentAllocation;

class FinancialSummaryService
{
    /**
     * Get financial metrics for a specific house.
     */
    public function getHouseFinancialSummary(int $houseId): array
    {
        $totalBills = (float) MonthlyBill::where('house_id', $houseId)->sum('amount');
        $totalAllocations = (float) PaymentAllocation::whereHas('payment', function ($q) use ($houseId) {
            $q->where('house_id', $houseId)->where('status', 'active');
        })->sum('amount');

        $totalActivePayments = (float) Payment::where('house_id', $houseId)
            ->where('status', 'active')
            ->sum('amount');

        $totalArrears = (float) MonthlyBill::where('house_id', $houseId)
            ->whereIn('status', ['unpaid', 'partial'])
            ->sum('remaining_amount');

        $creditBalance = max(0, $totalActivePayments - $totalAllocations);

        $lastPayment = Payment::where('house_id', $houseId)
            ->where('status', 'active')
            ->orderBy('payment_date', 'desc')
            ->first();

        return [
            'total_bills' => $totalBills,
            'total_paid' => $totalAllocations,
            'total_active_payments' => $totalActivePayments,
            'total_arrears' => $totalArrears,
            'credit_balance' => $creditBalance,
            'last_payment_date' => $lastPayment ? $lastPayment->payment_date->format('Y-m-d') : null,
            'last_payment_amount' => $lastPayment ? (float) $lastPayment->amount : 0,
        ];
    }

    /**
     * Get overall financial metrics for Pengurus Dashboard.
     */
    public function getAdminFinancialSummary(): array
    {
        $currentPeriod = now()->format('Y-m');

        $totalHouses = House::count();
        $occupiedHouses = House::where('status', 'ditempati')->count();

        $currentPeriodBillAmount = (float) MonthlyBill::where('billing_period', $currentPeriod)->sum('amount');
        $currentPeriodPaidAmount = (float) MonthlyBill::where('billing_period', $currentPeriod)->sum('paid_amount');

        $totalArrears = (float) MonthlyBill::whereIn('status', ['unpaid', 'partial'])->sum('remaining_amount');
        $totalCollectedAllTime = (float) Payment::where('status', 'active')->sum('amount');

        return [
            'total_houses' => $totalHouses,
            'occupied_houses' => $occupiedHouses,
            'current_period' => $currentPeriod,
            'current_period_bill' => $currentPeriodBillAmount,
            'current_period_paid' => $currentPeriodPaidAmount,
            'total_arrears' => $totalArrears,
            'total_collected_all_time' => $totalCollectedAllTime,
        ];
    }
}
