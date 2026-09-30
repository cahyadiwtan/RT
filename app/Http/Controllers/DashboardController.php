<?php

namespace App\Http\Controllers;

use App\Services\FinancialSummaryService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected FinancialSummaryService $financialSummaryService;

    public function __construct(FinancialSummaryService $financialSummaryService)
    {
        $this->financialSummaryService = $financialSummaryService;
    }

    public function index()
    {
        $user = Auth::user();

        if ($user->isPengurus()) {
            $summary = $this->financialSummaryService->getAdminFinancialSummary();

            return view('dashboard.pengurus', compact('summary'));
        }

        $resident = $user->resident;
        $house = $resident ? $resident->house : null;
        $financials = $house ? $this->financialSummaryService->getHouseFinancialSummary($house->id) : null;

        return view('dashboard.warga', compact('user', 'resident', 'house', 'financials'));
    }
}
