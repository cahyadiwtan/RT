<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\House;
use App\Models\MonthlyBill;
use App\Models\Payment;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ReportService
{
    public function getIuranReport(?string $period = null, ?string $block = null, ?string $status = null): LengthAwarePaginator
    {
        $query = MonthlyBill::with('house')
            ->orderBy('billing_period', 'desc')
            ->orderBy('id');

        if ($period) {
            $query->where('billing_period', $period);
        }

        if ($block) {
            $query->whereHas('house', fn ($q) => $q->where('block', $block));
        }

        if ($status) {
            $query->where('status', $status);
        }

        return $query->paginate(20)->withQueryString();
    }

    public function getIuranSummary(?string $period = null, ?string $block = null, ?string $status = null): array
    {
        $query = MonthlyBill::query();

        if ($period) {
            $query->where('billing_period', $period);
        }

        if ($block) {
            $query->whereHas('house', fn ($q) => $q->where('block', $block));
        }

        if ($status) {
            $query->where('status', $status);
        }

        return [
            'total_bills' => (float) $query->clone()->sum('amount'),
            'total_paid' => (float) $query->clone()->sum('paid_amount'),
            'total_arrears' => (float) $query->clone()->sum('remaining_amount'),
            'count' => $query->clone()->count(),
        ];
    }

    public function getIuranRows(?string $period = null, ?string $block = null, ?string $status = null): Collection
    {
        $query = MonthlyBill::with('house')
            ->orderBy('billing_period', 'desc')
            ->orderBy('id');

        if ($period) {
            $query->where('billing_period', $period);
        }

        if ($block) {
            $query->whereHas('house', fn ($q) => $q->where('block', $block));
        }

        if ($status) {
            $query->where('status', $status);
        }

        return $query->get();
    }

    public function getPaymentReport(?string $dateFrom = null, ?string $dateTo = null, ?string $method = null, ?int $houseId = null): LengthAwarePaginator
    {
        $query = Payment::with(['house', 'resident'])
            ->where('status', 'active')
            ->orderBy('payment_date', 'desc')
            ->orderBy('id');

        if ($dateFrom) {
            $query->where('payment_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('payment_date', '<=', $dateTo);
        }

        if ($method) {
            $query->where('payment_method', $method);
        }

        if ($houseId) {
            $query->where('house_id', $houseId);
        }

        return $query->paginate(20)->withQueryString();
    }

    public function getPaymentSummary(?string $dateFrom = null, ?string $dateTo = null, ?string $method = null, ?int $houseId = null): array
    {
        $query = Payment::where('status', 'active');

        if ($dateFrom) {
            $query->where('payment_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('payment_date', '<=', $dateTo);
        }

        if ($method) {
            $query->where('payment_method', $method);
        }

        if ($houseId) {
            $query->where('house_id', $houseId);
        }

        return [
            'total_amount' => (float) $query->clone()->sum('amount'),
            'count' => $query->clone()->count(),
        ];
    }

    public function getPaymentRows(?string $dateFrom = null, ?string $dateTo = null, ?string $method = null, ?int $houseId = null): Collection
    {
        $query = Payment::with(['house', 'resident'])
            ->where('status', 'active')
            ->orderBy('payment_date', 'desc')
            ->orderBy('id');

        if ($dateFrom) {
            $query->where('payment_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('payment_date', '<=', $dateTo);
        }

        if ($method) {
            $query->where('payment_method', $method);
        }

        if ($houseId) {
            $query->where('house_id', $houseId);
        }

        return $query->get();
    }

    public function getArrearsReport(): LengthAwarePaginator
    {
        return MonthlyBill::with(['house.residents'])
            ->whereIn('status', ['unpaid', 'partial'])
            ->orderBy('remaining_amount', 'desc')
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();
    }

    public function getArrearsSummary(): array
    {
        $query = MonthlyBill::whereIn('status', ['unpaid', 'partial']);

        return [
            'total_arrears' => (float) $query->clone()->sum('remaining_amount'),
            'count' => $query->clone()->count(),
        ];
    }

    public function getArrearsRows(): Collection
    {
        return MonthlyBill::with(['house.residents'])
            ->whereIn('status', ['unpaid', 'partial'])
            ->orderBy('remaining_amount', 'desc')
            ->get();
    }

    public function getDepositRows(): Collection
    {
        $payments = Payment::where('status', 'active')
            ->selectRaw('house_id, SUM(amount) as total')
            ->groupBy('house_id')
            ->pluck('total', 'house_id');

        $allocations = Payment::where('status', 'active')
            ->join('payment_allocations', 'payment_allocations.payment_id', '=', 'payments.id')
            ->selectRaw('payments.house_id, SUM(payment_allocations.amount) as total')
            ->groupBy('payments.house_id')
            ->pluck('total', 'payments.house_id');

        $rows = collect();
        foreach ($payments->keys()->merge($allocations->keys())->unique() as $houseId) {
            $credit = (float) ($payments[$houseId] ?? 0) - (float) ($allocations[$houseId] ?? 0);
            if ($credit > 0) {
                $rows->push(collect([
                    'house' => House::find($houseId),
                    'credit_balance' => $credit,
                    'last_payment_date' => Payment::where('house_id', $houseId)
                        ->where('status', 'active')
                        ->max('payment_date'),
                ]));
            }
        }

        return $rows->sortByDesc('credit_balance')->values();
    }

    public function getDepositReport(): LengthAwarePaginator
    {
        $rows = $this->getDepositRows();
        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 20;
        $items = $rows->forPage($page, $perPage);

        return new LengthAwarePaginator(
            $items,
            $rows->count(),
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );
    }

    public function getDepositSummary(): array
    {
        $rows = $this->getDepositRows();

        return [
            'total_deposit' => (float) $rows->sum('credit_balance'),
            'count' => $rows->count(),
        ];
    }

    public function getAvailablePeriods(): Collection
    {
        return MonthlyBill::select('billing_period')
            ->distinct()
            ->orderBy('billing_period', 'desc')
            ->pluck('billing_period');
    }

    public function getAvailableBlocks(): Collection
    {
        return House::select('block')
            ->distinct()
            ->orderBy('block')
            ->pluck('block');
    }

    public function getAssetReport(): LengthAwarePaginator
    {
        return Asset::with('category')
            ->orderBy('asset_code')
            ->paginate(20)
            ->withQueryString();
    }

    public function getAssetSummary(): array
    {
        return [
            'total_assets' => Asset::count(),
            'total_value' => (float) Asset::sum('current_value'),
            'total_borrowed' => Asset::where('status', 'dipinjam')->count(),
            'total_damaged' => Asset::whereIn('condition', ['rusak_ringan', 'rusak_berat', 'tidak_layak'])->count(),
        ];
    }

    public function getAssetRows(): Collection
    {
        return Asset::with('category')
            ->orderBy('asset_code')
            ->get();
    }
}
