@extends('layouts.app')

@section('title', 'Pencatatan Pembayaran — Pengurus RT')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-display text-warm tracking-tight">Pencatatan Pembayaran Iuran</h1>
            <p class="text-xs text-warm/50 mt-1">Catat penerimaan kas pembayaran warga (otomatis teralokasi FIFO ke tagihan terlama)</p>
        </div>
        <a href="{{ route('pengurus.payments.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-jade hover:bg-jade/90 text-ink font-bold text-sm transition shadow-lg shadow-jade/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Catat Pembayaran Baru
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-surface border border-amber/10 rounded-2xl p-4">
        <form action="{{ route('pengurus.payments.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari blok/nomor rumah..."
                class="w-full sm:w-64 px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm">
            <select name="status" class="w-full sm:w-44 px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm">
                <option value="">Semua Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="void" {{ request('status') === 'void' ? 'selected' : '' }}>Void (Dibatalkan)</option>
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
                        <th class="px-6 py-4">Tgl Pembayaran</th>
                        <th class="px-6 py-4">Rumah & Pembayar</th>
                        <th class="px-6 py-4">Metode</th>
                        <th class="px-6 py-4">Nominal Diterima</th>
                        <th class="px-6 py-4">Alokasi Tagihan (FIFO)</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-amber/5">
                    @forelse($payments as $payment)
                        <tr class="hover:bg-warm/5 transition {{ $payment->status === 'void' ? 'opacity-60 bg-ink/40' : '' }}">
                            <td class="px-6 py-4 text-xs font-mono text-warm/50">
                                {{ $payment->payment_date->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-warm">{{ $payment->house ? $payment->house->full_address : '-' }}</div>
                                <div class="text-xs text-warm/50">{{ $payment->resident ? $payment->resident->nama_lengkap : 'Warga' }}</div>
                            </td>
                            <td class="px-6 py-4 text-xs uppercase font-semibold text-warm/70">
                                {{ $payment->payment_method }}
                            </td>
                            <td class="px-6 py-4 font-bold text-jade">
                                Rp{{ number_format($payment->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-xs text-warm/80">
                                @forelse($payment->allocations as $alloc)
                                    <span class="inline-block bg-ink px-2 py-0.5 rounded border border-amber/10 mr-1 mb-1">
                                        {{ $alloc->bill ? $alloc->bill->billing_period : '-' }}: Rp{{ number_format($alloc->amount, 0, ',', '.') }}
                                    </span>
                                @empty
                                    <span class="text-amber font-semibold">Saldo Kredit (Deposit)</span>
                                @endforelse
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($payment->status === 'active')
                                    <span class="px-2.5 py-1 rounded-full bg-jade/10 border border-jade/30 text-jade text-xs font-semibold">Aktif</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full bg-clay/10 border border-clay/30 text-clay text-xs font-semibold">VOID</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($payment->status === 'active')
                                    <form action="{{ route('pengurus.payments.void', $payment) }}" method="POST" onsubmit="return confirm('Batalkan (Void) transaksi pembayaran ini? Alokasi ke tagihan akan dikembalikan.')">
                                        @csrf
                                        <button type="submit" class="text-xs text-clay hover:underline font-semibold">Void</button>
                                    </form>
                                @else
                                    <span class="text-xs text-warm/30">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-warm/30">
                                Belum ada riwayat pembayaran dicatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
            <div class="px-6 py-4 border-t border-amber/10">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
