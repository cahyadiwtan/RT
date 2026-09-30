@extends('layouts.app')

@section('title', 'Event & Kegiatan — Sistem RT')

@section('content')
<div class="space-y-6">
    <div>
        <div class="gelar mb-1"><span class="gelar-label">Event</span></div>
        <h1 class="text-2xl font-display text-warm tracking-tight">Event & Kegiatan RT</h1>
        <p class="text-sm text-warm/50 mt-1">Daftar kegiatan dan event yang sedang berlangsung</p>
    </div>

    <div class="space-y-4">
        @forelse($events as $event)
            <a href="{{ route('warga.events.show', $event) }}" class="block bg-surface border border-amber/10 rounded-2xl p-5 card-amber transition">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-mono text-[10px] text-amber">{{ $event->event_code }}</span>
                            @php
                                $statusColors = ['published' => 'jade', 'registration_open' => 'amber', 'ongoing' => 'amber', 'completed' => 'warm/30'];
                                $color = $statusColors[$event->status] ?? 'warm/30';
                            @endphp
                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-{{ $color }}/10 border border-{{ $color }}/30 text-{{ $color }} font-semibold capitalize">{{ str_replace('_', ' ', $event->status) }}</span>
                        </div>
                        <h2 class="font-display text-lg text-warm">{{ $event->title }}</h2>
                        <p class="text-xs text-warm/50 mt-1">{{ $event->category }} &middot; {{ $event->location ?: 'Lokasi TBD' }}</p>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-semibold text-warm/80">{{ $event->start_at->format('d M Y') }}</div>
                        <div class="text-xs text-warm/50 mt-1">{{ $event->participants_count }} peserta</div>
                    </div>
                </div>
                @if($event->target_amount && (float) $event->target_amount > 0)
                    @php $progress = min(100, round(($event->total_income / (float) $event->target_amount) * 100, 1)); @endphp
                    <div class="mt-3 flex items-center gap-3">
                        <div class="flex-1 bg-ink/50 rounded-full h-1.5">
                            <div class="bg-amber h-1.5 rounded-full transition-all" style="width: {{ $progress }}%"></div>
                        </div>
                        <span class="text-xs text-amber font-semibold">{{ $progress }}%</span>
                    </div>
                @endif
            </a>
        @empty
            <div class="bg-surface border border-amber/10 rounded-2xl p-8 text-center">
                <p class="text-warm/30">Belum ada event yang tersedia.</p>
            </div>
        @endforelse
    </div>

    @if($events->hasPages())
        <div>{{ $events->links() }}</div>
    @endif
</div>
@endsection
