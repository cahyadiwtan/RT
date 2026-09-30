<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class ExpenseService
{
    public function createExpense(array $data, int $userId): Expense
    {
        return DB::transaction(function () use ($data, $userId) {
            $expense = Expense::create([
                ...$data,
                'recorded_by' => $userId,
                'status' => 'active',
            ]);

            AuditLog::create([
                'user_id' => $userId,
                'action' => 'CREATE_EXPENSE',
                'auditable_type' => Expense::class,
                'auditable_id' => $expense->id,
                'new_values' => [
                    'description' => $expense->description,
                    'amount' => $expense->amount,
                    'expense_date' => $expense->expense_date->toDateString(),
                    'category_id' => $expense->category_id,
                ],
                'created_at' => now(),
            ]);

            return $expense;
        });
    }

    public function updateExpense(Expense $expense, array $data, int $userId): Expense
    {
        return DB::transaction(function () use ($expense, $data, $userId) {
            $old = $expense->only(['category_id', 'description', 'amount', 'expense_date', 'vendor', 'receipt_number', 'notes']);

            $expense->update($data);

            AuditLog::create([
                'user_id' => $userId,
                'action' => 'UPDATE_EXPENSE',
                'auditable_type' => Expense::class,
                'auditable_id' => $expense->id,
                'old_values' => $old,
                'new_values' => $expense->only(['category_id', 'description', 'amount', 'expense_date', 'vendor', 'receipt_number', 'notes']),
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'created_at' => now(),
            ]);

            return $expense;
        });
    }

    public function voidExpense(Expense $expense, int $userId): void
    {
        DB::transaction(function () use ($expense, $userId) {
            if ($expense->status === 'void') {
                return;
            }

            $expense->update(['status' => 'void']);

            AuditLog::create([
                'user_id' => $userId,
                'action' => 'VOID_EXPENSE',
                'auditable_type' => Expense::class,
                'auditable_id' => $expense->id,
                'old_values' => ['status' => 'active'],
                'new_values' => ['status' => 'void'],
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'created_at' => now(),
            ]);
        });
    }

    public function createCategory(array $data): ExpenseCategory
    {
        return DB::transaction(function () use ($data) {
            return ExpenseCategory::create($data);
        });
    }

    public function updateCategory(ExpenseCategory $category, array $data): ExpenseCategory
    {
        $category->update($data);

        return $category;
    }

    public function destroyCategory(ExpenseCategory $category): void
    {
        $category->delete();
    }

    public function activeCategories()
    {
        return ExpenseCategory::where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function allCategories()
    {
        return ExpenseCategory::withCount('expenses')->orderBy('name')->get();
    }

    public function paginateExpenses(?string $search, ?string $categoryId, ?string $dateFrom, ?string $dateTo)
    {
        return Expense::with(['category', 'recorder'])
            ->where('status', 'active')
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('vendor', 'like', "%{$search}%")
                    ->orWhere('receipt_number', 'like', "%{$search}%");
            }))
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->when($dateFrom, fn ($q) => $q->whereDate('expense_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('expense_date', '<=', $dateTo))
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();
    }

    public function getSummary(?string $categoryId, ?string $dateFrom, ?string $dateTo): array
    {
        $base = Expense::where('status', 'active')
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->when($dateFrom, fn ($q) => $q->whereDate('expense_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('expense_date', '<=', $dateTo));

        $total = (float) $base->sum('amount');
        $count = (int) (clone $base)->count();

        $categoryTotals = (clone $base)
            ->select('category_id', DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(*) as total_count'))
            ->groupBy('category_id')
            ->get();

        $categoryNames = ExpenseCategory::pluck('name', 'id');

        $byCategory = $categoryTotals->map(fn ($row) => [
            'category_id' => $row->category_id,
            'name' => $categoryNames[$row->category_id] ?? 'Tanpa Kategori',
            'total_amount' => (float) $row->total_amount,
            'count' => (int) $row->total_count,
        ])->sortByDesc('total_amount')->values();

        return [
            'total' => $total,
            'count' => $count,
            'by_category' => $byCategory,
        ];
    }
}
