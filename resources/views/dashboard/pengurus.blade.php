@extends('layouts.app')

@section('title', 'Dashboard Pengurus — Sistem RT')

@section('content')
<div class="space-y-8">
    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-surface via-surface to-ink border border-amber/10 rounded-2xl p-6 sm:p-8 flex flex-col md:flex-row md:items-center justify-between gap-6 shadow-xl">
        <div>
            <div class="gelar mb-3">
                <span class="gelar-label">Pengurus RT</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-display text-warm tracking-tight">Dashboard Administrasi & Transparansi</h1>
            <p class="text-warm/50 text-sm mt-1">Ringkasan kondisi warga, keuangan, dan operasional RT</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-xs text-warm/50">Periode Aktif:</span>
            <span class="px-3.5 py-2 rounded-xl bg-ink border border-amber/20 font-mono font-bold text-sm text-amber">
                {{ $summary['current_period'] }}
            </span>
        </div>
    </div>

    <!-- Stat Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Card 1: Total Rumah -->
        <div class="card-amber bg-surface border border-amber/10 rounded-2xl p-5">
            <div class="flex items-center justify-between text-warm/50 mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider">Total Rumah</span>
                <div class="w-8 h-8 rounded-lg bg-amber/10 flex items-center justify-center text-amber">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                </div>
            </div>
            <div class="text-3xl font-display text-warm">{{ $summary['total_houses'] }}</div>
            <p class="text-xs text-warm/50 mt-1">{{ $summary['occupied_houses'] }} Rumah Ditempati</p>
        </div>

        <!-- Card 2: Tagihan Bulan Ini -->
        <div class="card-amber bg-surface border border-amber/10 rounded-2xl p-5">
            <div class="flex items-center justify-between text-warm/50 mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider">Tagihan Bulan Ini</span>
                <div class="w-8 h-8 rounded-lg bg-jade/10 flex items-center justify-center text-jade">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
            </div>
            <div class="text-3xl font-display text-warm">Rp{{ number_format($summary['current_period_bill'], 0, ',', '.') }}</div>
            <p class="text-xs text-jade mt-1">Terkumpul: Rp{{ number_format($summary['current_period_paid'], 0, ',', '.') }}</p>
        </div>

        <!-- Card 3: Total Tunggakan -->
        <div class="card-amber bg-surface border border-amber/10 rounded-2xl p-5">
            <div class="flex items-center justify-between text-warm/50 mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider">Total Tunggakan</span>
                <div class="w-8 h-8 rounded-lg bg-clay/10 flex items-center justify-center text-clay">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="text-3xl font-display text-clay">Rp{{ number_format($summary['total_arrears'], 0, ',', '.') }}</div>
            <p class="text-xs text-warm/50 mt-1">Belum Dilunasi Seluruh Warga</p>
        </div>

        <!-- Card 4: Total Pembayaran -->
        <div class="card-amber bg-surface border border-amber/10 rounded-2xl p-5">
            <div class="flex items-center justify-between text-warm/50 mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider">Total Pembayaran</span>
                <div class="w-8 h-8 rounded-lg bg-amber/10 flex items-center justify-center text-amber">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
            <div class="text-3xl font-display text-warm">Rp{{ number_format($summary['total_collected_all_time'], 0, ',', '.') }}</div>
            <p class="text-xs text-warm/50 mt-1">Total Kas Masuk Teratat</p>
        </div>
    </div>
</div>
@endsection
