@extends('layouts.app')

@section('title', 'Riwayat Pembayaran — Sistem RT')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-display text-warm tracking-tight">Riwayat Pembayaran Saya</h1>
        <p class="text-xs text-warm/50 mt-1">Daftar transaksi pembayaran iuran yang telah tercatat oleh Pengurus RT</p>
    </div>

    <div class="bg-surface border border-amber/10 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-warm/80">
                <thead class="table-head text-xs uppercase tracking-wider text-warm/50 border-b border-amber/10">
                    <tr>
                        <th class="px-6 py-4">Tgl Pembayaran</th>
                        <th class="px-6 py-4">Metode</th>
                        <th class="px-6 py-4">Nominal Disetor</th>
                        <th class="px-6 py-4">Dialokasikan Ke Periode</th>
                        <th class="px-6 py-4 text-center">Status Transaksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-amber/5">
                    @forelse($payments as $payment)
                        <tr class="hover:bg-warm/5 transition {{ $payment->status === 'void' ? 'opacity-50' : '' }}">
                            <td class="px-6 py-4 font-mono text-xs text-warm/50">
                                {{ $payment->payment_date->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 text-xs uppercase font-semibold text-warm/70">
                                {{ $payment->payment_method }}
                            </td>
                            <td class="px-6 py-4 font-bold text-jade">
                                Rp{{ number_format($payment->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-xs text-warm/80">
                                @forelse($payment->allocations as $alloc)
                                    <span class="inline-block bg-ink px-2.5 py-1 rounded border border-amber/10 mr-1 mb-1">
                                        Periode {{ $alloc->bill ? $alloc->bill->billing_period : '-' }}: Rp{{ number_format($alloc->amount, 0, ',', '.') }}
                                    </span>
                                @empty
                                    <span class="text-amber font-semibold">Saldo Kredit (Deposit)</span>
                                @endforelse
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($payment->status === 'active')
                                    <span class="px-2.5 py-1 rounded-full bg-jade/10 border border-jade/30 text-jade text-xs font-semibold">Berhasil</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full bg-clay/10 border border-clay/30 text-clay text-xs font-semibold">DIBATALKAN (VOID)</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-warm/30">
                                Belum ada riwayat pembayaran tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments && method_exists($payments, 'hasPages') && $payments->hasPages())
            <div class="px-6 py-4 border-t border-amber/10">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
