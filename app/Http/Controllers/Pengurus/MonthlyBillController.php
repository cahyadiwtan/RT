<?php

namespace App\Http\Controllers\Pengurus;

use App\Http\Controllers\Controller;
use App\Models\House;
use App\Models\MonthlyBill;
use App\Services\MonthlyBillingService;
use Illuminate\Http\Request;

class MonthlyBillController extends Controller
{
    protected MonthlyBillingService $billingService;

    public function __construct(MonthlyBillingService $billingService)
    {
        $this->billingService = $billingService;
    }

    public function index(Request $request)
    {
        $query = MonthlyBill::with('house');

        if ($request->filled('billing_period')) {
            $query->where('billing_period', $request->billing_period);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('house_id')) {
            $query->where('house_id', $request->house_id);
        }

        $bills = $query->orderBy('billing_period', 'desc')
            ->orderBy('house_id', 'asc')
            ->paginate(20);

        $houses = House::orderBy('block')->orderBy('house_number')->get();
        $periods = MonthlyBill::select('billing_period')->distinct()->orderBy('billing_period', 'desc')->pluck('billing_period');

        return view('pengurus.bills.index', compact('bills', 'houses', 'periods'));
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'from_period' => ['required', 'string', 'regex:/^\d{4}-\d{2}$/'],
            'to_period' => ['required', 'string', 'regex:/^\d{4}-\d{2}$/'],
        ]);

        $result = $this->billingService->generateMonthlyBillsRange(
            $validated['from_period'],
            $validated['to_period'],
        );

        return redirect()->route('pengurus.bills.index')
            ->with('success', "Generate tagihan periode {$validated['from_period']} s.d. {$result['billing_period']} selesai! Dibuat: {$result['generated_count']}, Skip (sudah ada/belum masuk): {$result['skipped_count']}.");
    }
}
