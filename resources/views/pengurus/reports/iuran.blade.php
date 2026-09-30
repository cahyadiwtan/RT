@extends('layouts.app')

@section('title', 'Laporan Iuran — Pengurus RT')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-display text-warm tracking-tight">Laporan Iuran</h1>
        <p class="text-xs text-warm/50 mt-1">Rekapitulasi tagihan iuran warga per periode</p>
    </div>

    <!-- Tabs -->
    <div class="flex gap-1 bg-surface border border-amber/10 rounded-2xl p-1 w-fit">
        <a href="{{ route('pengurus.reports.iuran') }}" class="px-4 py-2 rounded-xl bg-jade/10 text-jade font-semibold text-sm">Iuran</a>
        <a href="{{ route('pengurus.reports.payments') }}" class="px-4 py-2 rounded-xl text-warm/50 hover:text-warm hover:bg-warm/5 text-sm font-medium transition">Pembayaran</a>
        <a href="{{ route('pengurus.reports.arrears') }}" class="px-4 py-2 rounded-xl text-warm/50 hover:text-warm hover:bg-warm/5 text-sm font-medium transition">Tunggakan</a>
        <a href="{{ route('pengurus.reports.deposits') }}" class="px-4 py-2 rounded-xl text-warm/50 hover:text-warm hover:bg-warm/5 text-sm font-medium transition">Deposit</a>
        <a href="{{ route('pengurus.reports.cashflow') }}" class="px-4 py-2 rounded-xl text-warm/50 hover:text-warm hover:bg-warm/5 text-sm font-medium transition">Arus Kas</a>
        <a href="{{ route('pengurus.reports.assets') }}" class="px-4 py-2 rounded-xl text-warm/50 hover:text-warm hover:bg-warm/5 text-sm font-medium transition">Inventaris</a>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-surface border border-amber/10 rounded-2xl p-5">
            <div class="text-xs text-warm/50 font-semibold uppercase tracking-wider">Total Tagihan</div>
            <div class="text-2xl font-bold text-warm mt-1">Rp{{ number_format($summary['total_bills'], 0, ',', '.') }}</div>
        </div>
        <div class="bg-surface border border-amber/10 rounded-2xl p-5">
            <div class="text-xs text-warm/50 font-semibold uppercase tracking-wider">Total Dibayar</div>
            <div class="text-2xl font-bold text-jade mt-1">Rp{{ number_format($summary['total_paid'], 0, ',', '.') }}</div>
        </div>
        <div class="bg-surface border border-amber/10 rounded-2xl p-5">
            <div class="text-xs text-warm/50 font-semibold uppercase tracking-wider">Total Tunggakan</div>
            <div class="text-2xl font-bold text-clay mt-1">Rp{{ number_format($summary['total_arrears'], 0, ',', '.') }}</div>
        </div>
        <div class="bg-surface border border-amber/10 rounded-2xl p-5">
            <div class="text-xs text-warm/50 font-semibold uppercase tracking-wider">Jumlah Tagihan</div>
            <div class="text-2xl font-bold text-warm mt-1">{{ $summary['count'] }}</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-surface border border-amber/10 rounded-2xl p-4">
        <form action="{{ route('pengurus.reports.iuran') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
            <select name="billing_period" class="w-full sm:w-48 px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm">
                <option value="">Semua Periode</option>
                @foreach($periods as $p)
                    <option value="{{ $p }}" {{ request('billing_period') === $p ? 'selected' : '' }}>{{ $p }}</option>
                @endforeach
            </select>

            <select name="block" class="w-full sm:w-44 px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm">
                <option value="">Semua Blok</option>
                @foreach($blocks as $b)
                    <option value="{{ $b }}" {{ request('block') === $b ? 'selected' : '' }}>{{ $b }}</option>
                @endforeach
            </select>

            <select name="status" class="w-full sm:w-44 px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm">
                <option value="">Semua Status</option>
                <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Partial</option>
                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid (Lunas)</option>
            </select>

            <button type="submit" class="px-4 py-2 rounded-xl bg-warm/5 hover:bg-warm/10 text-warm text-sm font-semibold transition">Filter</button>

            <a href="{{ route('pengurus.reports.export', 'iuran') }}?{{ http_build_query(request()->only('billing_period', 'block', 'status')) }}" class="px-4 py-2 rounded-xl bg-jade/10 border border-jade/30 text-jade hover:bg-jade/20 text-sm font-semibold transition inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export CSV
            </a>
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
                        <th class="px-6 py-4">Tagihan</th>
                        <th class="px-6 py-4">Dibayar</th>
                        <th class="px-6 py-4">Sisa</th>
                        <th class="px-6 py-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-amber/5">
                    @forelse($bills as $bill)
                        <tr class="hover:bg-warm/5 transition">
                            <td class="px-6 py-4 font-mono font-bold text-jade">{{ $bill->billing_period }}</td>
                            <td class="px-6 py-4 font-semibold text-warm">{{ $bill->house?->full_address ?? '-' }}</td>
                            <td class="px-6 py-4">Rp{{ number_format($bill->amount, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-jade">Rp{{ number_format($bill->paid_amount, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 font-semibold text-clay">Rp{{ number_format($bill->remaining_amount, 0, ',', '.') }}</td>
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
                            <td colspan="6" class="px-6 py-8 text-center text-warm/30">Tidak ada data tagihan ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($bills->hasPages())
            <div class="px-6 py-4 border-t border-amber/10">{{ $bills->links() }}</div>
        @endif
    </div>
</div>
@endsection
