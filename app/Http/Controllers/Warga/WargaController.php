<?php

namespace App\Http\Controllers\Warga;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\MonthlyBill;
use App\Models\Payment;
use App\Services\FinancialSummaryService;
use App\Services\CashFlowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class WargaController extends Controller
{
    protected FinancialSummaryService $financialService;
    protected CashFlowService $cashFlowService;

    public function __construct(FinancialSummaryService $financialService, CashFlowService $cashFlowService)
    {
        $this->financialService = $financialService;
        $this->cashFlowService = $cashFlowService;
    }

    public function iuran()
    {
        $user = Auth::user();
        $resident = $user->resident;
        $house = $resident ? $resident->house : null;

        $bills = collect();
        $financials = null;

        if ($house) {
            $bills = MonthlyBill::where('house_id', $house->id)
                ->orderBy('billing_period', 'desc')
                ->get();
            $financials = $this->financialService->getHouseFinancialSummary($house->id);
        }

        return view('warga.iuran', compact('user', 'resident', 'house', 'bills', 'financials'));
    }

    public function riwayatPembayaran()
    {
        $user = Auth::user();
        $resident = $user->resident;
        $house = $resident ? $resident->house : null;

        $payments = collect();
        if ($house) {
            $payments = Payment::with('allocations.bill')
                ->where('house_id', $house->id)
                ->orderBy('payment_date', 'desc')
                ->paginate(15);
        }

        return view('warga.riwayat-pembayaran', compact('user', 'resident', 'house', 'payments'));
    }

    public function cashflow(Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $type = $request->input('type');

        $summary = $this->cashFlowService->getSummary($dateFrom, $dateTo);
        $monthly = $this->cashFlowService->getMonthly($dateFrom, $dateTo);
        $transactions = $this->cashFlowService->getTransactions($dateFrom, $dateTo, $type);

        return view('warga.cashflow', compact('summary', 'monthly', 'transactions'));
    }

    public function assets()
    {
        $assets = Asset::with('category')
            ->where('status', 'tersedia')
            ->orderBy('name')
            ->paginate(20);

        return view('warga.assets', compact('assets'));
    }

    public function showChangePasswordForm()
    {
        return view('warga.change-password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = Auth::user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini yang Anda masukkan salah.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password Anda berhasil diperbarui!');
    }
}
