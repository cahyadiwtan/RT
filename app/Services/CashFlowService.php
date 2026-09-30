<?php

namespace App\Services;

use App\Models\EventExpense;
use App\Models\EventIncome;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Payment;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class CashFlowService
{
    public function getSummary(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $incomeQuery = Payment::where('status', 'active');
        $incomeFromEvent = EventIncome::where('status', 'active')->whereNull('linked_payment_id');
        $incomeFromKas = Income::where('status', 'active');
        $expenseQuery = Expense::where('status', 'active');
        $expenseFromEvent = EventExpense::where('status', 'active');

        if ($dateFrom) {
            $incomeQuery->whereDate('payment_date', '>=', $dateFrom);
            $incomeFromEvent->whereDate('income_date', '>=', $dateFrom);
            $incomeFromKas->whereDate('income_date', '>=', $dateFrom);
            $expenseQuery->whereDate('expense_date', '>=', $dateFrom);
            $expenseFromEvent->whereDate('expense_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $incomeQuery->whereDate('payment_date', '<=', $dateTo);
            $incomeFromEvent->whereDate('income_date', '<=', $dateTo);
            $incomeFromKas->whereDate('income_date', '<=', $dateTo);
            $expenseQuery->whereDate('expense_date', '<=', $dateTo);
            $expenseFromEvent->whereDate('expense_date', '<=', $dateTo);
        }

        $totalIncome = (float) $incomeQuery->sum('amount')
            + (float) $incomeFromEvent->sum('amount')
            + (float) $incomeFromKas->sum('amount');
        $totalExpense = (float) $expenseQuery->sum('amount')
            + (float) $expenseFromEvent->sum('amount');

        return [
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'balance' => $totalIncome - $totalExpense,
            'income_count' => (clone $incomeQuery)->count() + (clone $incomeFromEvent)->count() + (clone $incomeFromKas)->count(),
            'expense_count' => (clone $expenseQuery)->count() + (clone $expenseFromEvent)->count(),
        ];
    }

    public function getMonthly(?string $dateFrom = null, ?string $dateTo = null): Collection
    {
        $from = $dateFrom ? Carbon::parse($dateFrom)->startOfMonth() : Carbon::now()->subMonths(11)->startOfMonth();
        $to = $dateTo ? Carbon::parse($dateTo)->endOfMonth() : Carbon::now()->endOfMonth();

        $payments = Payment::where('status', 'active')
            ->whereBetween('payment_date', [$from, $to])
            ->get(['id', 'payment_date', 'amount'])
            ->groupBy(fn ($p) => $p->payment_date->format('Y-m'))
            ->map(fn ($group) => (float) $group->sum('amount'));

        $eventIncomes = EventIncome::where('status', 'active')
            ->whereNull('linked_payment_id')
            ->whereBetween('income_date', [$from, $to])
            ->get(['id', 'income_date', 'amount'])
            ->groupBy(fn ($i) => $i->income_date->format('Y-m'))
            ->map(fn ($group) => (float) $group->sum('amount'));

        $kasIncomes = Income::where('status', 'active')
            ->whereBetween('income_date', [$from, $to])
            ->get(['id', 'income_date', 'amount'])
            ->groupBy(fn ($i) => $i->income_date->format('Y-m'))
            ->map(fn ($group) => (float) $group->sum('amount'));

        $expenses = Expense::where('status', 'active')
            ->whereBetween('expense_date', [$from, $to])
            ->get(['id', 'expense_date', 'amount'])
            ->groupBy(fn ($e) => $e->expense_date->format('Y-m'))
            ->map(fn ($group) => (float) $group->sum('amount'));

        $eventExpenses = EventExpense::where('status', 'active')
            ->whereBetween('expense_date', [$from, $to])
            ->get(['id', 'expense_date', 'amount'])
            ->groupBy(fn ($e) => $e->expense_date->format('Y-m'))
            ->map(fn ($group) => (float) $group->sum('amount'));

        $months = collect();
        $current = $from->copy();
        while ($current->lte($to)) {
            $key = $current->format('Y-m');
            $income = (float) ($payments[$key] ?? 0) + (float) ($eventIncomes[$key] ?? 0) + (float) ($kasIncomes[$key] ?? 0);
            $expense = (float) ($expenses[$key] ?? 0) + (float) ($eventExpenses[$key] ?? 0);

            $months->push([
                'month' => $key,
                'label' => $current->translatedFormat('M Y'),
                'income' => $income,
                'expense' => $expense,
                'balance' => $income - $expense,
            ]);

            $current->addMonth();
        }

        return $months;
    }

    public function getTransactions(?string $dateFrom = null, ?string $dateTo = null, ?string $type = null): LengthAwarePaginator
    {
        $transactions = collect();

        // Catatan: setiap sumber dinormalisasi ke base Collection (collect(...)) karena
        // EloquentCollection::merge() memanggil getKey() pada item, sedangkan item di sini
        // berupa array. concat() dipakai (bukan merge()) agar key numerik antar sumber
        // tidak saling menimpa.
        $payments = collect(Payment::with(['house', 'recorder'])->where('status', 'active')
            ->when($dateFrom, fn ($q) => $q->whereDate('payment_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('payment_date', '<=', $dateTo))
            ->get()
            ->map(fn ($p) => [
                'id' => "payment-{$p->id}",
                'date' => $p->payment_date,
                'description' => 'Iuran '.($p->house->full_address ?? '-'),
                'amount' => (float) $p->amount,
                'type' => 'income',
                'source' => 'Iuran Warga',
                'recorded_by' => $p->recorder?->username ?? '-',
            ])->all());

        $eventIncomes = collect(EventIncome::with(['event', 'recorder'])->where('status', 'active')
            ->whereNull('linked_payment_id')
            ->when($dateFrom, fn ($q) => $q->whereDate('income_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('income_date', '<=', $dateTo))
            ->get()
            ->map(fn ($i) => [
                'id' => "event-income-{$i->id}",
                'date' => $i->income_date,
                'description' => 'Event: '.($i->event->title ?? '-'),
                'amount' => (float) $i->amount,
                'type' => 'income',
                'source' => 'Pemasukan Event',
                'recorded_by' => $i->recorder?->username ?? '-',
            ])->all());

        $kasIncomes = collect(Income::with(['category', 'recorder'])->where('status', 'active')
            ->when($dateFrom, fn ($q) => $q->whereDate('income_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('income_date', '<=', $dateTo))
            ->get()
            ->map(fn ($i) => [
                'id' => "income-{$i->id}",
                'date' => $i->income_date,
                'description' => $i->description,
                'amount' => (float) $i->amount,
                'type' => 'income',
                'source' => $i->category->name ?? 'Pemasukan Kas',
                'recorded_by' => $i->recorder?->username ?? '-',
            ])->all());

        $expenses = collect(Expense::with(['category', 'recorder'])->where('status', 'active')
            ->when($dateFrom, fn ($q) => $q->whereDate('expense_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('expense_date', '<=', $dateTo))
            ->get()
            ->map(fn ($e) => [
                'id' => "expense-{$e->id}",
                'date' => $e->expense_date,
                'description' => $e->description,
                'amount' => (float) $e->amount,
                'type' => 'expense',
                'source' => $e->category->name ?? 'Pengeluaran',
                'recorded_by' => $e->recorder?->username ?? '-',
            ])->all());

        $eventExpenses = collect(EventExpense::with(['event', 'category', 'recorder'])->where('status', 'active')
            ->when($dateFrom, fn ($q) => $q->whereDate('expense_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('expense_date', '<=', $dateTo))
            ->get()
            ->map(fn ($e) => [
                'id' => "event-expense-{$e->id}",
                'date' => $e->expense_date,
                'description' => 'Event: '.($e->event->title ?? '-'),
                'amount' => (float) $e->amount,
                'type' => 'expense',
                'source' => 'Pengeluaran Event',
                'recorded_by' => $e->recorder?->username ?? '-',
            ])->all());

        $transactions = $payments
            ->concat($eventIncomes)
            ->concat($kasIncomes)
            ->concat($expenses)
            ->concat($eventExpenses);

        if ($type === 'income') {
            $transactions = $transactions->where('type', 'income');
        } elseif ($type === 'expense') {
            $transactions = $transactions->where('type', 'expense');
        }

        $transactions = $transactions->sortByDesc('date')->values();

        $perPage = 15;
        $currentPage = request()->get('page', 1);
        $paginated = new LengthAwarePaginator(
            $transactions->slice(($currentPage - 1) * $perPage, $perPage),
            $transactions->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return $paginated;
    }
}
