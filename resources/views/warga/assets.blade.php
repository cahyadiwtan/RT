@extends('layouts.app')

@section('title', 'Inventaris RT — Warga')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-display text-warm tracking-tight">Inventaris / Aset RT</h1>
        <p class="text-xs text-warm/50 mt-1">Daftar aset yang dimiliki oleh RT</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($assets as $asset)
            <div class="bg-surface border border-amber/10 rounded-2xl p-5 space-y-3">
                @if($asset->photo)
                    <img src="{{ asset('storage/' . $asset->photo) }}" alt="{{ $asset->name }}" class="w-full h-40 rounded-xl object-cover border border-amber/10">
                @endif
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="font-bold text-warm">{{ $asset->name }}</h3>
                        <p class="text-xs text-warm/50">{{ $asset->category?->name ?? '-' }}</p>
                    </div>
                    <span class="font-mono text-xs text-jade">{{ $asset->asset_code }}</span>
                </div>
                @if($asset->description)
                    <p class="text-xs text-warm/50">{{ Str::limit($asset->description, 80) }}</p>
                @endif
                <div class="flex items-center gap-3 text-xs">
                    <span class="text-warm/80">Stok: <span class="font-bold text-warm">{{ $asset->quantity }} {{ $asset->unit }}</span></span>
                    @php
                        $conditionLabels = ['baik' => 'Baik', 'rusak_ringan' => 'Rusak Ringan', 'rusak_berat' => 'Rusak Berat', 'tidak_layak' => 'Tidak Layak', 'hilang' => 'Hilang'];
                        $conditionTokens = ['baik' => 'jade', 'rusak_ringan' => 'amber', 'rusak_berat' => 'clay', 'tidak_layak' => 'clay', 'hilang' => 'warm'];
                        $ct = $conditionTokens[$asset->condition] ?? 'warm';
                    @endphp
                    <span class="px-2 py-0.5 rounded-full bg-{{ $ct }}/10 border border-{{ $ct }}/30 text-{{ $ct }} font-semibold">{{ $conditionLabels[$asset->condition] ?? $asset->condition }}</span>
                </div>
                @if($asset->location)
                    <div class="text-xs text-warm/30">Lokasi: {{ $asset->location }}</div>
                @endif
            </div>
        @empty
            <div class="sm:col-span-2 lg:col-span-3 bg-surface border border-amber/10 rounded-2xl p-8 text-center text-warm/30">
                Belum ada aset terdaftar.
            </div>
        @endforelse
    </div>

    @if($assets->hasPages())
        <div>{{ $assets->links() }}</div>
    @endif
</div>
@endsection
