@extends('layouts.app')

@section('title', $asset->asset_code . ' — Detail Aset')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-display text-warm tracking-tight">{{ $asset->name }}</h1>
            <p class="text-xs text-warm/50 mt-1">Kode: <span class="font-mono text-jade">{{ $asset->asset_code }}</span> &bull; {{ $asset->category?->name ?? '-' }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('pengurus.assets.edit', $asset) }}" class="px-4 py-2 rounded-xl bg-warm/5 hover:bg-warm/10 text-warm text-sm font-semibold transition">Edit</a>
            <form action="{{ route('pengurus.assets.destroy', $asset) }}" method="POST" onsubmit="return confirm('Hapus aset ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 rounded-xl bg-clay/10 border border-clay/30 text-clay hover:bg-clay/20 text-sm font-semibold transition">Hapus</button>
            </form>
        </div>
    </div>

    <!-- Info Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-surface border border-amber/10 rounded-2xl p-5">
            <div class="text-xs text-warm/50 font-semibold uppercase tracking-wider">Stok</div>
            <div class="text-2xl font-bold text-warm mt-1">{{ $asset->quantity }} {{ $asset->unit }}</div>
        </div>
        <div class="bg-surface border border-amber/10 rounded-2xl p-5">
            <div class="text-xs text-warm/50 font-semibold uppercase tracking-wider">Kondisi</div>
            @php
                $conditionLabels = ['baik' => 'Baik', 'rusak_ringan' => 'Rusak Ringan', 'rusak_berat' => 'Rusak Berat', 'tidak_layak' => 'Tidak Layak', 'hilang' => 'Hilang'];
                $conditionColors = ['baik' => 'emerald', 'rusak_ringan' => 'amber', 'rusak_berat' => 'orange', 'tidak_layak' => 'rose', 'hilang' => 'slate'];
                $cc = $conditionColors[$asset->condition] ?? 'slate';
            @endphp
            <div class="mt-1"><span class="px-3 py-1 rounded-full bg-{{ $cc }}-500/10 border border-{{ $cc }}-500/30 text-{{ $cc }}-400 text-sm font-semibold">{{ $conditionLabels[$asset->condition] ?? $asset->condition }}</span></div>
        </div>
        <div class="bg-surface border border-amber/10 rounded-2xl p-5">
            <div class="text-xs text-warm/50 font-semibold uppercase tracking-wider">Status</div>
            @php
                $statusLabels = ['tersedia' => 'Tersedia', 'dipinjam' => 'Dipinjam', 'dalam_perbaikan' => 'Perbaikan', 'tidak_aktif' => 'Nonaktif', 'hilang' => 'Hilang'];
                $statusColors = ['tersedia' => 'emerald', 'dipinjam' => 'amber', 'dalam_perbaikan' => 'blue', 'tidak_aktif' => 'slate', 'hilang' => 'rose'];
                $sc = $statusColors[$asset->status] ?? 'slate';
            @endphp
            <div class="mt-1"><span class="px-3 py-1 rounded-full bg-{{ $sc }}-500/10 border border-{{ $sc }}-500/30 text-{{ $sc }}-400 text-sm font-semibold">{{ $statusLabels[$asset->status] ?? $asset->status }}</span></div>
        </div>
        <div class="bg-surface border border-amber/10 rounded-2xl p-5">
            <div class="text-xs text-warm/50 font-semibold uppercase tracking-wider">Lokasi</div>
            <div class="text-lg font-bold text-warm mt-1">{{ $asset->location ?? '-' }}</div>
        </div>
    </div>

    @if($asset->photo)
        <div class="bg-surface border border-amber/10 rounded-2xl p-6">
            <h2 class="text-sm font-bold text-warm/80 uppercase tracking-wider mb-4">Foto Aset</h2>
            <img src="{{ asset('storage/' . $asset->photo) }}" alt="{{ $asset->name }}" class="max-w-md max-h-80 rounded-xl object-cover border border-amber/10">
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Detail Info -->
        <div class="bg-surface border border-amber/10 rounded-2xl p-6 space-y-4">
            <h2 class="text-sm font-bold text-warm/80 uppercase tracking-wider">Detail</h2>
            <dl class="grid grid-cols-2 gap-3 text-sm">
                <dt class="text-warm/50">Kode Aset</dt>
                <dd class="font-mono font-bold text-jade">{{ $asset->asset_code }}</dd>
                <dt class="text-warm/50">Merk</dt>
                <dd class="text-warm">{{ $asset->brand ?? '-' }}</dd>
                <dt class="text-warm/50">Model</dt>
                <dd class="text-warm">{{ $asset->model ?? '-' }}</dd>
                <dt class="text-warm/50">Serial Number</dt>
                <dd class="text-warm">{{ $asset->serial_number ?? '-' }}</dd>
                <dt class="text-warm/50">Tanggal Akuisisi</dt>
                <dd class="text-warm">{{ $asset->acquisition_date?->format('d/m/Y') ?? '-' }}</dd>
                <dt class="text-warm/50">Sumber</dt>
                <dd class="text-warm">{{ $asset->acquisition_source ?? '-' }}</dd>
                <dt class="text-warm/50">Harga Akuisisi</dt>
                <dd class="text-warm">{{ $asset->acquisition_price ? 'Rp' . number_format($asset->acquisition_price, 0, ',', '.') : '-' }}</dd>
                <dt class="text-warm/50">Nilai Sekarang</dt>
                <dd class="text-warm">{{ $asset->current_value ? 'Rp' . number_format($asset->current_value, 0, ',', '.') : '-' }}</dd>
                <dt class="text-warm/50">Catatan</dt>
                <dd class="text-warm">{{ $asset->notes ?? '-' }}</dd>
            </dl>
        </div>

        <!-- Movement History -->
        <div class="bg-surface border border-amber/10 rounded-2xl p-6 space-y-4">
            <h2 class="text-sm font-bold text-warm/80 uppercase tracking-wider">Riwayat Pergerakan</h2>
            @forelse($movements as $movement)
                <div class="flex gap-3 items-start">
                    <div class="w-2 h-2 rounded-full bg-jade mt-2 shrink-0"></div>
                    <div>
                        <div class="text-xs text-warm/50 font-mono">{{ $movement->created_at->format('d/m/Y H:i') }}</div>
                        <div class="text-sm text-warm">{{ ucfirst(str_replace('_', ' ', $movement->type)) }} — {{ $movement->quantity }} unit</div>
                        @if($movement->notes)
                            <div class="text-xs text-warm/30">{{ $movement->notes }}</div>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-sm text-warm/30">Belum ada riwayat pergerakan.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
