@extends('layouts.app')

@section('title', 'Dashboard Saya — Sistem RT')

@section('content')
<div class="space-y-8">
    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-surface via-surface to-ink border border-amber/10 rounded-2xl p-6 sm:p-8 flex flex-col md:flex-row md:items-center justify-between gap-6 shadow-xl">
        <div>
            <div class="gelar mb-3">
                <span class="gelar-label">Warga</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-display text-warm tracking-tight">
                Halo, {{ $resident ? $resident->nama_lengkap : $user->username }}!
            </h1>
            <p class="text-warm/50 text-sm mt-1">
                Rumah: <span class="text-amber font-semibold">{{ $house ? $house->full_address : 'Belum ditautkan' }}</span>
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3.5 py-2 rounded-xl bg-ink border border-amber/20 font-mono font-bold text-xs text-warm/70">
                NIK: {{ $resident ? substr($resident->nik, 0, 4) . '************' : '-' }}
            </span>
        </div>
    </div>

    <!-- Financial Cards Grid for Warga -->
    @if($financials)
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Card 1: Iuran Bulanan -->
        <div class="card-amber bg-surface border border-amber/10 rounded-2xl p-5">
            <span class="text-xs font-semibold text-warm/50 uppercase tracking-wider block mb-2">Iuran Bulanan</span>
            <div class="text-2xl font-display text-warm">Rp30.000</div>
            <span class="text-xs text-warm/50 mt-1 block">Tarif Periode 2026</span>
        </div>

        <!-- Card 2: Pembayaran Terakhir -->
        <div class="card-amber bg-surface border border-amber/10 rounded-2xl p-5">
            <span class="text-xs font-semibold text-warm/50 uppercase tracking-wider block mb-2">Pembayaran Terakhir</span>
            <div class="text-2xl font-display text-jade">
                {{ $financials['last_payment_date'] ? date('d M Y', strtotime($financials['last_payment_date'])) : 'Belum Ada' }}
            </div>
            <span class="text-xs text-warm/50 mt-1 block">
                {{ $financials['last_payment_amount'] > 0 ? 'Nominal: Rp' . number_format($financials['last_payment_amount'], 0, ',', '.') : '-' }}
            </span>
        </div>

        <!-- Card 3: Tunggakan -->
        <div class="card-amber bg-surface border border-amber/10 rounded-2xl p-5">
            <span class="text-xs font-semibold text-warm/50 uppercase tracking-wider block mb-2">Tunggakan Saya</span>
            <div class="text-2xl font-display {{ $financials['total_arrears'] > 0 ? 'text-clay' : 'text-jade' }}">
                Rp{{ number_format($financials['total_arrears'], 0, ',', '.') }}
            </div>
            <span class="text-xs text-warm/50 mt-1 block">
                {{ $financials['total_arrears'] > 0 ? 'Mohon segera dilakukan pembayaran' : 'Lunas seluruhnya!' }}
            </span>
        </div>

        <!-- Card 4: Saldo Deposit -->
        <div class="card-amber bg-surface border border-amber/10 rounded-2xl p-5">
            <span class="text-xs font-semibold text-warm/50 uppercase tracking-wider block mb-2">Saldo Deposit (Kredit)</span>
            <div class="text-2xl font-display text-amber">
                Rp{{ number_format($financials['credit_balance'], 0, ',', '.') }}
            </div>
            <span class="text-xs text-warm/50 mt-1 block">Kelebihan pembayaran simpanan</span>
        </div>
    </div>
    @endif
</div>
@endsection
