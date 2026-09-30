<?php

namespace App\Http\Controllers\Pengurus;

use App\Http\Controllers\Controller;
use App\Models\MonthlyFeeSetting;
use Illuminate\Http\Request;

class MonthlyFeeSettingController extends Controller
{
    public function index()
    {
        $settings = MonthlyFeeSetting::orderBy('effective_from', 'desc')->get();

        return view('pengurus.fee-settings.index', compact('settings'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1000'],
            'effective_from' => ['required', 'date'],
            'effective_until' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        MonthlyFeeSetting::create($validated);

        return redirect()->route('pengurus.fee-settings.index')
            ->with('success', 'Pengaturan nominal iuran bulanan berhasil ditambahkan.');
    }
}
