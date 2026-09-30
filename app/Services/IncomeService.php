<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Income;
use App\Models\IncomeCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class IncomeService
{
    public function createIncome(array $data, int $userId): Income
    {
        return DB::transaction(function () use ($data, $userId) {
            $income = Income::create([
                ...$data,
                'recorded_by' => $userId,
                'status' => 'active',
            ]);

            AuditLog::create([
                'user_id' => $userId,
                'action' => 'CREATE_INCOME',
                'auditable_type' => Income::class,
                'auditable_id' => $income->id,
                'new_values' => [
                    'description' => $income->description,
                    'amount' => $income->amount,
                    'income_date' => $income->income_date->toDateString(),
                    'category_id' => $income->category_id,
                ],
                'created_at' => now(),
            ]);

            return $income;
        });
    }

    public function updateIncome(Income $income, array $data, int $userId): Income
    {
        return DB::transaction(function () use ($income, $data, $userId) {
            $old = $income->only(['category_id', 'description', 'amount', 'income_date', 'source_name', 'reference_number', 'notes']);

            $income->update($data);

            AuditLog::create([
                'user_id' => $userId,
                'action' => 'UPDATE_INCOME',
                'auditable_type' => Income::class,
                'auditable_id' => $income->id,
                'old_values' => $old,
                'new_values' => $income->only(['category_id', 'description', 'amount', 'income_date', 'source_name', 'reference_number', 'notes']),
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'created_at' => now(),
            ]);

            return $income;
        });
    }

    public function voidIncome(Income $income, int $userId): void
    {
        DB::transaction(function () use ($income, $userId) {
            if ($income->status === 'void') {
                return;
            }

            $income->update(['status' => 'void']);

            AuditLog::create([
                'user_id' => $userId,
                'action' => 'VOID_INCOME',
                'auditable_type' => Income::class,
                'auditable_id' => $income->id,
                'old_values' => ['status' => 'active'],
                'new_values' => ['status' => 'void'],
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'created_at' => now(),
            ]);
        });
    }

    public function createCategory(array $data): IncomeCategory
    {
        return DB::transaction(function () use ($data) {
            return IncomeCategory::create($data);
        });
    }

    public function updateCategory(IncomeCategory $category, array $data): IncomeCategory
    {
        $category->update($data);

        return $category;
    }

    public function destroyCategory(IncomeCategory $category): void
    {
        $category->delete();
    }

    public function activeCategories()
    {
        return IncomeCategory::where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function allCategories()
    {
        return IncomeCategory::withCount('incomes')->orderBy('name')->get();
    }

    public function paginateIncomes(?string $search, ?string $categoryId, ?string $dateFrom, ?string $dateTo)
    {
        return Income::with(['category', 'recorder'])
            ->where('status', 'active')
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('source_name', 'like', "%{$search}%")
                    ->orWhere('reference_number', 'like', "%{$search}%");
            }))
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->when($dateFrom, fn ($q) => $q->whereDate('income_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('income_date', '<=', $dateTo))
            ->orderByDesc('income_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();
    }

    public function getSummary(?string $categoryId, ?string $dateFrom, ?string $dateTo): array
    {
        $base = Income::where('status', 'active')
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->when($dateFrom, fn ($q) => $q->whereDate('income_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('income_date', '<=', $dateTo));

        $total = (float) $base->sum('amount');
        $count = (int) (clone $base)->count();

        $categoryTotals = (clone $base)
            ->select('category_id', DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(*) as total_count'))
            ->groupBy('category_id')
            ->get();

        $categoryNames = IncomeCategory::pluck('name', 'id');

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
