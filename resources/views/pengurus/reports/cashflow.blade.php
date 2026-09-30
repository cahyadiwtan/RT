@extends('layouts.app')

@section('title', 'Laporan Arus Kas — Pengurus RT')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-display text-warm tracking-tight">Laporan Arus Kas</h1>
        <p class="text-xs text-warm/50 mt-1">Penerimaan &amp; pengeluaran kas RT untuk transparansi alur keuangan</p>
    </div>

    <!-- Tabs -->
    <div class="flex flex-wrap gap-1 bg-surface border border-amber/10 rounded-2xl p-1 w-fit">
        <a href="{{ route('pengurus.reports.iuran') }}" class="px-4 py-2 rounded-xl text-warm/50 hover:text-warm hover:bg-warm/5 text-sm font-medium transition">Iuran</a>
        <a href="{{ route('pengurus.reports.payments') }}" class="px-4 py-2 rounded-xl text-warm/50 hover:text-warm hover:bg-warm/5 text-sm font-medium transition">Pembayaran</a>
        <a href="{{ route('pengurus.reports.arrears') }}" class="px-4 py-2 rounded-xl text-warm/50 hover:text-warm hover:bg-warm/5 text-sm font-medium transition">Tunggakan</a>
        <a href="{{ route('pengurus.reports.deposits') }}" class="px-4 py-2 rounded-xl text-warm/50 hover:text-warm hover:bg-warm/5 text-sm font-medium transition">Deposit</a>
        <a href="{{ route('pengurus.reports.cashflow') }}" class="px-4 py-2 rounded-xl bg-jade/10 text-jade font-semibold text-sm">Arus Kas</a>
        <a href="{{ route('pengurus.reports.assets') }}" class="px-4 py-2 rounded-xl text-warm/50 hover:text-warm hover:bg-warm/5 text-sm font-medium transition">Inventaris</a>
    </div>

    <!-- Filter -->
    <div class="bg-surface border border-amber/10 rounded-2xl p-4">
        <form action="{{ route('pengurus.reports.cashflow') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3 flex-wrap">
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm">
            <span class="text-warm/40 text-sm">s.d.</span>
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm">
            <select name="type" class="w-full sm:w-44 px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm">
                <option value="">Semua Transaksi</option>
                <option value="income" {{ request('type') === 'income' ? 'selected' : '' }}>Penerimaan</option>
                <option value="expense" {{ request('type') === 'expense' ? 'selected' : '' }}>Pengeluaran</option>
            </select>
            <button type="submit" class="px-4 py-2 rounded-xl bg-warm/10 hover:bg-warm/15 text-warm text-sm font-semibold transition">Filter</button>
            <a href="{{ route('pengurus.reports.cashflow') }}" class="px-4 py-2 rounded-xl text-warm/50 hover:text-warm text-sm">Reset</a>
        </form>
    </div>

    <!-- Summary -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-surface border border-amber/10 rounded-2xl p-5 shadow-xl">
            <span class="text-xs font-semibold text-warm/50 uppercase tracking-wider block mb-1">Total Penerimaan</span>
            <div class="text-2xl font-display text-jade">Rp{{ number_format($summary['total_income'], 0, ',', '.') }}</div>
            <div class="text-xs text-warm/30 mt-1">{{ $summary['income_count'] }} transaksi</div>
        </div>
        <div class="bg-surface border border-amber/10 rounded-2xl p-5 shadow-xl">
            <span class="text-xs font-semibold text-warm/50 uppercase tracking-wider block mb-1">Total Pengeluaran</span>
            <div class="text-2xl font-display text-clay">Rp{{ number_format($summary['total_expense'], 0, ',', '.') }}</div>
            <div class="text-xs text-warm/30 mt-1">{{ $summary['expense_count'] }} transaksi</div>
        </div>
        <div class="bg-surface border border-amber/10 rounded-2xl p-5 shadow-xl">
            <span class="text-xs font-semibold text-warm/50 uppercase tracking-wider block mb-1">Saldo Bersih</span>
            <div class="text-2xl font-display {{ $summary['balance'] >= 0 ? 'text-jade' : 'text-clay' }}">Rp{{ number_format($summary['balance'], 0, ',', '.') }}</div>
            <div class="text-xs text-warm/30 mt-1">{{ $summary['balance'] >= 0 ? 'Surplus' : 'Defisit' }}</div>
        </div>
    </div>

    <!-- Monthly breakdown -->
    <div class="bg-surface border border-amber/10 rounded-2xl overflow-hidden shadow-xl">
        <div class="px-6 py-4 border-b border-amber/10">
            <h2 class="font-bold text-warm">Perincian Bulanan</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-warm/80">
                <thead class="table-head text-xs uppercase tracking-wider text-warm/50 border-b border-amber/10">
                    <tr>
                        <th class="px-6 py-4">Bulan</th>
                        <th class="px-6 py-4 text-right">Penerimaan</th>
                        <th class="px-6 py-4 text-right">Pengeluaran</th>
                        <th class="px-6 py-4 text-right">Saldo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-amber/5">
                    @foreach($monthly as $m)
                        <tr class="hover:bg-warm/5 transition">
                            <td class="px-6 py-4 font-semibold text-warm">{{ $m['label'] }}</td>
                            <td class="px-6 py-4 text-right text-jade font-medium">Rp{{ number_format($m['income'], 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right text-clay font-medium">Rp{{ number_format($m['expense'], 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right font-bold {{ $m['balance'] >= 0 ? 'text-warm' : 'text-clay' }}">Rp{{ number_format($m['balance'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Transactions -->
    <div class="bg-surface border border-amber/10 rounded-2xl overflow-hidden shadow-xl">
        <div class="px-6 py-4 border-b border-amber/10">
            <h2 class="font-bold text-warm">Rincian Transaksi</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-warm/80">
                <thead class="table-head text-xs uppercase tracking-wider text-warm/50 border-b border-amber/10">
                    <tr>
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4">Keterangan</th>
                        <th class="px-6 py-4">Sumber</th>
                        <th class="px-6 py-4">Tipe</th>
                        <th class="px-6 py-4 text-right">Nominal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-amber/5">
                    @forelse($transactions as $t)
                        <tr class="hover:bg-warm/5 transition">
                            <td class="px-6 py-4 text-warm/50 text-xs whitespace-nowrap">{{ $t['date']->format('d M Y') }}</td>
                            <td class="px-6 py-4 font-semibold text-warm">{{ $t['description'] }}</td>
                            <td class="px-6 py-4 text-warm/50">{{ $t['source'] }}</td>
                            <td class="px-6 py-4">
                                @if($t['type'] === 'income')
                                    <span class="px-2.5 py-1 rounded-full bg-jade/10 border border-jade/30 text-jade text-xs font-semibold">Masuk</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full bg-clay/10 border border-clay/30 text-clay text-xs font-semibold">Keluar</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right font-bold {{ $t['type'] === 'income' ? 'text-jade' : 'text-clay' }} whitespace-nowrap">{{ $t['type'] === 'income' ? '+' : '-' }} Rp{{ number_format($t['amount'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-warm/30">Tidak ada transaksi pada rentang ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
            <div class="px-6 py-4 border-t border-amber/10">{{ $transactions->links() }}</div>
        @endif
    </div>
</div>
@endsection
