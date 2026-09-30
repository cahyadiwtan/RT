@extends('layouts.app')

@section('title', 'Tagihan Bulanan — Pengurus RT')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-display text-warm tracking-tight">Tagihan Iuran Bulanan</h1>
            <p class="text-xs text-warm/50 mt-1">Generate tagihan berdasarkan range periode bulan/tahun (idempotent; saldo deposit otomatis menutup tagihan baru)</p>
        </div>

        <!-- Generate Bill Form -->
        <form action="{{ route('pengurus.bills.generate') }}" method="POST" class="flex flex-wrap items-center gap-2 bg-surface/90 border border-amber/10 p-2 rounded-2xl">
            @csrf
            <input type="month" name="from_period" value="{{ $periods->first() ?? date('Y-m') }}" required
                class="px-3 py-1.5 rounded-xl bg-ink border border-amber/10 text-warm text-xs font-mono focus:outline-none" title="Dari periode">
            <span class="text-warm/40 text-xs">s.d.</span>
            <input type="month" name="to_period" value="{{ date('Y-m') }}" required
                class="px-3 py-1.5 rounded-xl bg-ink border border-amber/10 text-warm text-xs font-mono focus:outline-none" title="Sampai periode">
            <button type="submit" class="px-4 py-1.5 rounded-xl bg-jade hover:bg-jade/90 text-ink font-bold text-xs transition shadow-md">
                ⚡ Generate Tagihan
            </button>
        </form>
    </div>

    <!-- Filters -->
    <div class="bg-surface border border-amber/10 rounded-2xl p-4">
        <form action="{{ route('pengurus.bills.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
            <select name="billing_period" class="w-full sm:w-48 px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm">
                <option value="">Semua Periode</option>
                @foreach($periods as $p)
                    <option value="{{ $p }}" {{ request('billing_period') === $p ? 'selected' : '' }}>{{ $p }}</option>
                @endforeach
            </select>

            <select name="status" class="w-full sm:w-44 px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm">
                <option value="">Semua Status</option>
                <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Partial</option>
                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid (Lunas)</option>
            </select>

            <select name="house_id" class="w-full sm:w-48 px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm">
                <option value="">Semua Rumah</option>
                @foreach($houses as $h)
                    <option value="{{ $h->id }}" {{ request('house_id') == $h->id ? 'selected' : '' }}>{{ $h->full_address }}</option>
                @endforeach
            </select>

            <button type="submit" class="px-4 py-2 rounded-xl bg-warm/5 hover:bg-warm/10 text-warm text-sm font-semibold transition">
                Filter
            </button>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-surface border border-amber/10 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-warm/80">
                <thead class="table-head text-xs uppercase tracking-wider text-warm/50 border-b border-amber/10">
                    <tr>
                        <th class="px-6 py-4">Periode</th>
                        <th class="px-6 py-4">Rumah</th>
                        <th class="px-6 py-4">Nominal Tagihan</th>
                        <th class="px-6 py-4">Sudah Dibayar</th>
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
                            <td class="px-6 py-4 font-semibold text-warm">
                                {{ $bill->house ? $bill->house->full_address : '-' }}
                            </td>
                            <td class="px-6 py-4">
                                Rp{{ number_format($bill->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-jade/80">
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
                            <td colspan="6" class="px-6 py-8 text-center text-warm/30">
                                Belum ada data tagihan. Klik "Generate Tagihan" untuk membuat tagihan periode bulan ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bills->hasPages())
            <div class="px-6 py-4 border-t border-amber/10">
                {{ $bills->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
