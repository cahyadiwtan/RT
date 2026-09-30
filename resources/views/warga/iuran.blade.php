@extends('layouts.app')

@section('title', 'Status Iuran Saya — Sistem RT')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-display text-warm tracking-tight">Status Iuran Bulanan Saya</h1>
        <p class="text-xs text-warm/50 mt-1">Status kewajiban iuran rutin rumah {{ $house ? $house->full_address : '-' }}</p>
    </div>

    <!-- Summary Box -->
    @if($financials)
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="bg-surface border border-amber/10 rounded-2xl p-5">
            <span class="text-xs font-semibold text-warm/50 uppercase tracking-wider block mb-1">Total Tagihan Diterbitkan</span>
            <div class="text-2xl font-bold text-warm">Rp{{ number_format($financials['total_bills'], 0, ',', '.') }}</div>
        </div>
        <div class="bg-surface border border-amber/10 rounded-2xl p-5">
            <span class="text-xs font-semibold text-warm/50 uppercase tracking-wider block mb-1">Tunggakan (Belum Lunas)</span>
            <div class="text-2xl font-bold {{ $financials['total_arrears'] > 0 ? 'text-clay' : 'text-jade' }}">
                Rp{{ number_format($financials['total_arrears'], 0, ',', '.') }}
            </div>
        </div>
        <div class="bg-surface border border-amber/10 rounded-2xl p-5">
            <span class="text-xs font-semibold text-warm/50 uppercase tracking-wider block mb-1">Saldo Deposit (Kredit)</span>
            <div class="text-2xl font-bold text-jade">Rp{{ number_format($financials['credit_balance'], 0, ',', '.') }}</div>
        </div>
    </div>
    @endif

    <!-- Bills Table -->
    <div class="bg-surface border border-amber/10 rounded-2xl overflow-hidden shadow-xl">
        <div class="p-4 border-b border-amber/10 font-bold text-warm">Daftar Tagihan Bulanan</div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-warm/80">
                <thead class="table-head text-xs uppercase tracking-wider text-warm/50 border-b border-amber/10">
                    <tr>
                        <th class="px-6 py-4">Periode</th>
                        <th class="px-6 py-4">Nominal Tagihan</th>
                        <th class="px-6 py-4">Telah Dibayar</th>
                        <th class="px-6 py-4">Sisa Tunggakan</th>
                        <th class="px-6 py-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-amber/5">
                    @forelse($bills as $bill)
                        <tr class="hover:bg-warm/5 transition">
                            <td class="px-6 py-4 font-mono font-bold text-jade">
                                {{ $bill->billing_period }}
                            </td>
                            <td class="px-6 py-4">
                                Rp{{ number_format($bill->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-jade">
                                Rp{{ number_format($bill->paid_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 font-semibold text-clay">
                                Rp{{ number_format($bill->remaining_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($bill->status === 'paid')
                                    <span class="px-3 py-1 rounded-full bg-jade/10 border border-jade/30 text-jade text-xs font-semibold">LUNAS</span>
                                @elseif($bill->status === 'partial')
                                    <span class="px-3 py-1 rounded-full bg-amber/10 border border-amber/30 text-amber text-xs font-semibold">PARTIAL</span>
                                @else
                                    <span class="px-3 py-1 rounded-full bg-clay/10 border border-clay/30 text-clay text-xs font-semibold">BELUM BAYAR</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-warm/30">
                                Belum ada tagihan iuran diterbitkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
