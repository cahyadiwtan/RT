<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Event;
use App\Models\EventBudget;
use App\Models\EventExpense;
use App\Models\EventIncome;
use App\Models\EventParticipant;
use App\Models\EventPayment;
use App\Models\Resident;
use Exception;
use Illuminate\Support\Facades\DB;

class EventModerationService
{
    public function storeEvent(array $data, int $userId): Event
    {
        $data['created_by'] = $userId;

        return Event::create($data);
    }

    public function updateEvent(Event $event, array $data): void
    {
        $event->update($data);
    }

    public function removeEvent(Event $event): void
    {
        $event->delete();
    }

    public function updateStatus(Event $event, string $status): void
    {
        $event->update(['status' => $status]);
    }

    public function addParticipant(Event $event, int $residentId, ?float $amount = null, ?string $notes = null): ?EventParticipant
    {
        if (EventParticipant::where('event_id', $event->id)
            ->where('resident_id', $residentId)
            ->exists()) {
            return null;
        }

        return EventParticipant::create([
            'event_id' => $event->id,
            'resident_id' => $residentId,
            'participation_status' => 'registered',
            'payment_required' => $event->funding_type !== 'free',
            'payment_amount' => $amount ?? $event->required_payment,
            'payment_status' => $event->funding_type === 'free' ? 'paid' : 'unpaid',
            'registered_at' => now(),
            'notes' => $notes,
        ]);
    }

    public function bulkAddParticipants(Event $event, array $residentIds): int
    {
        $added = 0;

        foreach ($residentIds as $residentId) {
            if ($this->addParticipant($event, (int) $residentId) !== null) {
                $added++;
            }
        }

        return $added;
    }

    public function removeParticipant(EventParticipant $participant): void
    {
        $participant->delete();
    }

    public function updateParticipantStatus(EventParticipant $participant, string $status): void
    {
        $participant->update(['participation_status' => $status]);
    }

    public function storePayment(Event $event, array $data, int $userId): EventPayment
    {
        return DB::transaction(function () use ($event, $data, $userId) {
            $participant = EventParticipant::findOrFail($data['event_participant_id']);

            $payment = EventPayment::create([
                'event_id' => $event->id,
                'event_participant_id' => $participant->id,
                'resident_id' => $participant->resident_id,
                'payment_date' => $data['payment_date'],
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'] ?? 'cash',
                'reference_number' => $data['reference_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'recorded_by' => $userId,
                'status' => 'active',
            ]);

            $this->recalculateParticipantStatus($participant);

            EventIncome::create([
                'event_id' => $event->id,
                'income_type' => 'resident_fee',
                'description' => 'Pembayaran peserta: ' . ($participant->resident?->nama_lengkap ?? 'Warga'),
                'amount' => $payment->amount,
                'income_date' => $payment->payment_date,
                'source' => $participant->resident_id,
                'reference_number' => $payment->reference_number,
                'notes' => 'Auto dari pembayaran event',
                'recorded_by' => $userId,
                'status' => 'active',
                'linked_payment_id' => $payment->id,
            ]);

            AuditLog::create([
                'user_id' => $userId,
                'action' => 'CREATE_EVENT_PAYMENT',
                'auditable_type' => EventPayment::class,
                'auditable_id' => $payment->id,
                'new_values' => [
                    'event_id' => $event->id,
                    'amount' => $payment->amount,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return $payment;
        });
    }

    public function voidPayment(Event $event, EventPayment $payment, int $userId): void
    {
        DB::transaction(function () use ($event, $payment, $userId) {
            if ($payment->status === 'void') {
                throw new Exception('Pembayaran ini sudah dibatalkan (void).');
            }

            $payment->update(['status' => 'void']);

            EventIncome::where('linked_payment_id', $payment->id)
                ->where('status', 'active')
                ->update(['status' => 'void']);

            $participant = EventParticipant::find($payment->event_participant_id);
            if ($participant) {
                $this->recalculateParticipantStatus($participant);
            }

            AuditLog::create([
                'user_id' => $userId,
                'action' => 'VOID_EVENT_PAYMENT',
                'auditable_type' => EventPayment::class,
                'auditable_id' => $payment->id,
                'old_values' => ['status' => 'active'],
                'new_values' => ['status' => 'void'],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });
    }

    public function storeIncome(Event $event, array $data, int $userId): EventIncome
    {
        return DB::transaction(function () use ($event, $data, $userId) {
            $income = EventIncome::create([
                'event_id' => $event->id,
                'income_type' => $data['income_type'],
                'description' => $data['description'],
                'amount' => $data['amount'],
                'income_date' => $data['income_date'],
                'source' => $data['source'] ?? null,
                'reference_number' => $data['reference_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'recorded_by' => $userId,
                'status' => 'active',
            ]);

            AuditLog::create([
                'user_id' => $userId,
                'action' => 'CREATE_EVENT_INCOME',
                'auditable_type' => EventIncome::class,
                'auditable_id' => $income->id,
                'new_values' => [
                    'event_id' => $event->id,
                    'income_type' => $income->income_type,
                    'amount' => $income->amount,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return $income;
        });
    }

    public function voidIncome(Event $event, EventIncome $income, int $userId): void
    {
        DB::transaction(function () use ($event, $income, $userId) {
            if ($income->linked_payment_id) {
                throw new Exception('Pemasukan ini otomatis dari pembayaran; batalkan pembayarannya, bukan income ini.');
            }

            if ($income->status === 'void') {
                throw new Exception('Pemasukan ini sudah dibatalkan.');
            }

            $income->update(['status' => 'void']);

            AuditLog::create([
                'user_id' => $userId,
                'action' => 'VOID_EVENT_INCOME',
                'auditable_type' => EventIncome::class,
                'auditable_id' => $income->id,
                'old_values' => ['status' => 'active'],
                'new_values' => ['status' => 'void'],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });
    }

    public function storeExpense(Event $event, array $data, int $userId): EventExpense
    {
        return DB::transaction(function () use ($event, $data, $userId) {
            $expense = EventExpense::create([
                ...$data,
                'event_id' => $event->id,
                'recorded_by' => $userId,
                'status' => 'active',
            ]);

            AuditLog::create([
                'user_id' => $userId,
                'action' => 'CREATE_EVENT_EXPENSE',
                'auditable_type' => EventExpense::class,
                'auditable_id' => $expense->id,
                'new_values' => [
                    'event_id' => $expense->event_id,
                    'amount' => $expense->amount,
                    'description' => $expense->description,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return $expense;
        });
    }

    public function voidExpense(Event $event, EventExpense $expense, int $userId): void
    {
        DB::transaction(function () use ($event, $expense, $userId) {
            $expense->update(['status' => 'void']);

            AuditLog::create([
                'user_id' => $userId,
                'action' => 'VOID_EVENT_EXPENSE',
                'auditable_type' => EventExpense::class,
                'auditable_id' => $expense->id,
                'old_values' => ['status' => 'active'],
                'new_values' => ['status' => 'void'],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });
    }

    public function storeBudget(Event $event, array $data): EventBudget
    {
        return EventBudget::create([
            ...$data,
            'event_id' => $event->id,
        ]);
    }

    public function updateBudget(EventBudget $budget, array $data): void
    {
        $budget->update($data);
    }

    public function removeBudget(EventBudget $budget): void
    {
        $budget->delete();
    }

    private function recalculateParticipantStatus(EventParticipant $participant): void
    {
        $totalPaid = (float) EventPayment::where('event_participant_id', $participant->id)
            ->where('status', 'active')
            ->sum('amount');

        $required = (float) $participant->payment_amount;

        if ($required <= 0) {
            $participant->update(['payment_status' => 'paid']);
        } elseif ($totalPaid <= 0) {
            $participant->update(['payment_status' => 'unpaid']);
        } elseif ($totalPaid >= $required) {
            $participant->update(['payment_status' => 'paid']);
        } else {
            $participant->update(['payment_status' => 'partial']);
        }
    }
}
