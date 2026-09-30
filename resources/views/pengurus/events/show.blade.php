@extends('layouts.app')

@section('title', $event->title . ' — Event')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="gelar mb-1"><span class="gelar-label">{{ $event->event_code }}</span></div>
            <h1 class="text-2xl font-display text-warm tracking-tight">{{ $event->title }}</h1>
            <p class="text-sm text-warm/50 mt-1">{{ $event->category }} &middot; {{ $event->location ?: 'Lokasi belum ditentukan' }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('pengurus.events.edit', $event) }}" class="px-3 py-1.5 rounded-xl bg-warm/5 text-warm/70 text-xs font-semibold hover:bg-warm/10 transition">Edit</a>
            <form action="{{ route('pengurus.events.update-status', $event) }}" method="POST" class="inline">
                @csrf
                @method('PATCH')
                <select name="status" onchange="this.form.submit()" class="px-3 py-1.5 rounded-xl bg-ink border border-amber/10 text-warm text-xs font-semibold focus:outline-none">
                    <option value="draft" {{ $event->status === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="published" {{ $event->status === 'published' ? 'selected' : '' }}>Published</option>
                    <option value="registration_open" {{ $event->status === 'registration_open' ? 'selected' : '' }}>Registration Open</option>
                    <option value="ongoing" {{ $event->status === 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                    <option value="completed" {{ $event->status === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ $event->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="closed" {{ $event->status === 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </form>
        </div>
    </div>

    <!-- Event Info -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-surface border border-amber/10 rounded-2xl p-4">
            <span class="text-xs text-warm/50 block mb-1">Tanggal Mulai</span>
            <span class="text-sm font-semibold text-warm">{{ $event->start_at->format('d M Y H:i') }}</span>
        </div>
        <div class="bg-surface border border-amber/10 rounded-2xl p-4">
            <span class="text-xs text-warm/50 block mb-1">Tanggal Selesai</span>
            <span class="text-sm font-semibold text-warm">{{ $event->end_at->format('d M Y H:i') }}</span>
        </div>
        <div class="bg-surface border border-amber/10 rounded-2xl p-4">
            <span class="text-xs text-warm/50 block mb-1">Tipe Pendanaan</span>
            <span class="text-sm font-semibold text-amber capitalize">{{ str_replace('_', ' ', $event->funding_type) }}</span>
        </div>
    </div>

    <!-- Financial Summary Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="card-amber bg-surface border border-amber/10 rounded-2xl p-4">
            <span class="text-xs text-warm/50 uppercase tracking-wider block mb-2">Total Pemasukan</span>
            <div class="text-xl font-display text-jade">Rp{{ number_format($summary['total_income'], 0, ',', '.') }}</div>
        </div>
        <div class="card-amber bg-surface border border-amber/10 rounded-2xl p-4">
            <span class="text-xs text-warm/50 uppercase tracking-wider block mb-2">Total Pengeluaran</span>
            <div class="text-xl font-display text-clay">Rp{{ number_format($summary['total_expense'], 0, ',', '.') }}</div>
        </div>
        <div class="card-amber bg-surface border border-amber/10 rounded-2xl p-4">
            <span class="text-xs text-warm/50 uppercase tracking-wider block mb-2">Saldo</span>
            <div class="text-xl font-display {{ $summary['balance'] >= 0 ? 'text-jade' : 'text-clay' }}">Rp{{ number_format($summary['balance'], 0, ',', '.') }}</div>
        </div>
        <div class="card-amber bg-surface border border-amber/10 rounded-2xl p-4">
            <span class="text-xs text-warm/50 uppercase tracking-wider block mb-2">Progress Target</span>
            <div class="text-xl font-display text-amber">{{ $summary['target_progress'] }}%</div>
            @if($event->target_amount)
                <div class="w-full bg-ink/50 rounded-full h-1.5 mt-2">
                    <div class="bg-amber h-1.5 rounded-full transition-all" style="width: {{ $summary['target_progress'] }}%"></div>
                </div>
            @endif
        </div>
    </div>

    <!-- Tabs -->
    <div x-data="{ activeTab: 'participants', showAddParticipant: false, showAddIncome: false, showAddExpense: false, showAddBudget: false }" class="space-y-4">
        <div class="flex gap-1 bg-surface border border-amber/10 rounded-xl p-1 overflow-x-auto">
            <button @click="activeTab = 'participants'" :class="activeTab === 'participants' ? 'bg-amber/10 text-amber' : 'text-warm/50 hover:text-warm'" class="px-4 py-2 rounded-lg text-xs font-semibold transition whitespace-nowrap">Peserta ({{ $summary['payment_summary']['total'] }})</button>
            <button @click="activeTab = 'payments'" :class="activeTab === 'payments' ? 'bg-amber/10 text-amber' : 'text-warm/50 hover:text-warm'" class="px-4 py-2 rounded-lg text-xs font-semibold transition whitespace-nowrap">Pembayaran</button>
            <button @click="activeTab = 'income'" :class="activeTab === 'income' ? 'bg-amber/10 text-amber' : 'text-warm/50 hover:text-warm'" class="px-4 py-2 rounded-lg text-xs font-semibold transition whitespace-nowrap">Pemasukan</button>
            <button @click="activeTab = 'expenses'" :class="activeTab === 'expenses' ? 'bg-amber/10 text-amber' : 'text-warm/50 hover:text-warm'" class="px-4 py-2 rounded-lg text-xs font-semibold transition whitespace-nowrap">Pengeluaran</button>
            <button @click="activeTab = 'budgets'" :class="activeTab === 'budgets' ? 'bg-amber/10 text-amber' : 'text-warm/50 hover:text-warm'" class="px-4 py-2 rounded-lg text-xs font-semibold transition whitespace-nowrap">Anggaran</button>
        </div>

        <!-- Participants Tab -->
        <div x-show="activeTab === 'participants'" x-cloak>
            @include('pengurus.events._participants')
        </div>

        <!-- Payments Tab -->
        <div x-show="activeTab === 'payments'" x-cloak>
            @include('pengurus.events._payments')
        </div>

        <!-- Income Tab -->
        <div x-show="activeTab === 'income'" x-cloak>
            @include('pengurus.events._income')
        </div>

        <!-- Expenses Tab -->
        <div x-show="activeTab === 'expenses'" x-cloak>
            @include('pengurus.events._expenses')
        </div>

        <!-- Budgets Tab -->
        <div x-show="activeTab === 'budgets'" x-cloak>
            @include('pengurus.events._budgets')
        </div>
    </div>
</div>
@endsection
