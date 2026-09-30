@extends('layouts.app')

@section('title', 'Semua Aset — Pengurus RT')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-display text-warm tracking-tight">Inventaris / Aset RT</h1>
            <p class="text-xs text-warm/50 mt-1">Daftar seluruh aset yang dimiliki RT</p>
        </div>
        <a href="{{ route('pengurus.assets.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber hover:bg-amber/90 text-ink font-bold text-sm transition shadow-lg shadow-jade/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Aset
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-surface border border-amber/10 rounded-2xl p-4">
        <form action="{{ route('pengurus.assets.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama/kode/lokasi..."
                class="w-full sm:w-56 px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm">

            <select name="category_id" class="w-full sm:w-44 px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>

            <select name="condition" class="w-full sm:w-40 px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm">
                <option value="">Semua Kondisi</option>
                <option value="baik" {{ request('condition') === 'baik' ? 'selected' : '' }}>Baik</option>
                <option value="rusak_ringan" {{ request('condition') === 'rusak_ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                <option value="rusak_berat" {{ request('condition') === 'rusak_berat' ? 'selected' : '' }}>Rusak Berat</option>
                <option value="tidak_layak" {{ request('condition') === 'tidak_layak' ? 'selected' : '' }}>Tidak Layak</option>
                <option value="hilang" {{ request('condition') === 'hilang' ? 'selected' : '' }}>Hilang</option>
            </select>

            <select name="status" class="w-full sm:w-40 px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm">
                <option value="">Semua Status</option>
                <option value="tersedia" {{ request('status') === 'tersedia' ? 'selected' : '' }}>Tersedia</option>
                <option value="dipinjam" {{ request('status') === 'dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                <option value="dalam_perbaikan" {{ request('status') === 'dalam_perbaikan' ? 'selected' : '' }}>Perbaikan</option>
                <option value="tidak_aktif" {{ request('status') === 'tidak_aktif' ? 'selected' : '' }}>Nonaktif</option>
                <option value="hilang" {{ request('status') === 'hilang' ? 'selected' : '' }}>Hilang</option>
            </select>

            <button type="submit" class="px-4 py-2 rounded-xl bg-warm/5 hover:bg-warm/10 text-warm text-sm font-semibold transition">Filter</button>
        </form>
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
                        <th class="px-6 py-4 text-center">Stok</th>
                        <th class="px-6 py-4 text-center">Kondisi</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4">Lokasi</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-amber/5">
                    @forelse($assets as $asset)
                        <tr class="hover:bg-warm/5 transition">
                            <td class="px-6 py-4 font-mono font-bold text-jade">{{ $asset->asset_code }}</td>
                            <td class="px-6 py-4 font-semibold text-warm">{{ $asset->name }}</td>
                            <td class="px-6 py-4 text-warm/50">{{ $asset->category?->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-center font-mono font-bold">{{ $asset->quantity }} {{ $asset->unit }}</td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $conditionColors = [
                                        'baik' => 'emerald',
                                        'rusak_ringan' => 'amber',
                                        'rusak_berat' => 'orange',
                                        'tidak_layak' => 'rose',
                                        'hilang' => 'slate',
                                    ];
                                    $conditionLabels = [
                                        'baik' => 'Baik',
                                        'rusak_ringan' => 'Rusak Ringan',
                                        'rusak_berat' => 'Rusak Berat',
                                        'tidak_layak' => 'Tidak Layak',
                                        'hilang' => 'Hilang',
                                    ];
                                    $color = $conditionColors[$asset->condition] ?? 'slate';
                                @endphp
                                <span class="px-2.5 py-1 rounded-full bg-{{ $color }}-500/10 border border-{{ $color }}-500/30 text-{{ $color }}-400 text-xs font-semibold">{{ $conditionLabels[$asset->condition] ?? $asset->condition }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $statusColors = [
                                        'tersedia' => 'emerald',
                                        'dipinjam' => 'amber',
                                        'dalam_perbaikan' => 'blue',
                                        'tidak_aktif' => 'slate',
                                        'hilang' => 'rose',
                                    ];
                                    $statusLabels = [
                                        'tersedia' => 'Tersedia',
                                        'dipinjam' => 'Dipinjam',
                                        'dalam_perbaikan' => 'Perbaikan',
                                        'tidak_aktif' => 'Nonaktif',
                                        'hilang' => 'Hilang',
                                    ];
                                    $sc = $statusColors[$asset->status] ?? 'slate';
                                @endphp
                                <span class="px-2.5 py-1 rounded-full bg-{{ $sc }}-500/10 border border-{{ $sc }}-500/30 text-{{ $sc }}-400 text-xs font-semibold">{{ $statusLabels[$asset->status] ?? $asset->status }}</span>
                            </td>
                            <td class="px-6 py-4 text-warm/50 text-xs">{{ $asset->location ?? '-' }}</td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('pengurus.assets.show', $asset) }}" class="text-xs text-jade hover:underline font-semibold">Detail</a>
                                <span class="text-warm/30 mx-1">|</span>
                                <a href="{{ route('pengurus.assets.edit', $asset) }}" class="text-xs text-blue-400 hover:underline font-semibold">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-warm/30">Belum ada aset terdaftar.</td>
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
