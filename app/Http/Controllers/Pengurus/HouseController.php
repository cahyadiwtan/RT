<?php

namespace App\Http\Controllers\Pengurus;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHouseRequest;
use App\Http\Requests\UpdateHouseRequest;
use App\Models\AuditLog;
use App\Models\House;
use App\Models\MonthlyBill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HouseController extends Controller
{
    public function index(Request $request)
    {
        $query = House::withCount('residents');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('block', 'like', "%{$search}%")
                    ->orWhere('house_number', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $houses = $query->orderBy('block', 'asc')
            ->orderBy('house_number', 'asc')
            ->paginate(15);

        $arrearsCountByHouse = MonthlyBill::whereIn('status', ['unpaid', 'partial'])
            ->select('house_id', DB::raw('COUNT(*) as total'))
            ->groupBy('house_id')
            ->pluck('total', 'house_id')
            ->toArray();

        $arrearsDetailsByHouse = MonthlyBill::whereIn('status', ['unpaid', 'partial'])
            ->select('house_id', 'billing_period', 'amount', 'paid_amount', 'remaining_amount', 'status')
            ->orderBy('billing_period', 'desc')
            ->get()
            ->groupBy('house_id');

        return view('pengurus.houses.index', compact('houses', 'arrearsCountByHouse', 'arrearsDetailsByHouse'));
    }

    public function create()
    {
        return view('pengurus.houses.create');
    }

    public function store(StoreHouseRequest $request)
    {
        House::create($request->validated());

        return redirect()->route('pengurus.houses.index')
            ->with('success', 'Data rumah berhasil ditambahkan.');
    }

    public function edit(House $house)
    {
        return view('pengurus.houses.edit', compact('house'));
    }

    public function update(UpdateHouseRequest $request, House $house)
    {
        $house->update($request->validated());

        return redirect()->route('pengurus.houses.index')
            ->with('success', 'Data rumah berhasil diperbarui.');
    }

    public function destroy(House $house)
    {
        if ($house->residents()->count() > 0) {
            return back()->withErrors(['error' => 'Tidak dapat menghapus rumah yang masih memiliki penghuni warga terdaftar.']);
        }

        $house->delete();

        return redirect()->route('pengurus.houses.index')
            ->with('success', 'Data rumah berhasil dihapus.');
    }

    public function amnesty(House $house, Request $request)
    {
        $request->validate([
            'amnesty_reason' => 'required|string|max:255',
        ]);

        $bills = MonthlyBill::where('house_id', $house->id)
            ->whereIn('status', ['unpaid', 'partial'])
            ->lockForUpdate()
            ->get();

        if ($bills->isEmpty()) {
            return back()->withErrors(['error' => 'Tidak ada tunggakan untuk rumah ini.']);
        }

        DB::transaction(function () use ($bills, $house, $request) {
            $oldValues = [];

            foreach ($bills as $bill) {
                $oldValues[] = [
                    'billing_period' => $bill->billing_period,
                    'amount' => (float) $bill->amount,
                    'paid_amount' => (float) $bill->paid_amount,
                    'remaining_amount' => (float) $bill->remaining_amount,
                    'status' => $bill->status,
                ];

                $bill->update([
                    'paid_amount' => $bill->amount,
                    'remaining_amount' => 0,
                    'status' => 'paid',
                    'amnesty_reason' => $request->amnesty_reason,
                ]);
            }

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'AMNESTY_ARREARS',
                'auditable_type' => House::class,
                'auditable_id' => $house->id,
                'old_values' => $oldValues,
                'new_values' => [
                    'amnesty_reason' => $request->amnesty_reason,
                    'total_amnestied' => $bills->count(),
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        return redirect()->route('pengurus.houses.index')
            ->with('success', "Berhasil memutihkan {$bills->count()} periode tunggakan untuk {$house->full_address}.");
    }
}
