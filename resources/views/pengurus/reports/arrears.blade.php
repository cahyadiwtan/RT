@extends('layouts.app')

@section('title', 'Laporan Tunggakan â€” Pengurus RT')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-display text-warm tracking-tight">Laporan Tunggakan</h1>
        <p class="text-xs text-warm/50 mt-1">Daftar rumah dengan tagihan belum lunas</p>
    </div>

    <!-- Tabs -->
    <div class="flex gap-1 bg-surface border border-amber/10 rounded-2xl p-1 w-fit">
        <a href="{{ route('pengurus.reports.iuran') }}" class="px-4 py-2 rounded-xl text-warm/50 hover:text-warm hover:bg-warm/5 text-sm font-medium transition">Iuran</a>
        <a href="{{ route('pengurus.reports.payments') }}" class="px-4 py-2 rounded-xl text-warm/50 hover:text-warm hover:bg-warm/5 text-sm font-medium transition">Pembayaran</a>
        <a href="{{ route('pengurus.reports.arrears') }}" class="px-4 py-2 rounded-xl bg-jade/10 text-jade font-semibold text-sm">Tunggakan</a>
        <a href="{{ route('pengurus.reports.deposits') }}" class="px-4 py-2 rounded-xl text-warm/50 hover:text-warm hover:bg-warm/5 text-sm font-medium transition">Deposit</a>
        <a href="{{ route('pengurus.reports.cashflow') }}" class="px-4 py-2 rounded-xl text-warm/50 hover:text-warm hover:bg-warm/5 text-sm font-medium transition">Arus Kas</a>
        <a href="{{ route('pengurus.reports.assets') }}" class="px-4 py-2 rounded-xl text-warm/50 hover:text-warm hover:bg-warm/5 text-sm font-medium transition">Inventaris</a>
        <a href="{{ route('pengurus.reports.kependudukan') }}" class="px-4 py-2 rounded-xl text-warm/50 hover:text-warm hover:bg-warm/5 text-sm font-medium transition">Kependudukan</a>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-surface border border-amber/10 rounded-2xl p-5">
            <div class="text-xs text-warm/50 font-semibold uppercase tracking-wider">Total Tunggakan</div>
            <div class="text-2xl font-bold text-clay mt-1">Rp{{ number_format($summary['total_arrears'], 0, ',', '.') }}</div>
        </div>
        <div class="bg-surface border border-amber/10 rounded-2xl p-5">
            <div class="text-xs text-warm/50 font-semibold uppercase tracking-wider">Jumlah Tagihan Belum Lunas</div>
            <div class="text-2xl font-bold text-warm mt-1">{{ $summary['count'] }}</div>
        </div>
    </div>

    <!-- Export -->
    <div class="flex justify-end">
        <a href="{{ route('pengurus.reports.export', 'tunggakan') }}" class="px-4 py-2 rounded-xl bg-jade/10 border border-jade/30 text-jade hover:bg-jade/20 text-sm font-semibold transition inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Export CSV
        </a>
    </div>

    <!-- Table -->
    <div class="bg-surface border border-amber/10 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-warm/80">
                <thead class="table-head text-xs uppercase tracking-wider text-warm/50 border-b border-amber/10">
                    <tr>
                        <th class="px-6 py-4">Rumah</th>
                        <th class="px-6 py-4">Warga</th>
                        <th class="px-6 py-4">Periode</th>
                        <th class="px-6 py-4">Sisa Tunggakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-amber/5">
                    @forelse($bills as $bill)
                        <tr class="hover:bg-warm/5 transition">
                            <td class="px-6 py-4 font-semibold text-warm">{{ $bill->house?->full_address ?? '-' }}</td>
                            <td class="px-6 py-4 text-warm/80">
                                @forelse($bill->house->residents as $resident)
                                    <span class="inline-block">{{ $resident->nama_lengkap }}</span>{{ $loop->last ? '' : ', ' }}
                                @empty
                                    <span class="text-warm/30">-</span>
                                @endforelse
                            </td>
                            <td class="px-6 py-4 font-mono font-bold text-jade">{{ $bill->billing_period }}</td>
                            <td class="px-6 py-4 font-bold text-clay">Rp{{ number_format($bill->remaining_amount, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-warm/30">Tidak ada tunggakan. Semua tagihan sudah lunas.</td>
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
