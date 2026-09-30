@extends('layouts.app')

@section('title', 'Laporan Pembayaran — Pengurus RT')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-display text-warm tracking-tight">Laporan Pembayaran</h1>
        <p class="text-xs text-warm/50 mt-1">Riwayat penerimaan pembayaran iuran warga</p>
    </div>

    <!-- Tabs -->
    <div class="flex gap-1 bg-surface border border-amber/10 rounded-2xl p-1 w-fit">
        <a href="{{ route('pengurus.reports.iuran') }}" class="px-4 py-2 rounded-xl text-warm/50 hover:text-warm hover:bg-warm/5 text-sm font-medium transition">Iuran</a>
        <a href="{{ route('pengurus.reports.payments') }}" class="px-4 py-2 rounded-xl bg-jade/10 text-jade font-semibold text-sm">Pembayaran</a>
        <a href="{{ route('pengurus.reports.arrears') }}" class="px-4 py-2 rounded-xl text-warm/50 hover:text-warm hover:bg-warm/5 text-sm font-medium transition">Tunggakan</a>
        <a href="{{ route('pengurus.reports.deposits') }}" class="px-4 py-2 rounded-xl text-warm/50 hover:text-warm hover:bg-warm/5 text-sm font-medium transition">Deposit</a>
        <a href="{{ route('pengurus.reports.cashflow') }}" class="px-4 py-2 rounded-xl text-warm/50 hover:text-warm hover:bg-warm/5 text-sm font-medium transition">Arus Kas</a>
        <a href="{{ route('pengurus.reports.assets') }}" class="px-4 py-2 rounded-xl text-warm/50 hover:text-warm hover:bg-warm/5 text-sm font-medium transition">Inventaris</a>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-surface border border-amber/10 rounded-2xl p-5">
            <div class="text-xs text-warm/50 font-semibold uppercase tracking-wider">Total Pemasukan</div>
            <div class="text-2xl font-bold text-jade mt-1">Rp{{ number_format($summary['total_amount'], 0, ',', '.') }}</div>
        </div>
        <div class="bg-surface border border-amber/10 rounded-2xl p-5">
            <div class="text-xs text-warm/50 font-semibold uppercase tracking-wider">Jumlah Transaksi</div>
            <div class="text-2xl font-bold text-warm mt-1">{{ $summary['count'] }}</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-surface border border-amber/10 rounded-2xl p-4">
        <form action="{{ route('pengurus.reports.payments') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
            <input type="date" name="date_from" value="{{ request('date_from') }}" placeholder="Dari tanggal"
                class="w-full sm:w-44 px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm">

            <input type="date" name="date_to" value="{{ request('date_to') }}" placeholder="Sampai tanggal"
                class="w-full sm:w-44 px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm">

            <select name="method" class="w-full sm:w-44 px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm">
                <option value="">Semua Metode</option>
                <option value="cash" {{ request('method') === 'cash' ? 'selected' : '' }}>Cash</option>
                <option value="transfer" {{ request('method') === 'transfer' ? 'selected' : '' }}>Transfer</option>
                <option value="ewallet" {{ request('method') === 'ewallet' ? 'selected' : '' }}>E-Wallet</option>
            </select>

            <button type="submit" class="px-4 py-2 rounded-xl bg-warm/5 hover:bg-warm/10 text-warm text-sm font-semibold transition">Filter</button>

            <a href="{{ route('pengurus.reports.export', 'pembayaran') }}?{{ http_build_query(request()->only('date_from', 'date_to', 'method')) }}" class="px-4 py-2 rounded-xl bg-jade/10 border border-jade/30 text-jade hover:bg-jade/20 text-sm font-semibold transition inline-flex items-center gap-2">
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
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4">Rumah & Pembayar</th>
                        <th class="px-6 py-4">Metode</th>
                        <th class="px-6 py-4">Nominal</th>
                        <th class="px-6 py-4">Referensi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-amber/5">
                    @forelse($payments as $payment)
                        <tr class="hover:bg-warm/5 transition">
                            <td class="px-6 py-4 text-xs font-mono text-warm/50">{{ $payment->payment_date->format('d/m/Y') }}</td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-warm">{{ $payment->house?->full_address ?? '-' }}</div>
                                <div class="text-xs text-warm/50">{{ $payment->resident?->nama_lengkap ?? 'Warga' }}</div>
                            </td>
                            <td class="px-6 py-4 text-xs uppercase font-semibold text-warm/70">{{ $payment->payment_method }}</td>
                            <td class="px-6 py-4 font-bold text-jade">Rp{{ number_format($payment->amount, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-xs text-warm/50 font-mono">{{ $payment->reference_number ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-warm/30">Tidak ada data pembayaran ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payments->hasPages())
            <div class="px-6 py-4 border-t border-amber/10">{{ $payments->links() }}</div>
        @endif
    </div>
</div>
@endsection
