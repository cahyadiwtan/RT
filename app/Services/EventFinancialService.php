<?php

namespace App\Services;

use App\Models\Event;

class EventFinancialService
{
    public function getTotalIncome(Event $event): float
    {
        return (float) $event->eventPayments()->where('status', 'active')->sum('amount')
            + (float) $event->incomes()->where('status', 'active')->whereNull('linked_payment_id')->sum('amount');
    }

    public function getTotalExpense(Event $event): float
    {
        return (float) $event->expenses()->where('status', 'active')->sum('amount');
    }

    public function getBalance(Event $event): float
    {
        return $this->getTotalIncome($event) - $this->getTotalExpense($event);
    }

    public function getTargetProgress(Event $event): float
    {
        if (!$event->target_amount || (float) $event->target_amount <= 0) {
            return 0;
        }

        return min(100, round(($this->getTotalIncome($event) / (float) $event->target_amount) * 100, 1));
    }

    public function getPaymentSummary(Event $event): array
    {
        $participants = $event->participants();
        $total = $participants->count();
        $paid = (clone $participants)->where('payment_status', 'paid')->count();
        $partial = (clone $participants)->where('payment_status', 'partial')->count();
        $unpaid = (clone $participants)->where('payment_status', 'unpaid')->count();
        $waived = (clone $participants)->where('payment_status', 'waived')->count();

        return compact('total', 'paid', 'partial', 'unpaid', 'waived');
    }

    public function getBudgetSummary(Event $event): array
    {
        $budgets = $event->budgets()->with('category')->get();
        $estimated = (float) $budgets->sum('estimated_amount');
        $actual = (float) $budgets->sum('actual_amount');

        return compact('estimated', 'actual', 'budgets');
    }

    public function getEventFinancialSummary(Event $event): array
    {
        return [
            'total_income' => $this->getTotalIncome($event),
            'total_expense' => $this->getTotalExpense($event),
            'balance' => $this->getBalance($event),
            'target_progress' => $this->getTargetProgress($event),
            'payment_summary' => $this->getPaymentSummary($event),
            'budget_summary' => $this->getBudgetSummary($event),
        ];
    }
}
