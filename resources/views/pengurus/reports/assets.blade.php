@extends('layouts.app')

@section('title', 'Laporan Inventaris â€” Pengurus RT')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-display text-warm tracking-tight">Laporan Inventaris</h1>
        <p class="text-xs text-warm/50 mt-1">Daftar seluruh aset milik RT</p>
    </div>

    <!-- Tabs -->
    <div class="flex gap-1 bg-surface border border-amber/10 rounded-2xl p-1 w-fit">
        <a href="{{ route('pengurus.reports.iuran') }}" class="px-4 py-2 rounded-xl text-warm/50 hover:text-warm hover:bg-warm/5 text-sm font-medium transition">Iuran</a>
        <a href="{{ route('pengurus.reports.payments') }}" class="px-4 py-2 rounded-xl text-warm/50 hover:text-warm hover:bg-warm/5 text-sm font-medium transition">Pembayaran</a>
        <a href="{{ route('pengurus.reports.arrears') }}" class="px-4 py-2 rounded-xl text-warm/50 hover:text-warm hover:bg-warm/5 text-sm font-medium transition">Tunggakan</a>
        <a href="{{ route('pengurus.reports.deposits') }}" class="px-4 py-2 rounded-xl text-warm/50 hover:text-warm hover:bg-warm/5 text-sm font-medium transition">Deposit</a>
        <a href="{{ route('pengurus.reports.cashflow') }}" class="px-4 py-2 rounded-xl text-warm/50 hover:text-warm hover:bg-warm/5 text-sm font-medium transition">Arus Kas</a>
        <a href="{{ route('pengurus.reports.assets') }}" class="px-4 py-2 rounded-xl bg-jade/10 text-jade font-semibold text-sm">Inventaris</a>
        <a href="{{ route('pengurus.reports.kependudukan') }}" class="px-4 py-2 rounded-xl text-warm/50 hover:text-warm hover:bg-warm/5 text-sm font-medium transition">Kependudukan</a>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-surface border border-amber/10 rounded-2xl p-5">
            <div class="text-xs text-warm/50 font-semibold uppercase tracking-wider">Total Aset</div>
            <div class="text-2xl font-bold text-warm mt-1">{{ $summary['total_assets'] }}</div>
        </div>
        <div class="bg-surface border border-amber/10 rounded-2xl p-5">
            <div class="text-xs text-warm/50 font-semibold uppercase tracking-wider">Total Nilai</div>
            <div class="text-2xl font-bold text-jade mt-1">Rp{{ number_format($summary['total_value'], 0, ',', '.') }}</div>
        </div>
        <div class="bg-surface border border-amber/10 rounded-2xl p-5">
            <div class="text-xs text-warm/50 font-semibold uppercase tracking-wider">Dipinjam</div>
            <div class="text-2xl font-bold text-amber mt-1">{{ $summary['total_borrowed'] }}</div>
        </div>
        <div class="bg-surface border border-amber/10 rounded-2xl p-5">
            <div class="text-xs text-warm/50 font-semibold uppercase tracking-wider">Bermasalah</div>
            <div class="text-2xl font-bold text-clay mt-1">{{ $summary['total_damaged'] }}</div>
        </div>
    </div>

    <!-- Export -->
    <div class="flex justify-end">
        <a href="{{ route('pengurus.reports.export', 'inventaris') }}" class="px-4 py-2 rounded-xl bg-jade/10 border border-jade/30 text-jade hover:bg-jade/20 text-sm font-semibold transition inline-flex items-center gap-2">
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
                        <th class="px-6 py-4">Kode</th>
                        <th class="px-6 py-4">Nama</th>
                        <th class="px-6 py-4">Kategori</th>
                        <th class="px-6 py-4 text-center">Jumlah</th>
                        <th class="px-6 py-4 text-center">Kondisi</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4">Lokasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-amber/5">
                    @forelse($assets as $asset)
                        <tr class="hover:bg-warm/5 transition">
                            <td class="px-6 py-4 font-mono font-bold text-jade">{{ $asset->asset_code }}</td>
                            <td class="px-6 py-4 font-semibold text-warm">{{ $asset->name }}</td>
                            <td class="px-6 py-4 text-warm/50">{{ $asset->category?->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-center font-mono">{{ $asset->quantity }} {{ $asset->unit }}</td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $conditionLabels = ['baik' => 'Baik', 'rusak_ringan' => 'Rusak Ringan', 'rusak_berat' => 'Rusak Berat', 'tidak_layak' => 'Tidak Layak', 'hilang' => 'Hilang'];
                                    $conditionColors = ['baik' => 'emerald', 'rusak_ringan' => 'amber', 'rusak_berat' => 'orange', 'tidak_layak' => 'rose', 'hilang' => 'slate'];
                                    $cc = $conditionColors[$asset->condition] ?? 'slate';
                                @endphp
                                <span class="px-2.5 py-1 rounded-full bg-{{ $cc }}-500/10 border border-{{ $cc }}-500/30 text-{{ $cc }}-400 text-xs font-semibold">{{ $conditionLabels[$asset->condition] ?? $asset->condition }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $statusLabels = ['tersedia' => 'Tersedia', 'dipinjam' => 'Dipinjam', 'dalam_perbaikan' => 'Perbaikan', 'tidak_aktif' => 'Nonaktif', 'hilang' => 'Hilang'];
                                    $statusColors = ['tersedia' => 'emerald', 'dipinjam' => 'amber', 'dalam_perbaikan' => 'blue', 'tidak_aktif' => 'slate', 'hilang' => 'rose'];
                                    $sc = $statusColors[$asset->status] ?? 'slate';
                                @endphp
                                <span class="px-2.5 py-1 rounded-full bg-{{ $sc }}-500/10 border border-{{ $sc }}-500/30 text-{{ $sc }}-400 text-xs font-semibold">{{ $statusLabels[$asset->status] ?? $asset->status }}</span>
                            </td>
                            <td class="px-6 py-4 text-warm/50 text-xs">{{ $asset->location ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-warm/30">Belum ada aset terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($assets->hasPages())
            <div class="px-6 py-4 border-t border-amber/10">{{ $assets->links() }}</div>
        @endif
    </div>
</div>
@endsection
