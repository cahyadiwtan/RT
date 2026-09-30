@extends('layouts.app')

@section('title', $event->title . ' — Event')

@section('content')
<div class="space-y-6">
    <div>
        <a href="{{ route('warga.events.index') }}" class="text-xs text-amber hover:underline">&larr; Kembali ke Daftar Event</a>
        <div class="gelar mb-1 mt-2"><span class="gelar-label">{{ $event->event_code }}</span></div>
        <h1 class="text-2xl font-display text-warm tracking-tight">{{ $event->title }}</h1>
        <p class="text-sm text-warm/50 mt-1">{{ $event->category }} &middot; {{ $event->location ?: 'Lokasi belum ditentukan' }}</p>
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

    @if($event->description)
        <div class="bg-surface border border-amber/10 rounded-2xl p-5">
            <p class="text-sm text-warm/80 leading-relaxed">{{ $event->description }}</p>
        </div>
    @endif

    <!-- Financial Summary -->
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

    <!-- Payment Summary -->
    @php $ps = $summary['payment_summary']; @endphp
    @if($ps['total'] > 0 && $event->funding_type !== 'free')
    <div class="bg-surface border border-amber/10 rounded-2xl p-5">
        <h3 class="text-sm font-semibold text-warm mb-3">Status Pembayaran Peserta</h3>
        <div class="grid grid-cols-4 gap-2 text-center text-xs">
            <div class="bg-jade/10 rounded-lg py-2"><span class="text-jade font-bold block text-lg">{{ $ps['paid'] }}</span><span class="text-warm/50">Lunas</span></div>
            <div class="bg-amber/10 rounded-lg py-2"><span class="text-amber font-bold block text-lg">{{ $ps['partial'] }}</span><span class="text-warm/50">Partial</span></div>
            <div class="bg-clay/10 rounded-lg py-2"><span class="text-clay font-bold block text-lg">{{ $ps['unpaid'] }}</span><span class="text-warm/50">Belum Bayar</span></div>
            <div class="bg-warm/5 rounded-lg py-2"><span class="text-warm/50 font-bold block text-lg">{{ $ps['waived'] }}</span><span class="text-warm/50">Bebas</span></div>
        </div>
    </div>
    @endif

    <!-- My Payment Status -->
    @if($myPayment)
    <div class="bg-surface border border-amber/10 rounded-2xl p-5">
        <h3 class="text-sm font-semibold text-warm mb-3">Status Pembayaran Saya</h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div>
                <span class="text-xs text-warm/50 block">Kewajiban</span>
                <span class="text-sm font-semibold text-warm">Rp{{ number_format($myPayment->payment_amount, 0, ',', '.') }}</span>
            </div>
            <div>
                <span class="text-xs text-warm/50 block">Sudah Dibayar</span>
                <span class="text-sm font-semibold text-jade">
                    @php
                        $totalPaid = \App\Models\EventPayment::where('event_participant_id', $myPayment->id)->where('status', 'active')->sum('amount');
                    @endphp
                    Rp{{ number_format($totalPaid, 0, ',', '.') }}
                </span>
            </div>
            <div>
                <span class="text-xs text-warm/50 block">Sisa</span>
                <span class="text-sm font-semibold {{ $myPayment->payment_status === 'paid' ? 'text-jade' : 'text-clay' }}">
                    Rp{{ number_format(max(0, $myPayment->payment_amount - $totalPaid), 0, ',', '.') }}
                </span>
            </div>
            <div>
                <span class="text-xs text-warm/50 block">Status</span>
                @php
                    $psColors = ['paid' => 'jade', 'partial' => 'amber', 'unpaid' => 'clay', 'waived' => 'warm/30'];
                    $psColor = $psColors[$myPayment->payment_status] ?? 'warm/30';
                @endphp
                <span class="text-xs px-2 py-0.5 rounded-full bg-{{ $psColor }}/10 border border-{{ $psColor }}/30 text-{{ $psColor }} font-semibold capitalize">{{ $myPayment->payment_status }}</span>
            </div>
        </div>
    </div>
    @endif

    <!-- Expenses -->
    @if($event->expenses->where('status', 'active')->count() > 0)
    <div class="bg-surface border border-amber/10 rounded-2xl p-5">
        <h3 class="text-sm font-semibold text-warm mb-3">Rincian Pengeluaran</h3>
        <div class="space-y-2">
            @foreach($event->expenses()->with('category')->where('status', 'active')->get() as $expense)
                <div class="flex justify-between items-center bg-ink/50 rounded-xl px-4 py-3 border border-amber/5">
                    <div>
                        <span class="text-sm font-semibold text-warm">{{ $expense->description }}</span>
                        <span class="text-xs text-warm/50 ml-2">{{ $expense->category?->name }}</span>
                    </div>
                    <span class="font-mono text-sm text-clay">Rp{{ number_format($expense->amount, 0, ',', '.') }}</span>
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
