@extends('layouts.app')

@section('title', 'Pengeluaran — Pengurus RT')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="gelar mb-1"><span class="gelar-label">Kas RT</span></div>
            <h1 class="text-2xl font-display text-warm tracking-tight">Pengeluaran Kas Umum</h1>
            <p class="text-sm text-warm/50 mt-1">Catat pengeluaran operasional RT di luar event.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('pengurus.expense-categories.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-warm/5 hover:bg-warm/10 text-warm text-sm font-semibold transition">
                Kelola Kategori
            </a>
            <a href="{{ route('pengurus.expenses.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber hover:bg-amber/90 text-ink font-bold text-sm transition shadow-lg shadow-amber/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Catat Pengeluaran
            </a>
        </div>
    </div>

    <!-- Summary -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-surface border border-amber/10 rounded-2xl p-5 shadow-xl">
            <span class="text-xs font-semibold text-warm/50 uppercase tracking-wider block mb-1">Total Pengeluaran</span>
            <div class="text-2xl font-display text-clay">Rp{{ number_format($summary['total'], 0, ',', '.') }}</div>
        </div>
        <div class="bg-surface border border-amber/10 rounded-2xl p-5 shadow-xl">
            <span class="text-xs font-semibold text-warm/50 uppercase tracking-wider block mb-1">Jumlah Transaksi</span>
            <div class="text-2xl font-display text-warm">{{ $summary['count'] }}</div>
        </div>
        <div class="bg-surface border border-amber/10 rounded-2xl p-5 shadow-xl">
            <span class="text-xs font-semibold text-warm/50 uppercase tracking-wider block mb-1">Kategori Terbesar</span>
            @php $top = $summary['by_category']->first(); @endphp
            @if($top)
                <div class="text-2xl font-display text-warm">{{ $top['name'] }}</div>
                <div class="text-sm text-warm/50">Rp{{ number_format($top['total_amount'], 0, ',', '.') }} ({{ $top['count'] }})</div>
            @else
                <div class="text-2xl font-display text-warm/30">-</div>
            @endif
        </div>
    </div>

    <!-- Filter -->
    <div class="bg-surface border border-amber/10 rounded-2xl p-4">
        <form action="{{ route('pengurus.expenses.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3 flex-wrap">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari deskripsi, vendor, no. bukti..."
                class="w-full sm:w-64 px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-amber/50">
            <select name="category_id" class="w-full sm:w-56 px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-amber/50">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-amber/50">
            <span class="text-warm/40 text-sm">s.d.</span>
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-amber/50">
            <button type="submit" class="px-4 py-2 rounded-xl bg-warm/10 hover:bg-warm/15 text-warm text-sm font-semibold transition">Filter</button>
            <a href="{{ route('pengurus.expenses.index') }}" class="px-4 py-2 rounded-xl text-warm/50 hover:text-warm text-sm transition">Reset</a>
        </form>
    </div>

    <div class="bg-surface border border-amber/10 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-warm/80">
                <thead class="table-head text-xs uppercase tracking-wider text-warm/50 border-b border-amber/10">
                    <tr>
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4">Deskripsi</th>
                        <th class="px-6 py-4">Kategori</th>
                        <th class="px-6 py-4">Vendor</th>
                        <th class="px-6 py-4 text-right">Jumlah</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-amber/5">
                    @forelse($expenses as $expense)
                        <tr class="hover:bg-warm/5 transition">
                            <td class="px-6 py-4 text-warm/50 text-xs whitespace-nowrap">{{ $expense->expense_date->format('d M Y') }}</td>
                            <td class="px-6 py-4">
                                <span class="font-semibold text-warm">{{ $expense->description }}</span>
                                @if($expense->receipt_number)
                                    <span class="block text-xs text-warm/30 font-mono">No. {{ $expense->receipt_number }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($expense->category)
                                    <span class="px-2.5 py-1 rounded-full bg-amber/10 border border-amber/30 text-amber text-xs font-semibold">{{ $expense->category->name }}</span>
                                @else
                                    <span class="text-warm/30">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-warm/50">{{ $expense->vendor ?: '-' }}</td>
                            <td class="px-6 py-4 text-right font-bold text-clay whitespace-nowrap">Rp{{ number_format($expense->amount, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                                <a href="{{ route('pengurus.expenses.edit', $expense) }}" class="text-xs text-amber hover:underline">Edit</a>
                                <form action="{{ route('pengurus.expenses.void', $expense) }}" method="POST" class="inline" onsubmit="return confirm('Batalkan (void) pengeluaran ini?')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-xs text-clay hover:underline">Void</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-warm/30">Belum ada pengeluaran tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($expenses->hasPages())
            <div class="px-6 py-4 border-t border-amber/10">{{ $expenses->links() }}</div>
        @endif
    </div>
</div>
@endsection
