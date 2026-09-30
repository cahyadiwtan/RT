<?php

namespace App\Http\Controllers\Pengurus;

use App\Http\Controllers\Controller;
use App\Models\House;
use App\Models\Payment;
use App\Services\PaymentService;
use Exception;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function index(Request $request)
    {
        $query = Payment::with(['house', 'resident', 'recorder', 'allocations.bill']);

        if ($request->filled('house_id')) {
            $query->where('house_id', $request->house_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('house', function ($q) use ($search) {
                $q->where('block', 'like', "%{$search}%")
                    ->orWhere('house_number', 'like', "%{$search}%");
            });
        }

        $payments = $query->orderBy('payment_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15);

        $houses = House::orderBy('block')->orderBy('house_number')->get();

        return view('pengurus.payments.index', compact('payments', 'houses'));
    }

    public function create()
    {
        $houses = House::with('residents')->orderBy('block')->orderBy('house_number')->get();

        return view('pengurus.payments.create', compact('houses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'house_id' => ['required', 'exists:houses,id'],
            'resident_id' => ['nullable', 'exists:residents,id'],
            'payment_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:1000'],
            'payment_method' => ['required', 'in:cash,transfer,other'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $this->paymentService->recordPayment($validated, $request->user()->id);

        return redirect()->route('pengurus.payments.index')
            ->with('success', 'Pembayaran berhasil dicatat dan dialokasikan (FIFO).');
    }

    public function void(Request $request, Payment $payment)
    {
        try {
            $this->paymentService->voidPayment($payment->id, $request->user()->id);

            return back()->with('success', 'Pembayaran berhasil dibatalkan (void) dan alokasi dikembalikan.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
