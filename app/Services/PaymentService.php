<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\MonthlyBill;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use Exception;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    /**
     * Record a new payment and allocate to outstanding bills using FIFO.
     */
    public function recordPayment(array $data, int $recordedByUserId): Payment
    {
        return DB::transaction(function () use ($data, $recordedByUserId) {
            $payment = Payment::create([
                'house_id' => $data['house_id'],
                'resident_id' => $data['resident_id'] ?? null,
                'payment_date' => $data['payment_date'],
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'] ?? 'cash',
                'reference_number' => $data['reference_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'recorded_by' => $recordedByUserId,
                'status' => 'active',
            ]);

            $remainingAmountToAllocate = (float) $data['amount'];

            // Fetch unpaid or partial bills ordered by billing_period ASC (FIFO)
            $bills = MonthlyBill::where('house_id', $data['house_id'])
                ->whereIn('status', ['unpaid', 'partial'])
                ->orderBy('billing_period', 'asc')
                ->lockForUpdate()
                ->get();

            foreach ($bills as $bill) {
                if ($remainingAmountToAllocate <= 0) {
                    break;
                }

                $unpaidBillBalance = (float) $bill->remaining_amount;
                $allocation = min($remainingAmountToAllocate, $unpaidBillBalance);

                PaymentAllocation::create([
                    'payment_id' => $payment->id,
                    'bill_id' => $bill->id,
                    'amount' => $allocation,
                ]);

                $newPaid = (float) $bill->paid_amount + $allocation;
                $newRemaining = (float) $bill->amount - $newPaid;
                $newStatus = ($newRemaining <= 0) ? 'paid' : 'partial';

                $bill->update([
                    'paid_amount' => $newPaid,
                    'remaining_amount' => max(0, $newRemaining),
                    'status' => $newStatus,
                ]);

                $remainingAmountToAllocate -= $allocation;
            }

            // Audit log
            AuditLog::create([
                'user_id' => $recordedByUserId,
                'action' => 'CREATE_PAYMENT',
                'auditable_type' => Payment::class,
                'auditable_id' => $payment->id,
                'new_values' => [
                    'house_id' => $payment->house_id,
                    'amount' => $payment->amount,
                    'allocated' => (float) $data['amount'] - $remainingAmountToAllocate,
                    'credit' => max(0, $remainingAmountToAllocate),
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return $payment;
        });
    }

    /**
     * Void a payment and reverse allocations.
     */
    public function voidPayment(int $paymentId, int $performedByUserId): Payment
    {
        return DB::transaction(function () use ($paymentId, $performedByUserId) {
            $payment = Payment::with('allocations.bill')->findOrFail($paymentId);

            if ($payment->status === 'void') {
                throw new Exception('Pembayaran ini sudah dibatalkan (void).');
            }

            foreach ($payment->allocations as $allocation) {
                $bill = $allocation->bill;
                if ($bill) {
                    $newPaid = max(0, (float) $bill->paid_amount - (float) $allocation->amount);
                    $newRemaining = (float) $bill->amount - $newPaid;
                    $newStatus = ($newPaid <= 0) ? 'unpaid' : (($newRemaining <= 0) ? 'paid' : 'partial');

                    $bill->update([
                        'paid_amount' => $newPaid,
                        'remaining_amount' => $newRemaining,
                        'status' => $newStatus,
                    ]);
                }
            }

            $payment->update(['status' => 'void']);

            AuditLog::create([
                'user_id' => $performedByUserId,
                'action' => 'VOID_PAYMENT',
                'auditable_type' => Payment::class,
                'auditable_id' => $payment->id,
                'old_values' => ['status' => 'active'],
                'new_values' => ['status' => 'void'],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return $payment;
        });
    }
}
