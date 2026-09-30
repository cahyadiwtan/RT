@extends('layouts.app')

@section('title', 'Event & Kegiatan — Pengurus RT')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="gelar mb-1"><span class="gelar-label">Event</span></div>
            <h1 class="text-2xl font-display text-warm tracking-tight">Event & Kegiatan</h1>
        </div>
        <a href="{{ route('pengurus.events.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber hover:bg-amber/90 text-ink font-bold text-sm transition shadow-lg shadow-amber/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Buat Event Baru
        </a>
    </div>

    <div class="bg-surface border border-amber/10 rounded-2xl p-4">
        <form action="{{ route('pengurus.events.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul, kode, kategori..."
                class="w-full sm:w-64 px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-amber/50">
            <select name="status" class="w-full sm:w-48 px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-amber/50">
                <option value="">Semua Status</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                <option value="registration_open" {{ request('status') === 'registration_open' ? 'selected' : '' }}>Registration Open</option>
                <option value="ongoing" {{ request('status') === 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
            </select>
            <button type="submit" class="px-4 py-2 rounded-xl bg-warm/10 hover:bg-warm/15 text-warm text-sm font-semibold transition">Filter</button>
        </form>
    </div>

    <div class="bg-surface border border-amber/10 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-warm/80">
                <thead class="table-head text-xs uppercase tracking-wider text-warm/50 border-b border-amber/10">
                    <tr>
                        <th class="px-6 py-4">Kode</th>
                        <th class="px-6 py-4">Judul</th>
                        <th class="px-6 py-4">Kategori</th>
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4 text-center">Peserta</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-amber/5">
                    @forelse($events as $event)
                        <tr class="hover:bg-warm/5 transition">
                            <td class="px-6 py-4 font-mono text-xs text-amber">{{ $event->event_code }}</td>
                            <td class="px-6 py-4 font-semibold text-warm">{{ $event->title }}</td>
                            <td class="px-6 py-4 text-warm/50">{{ $event->category ?: '-' }}</td>
                            <td class="px-6 py-4 text-warm/50 text-xs">
                                {{ $event->start_at->format('d M Y') }}
                                @if($event->end_at && $event->end_at->format('d M Y') !== $event->start_at->format('d M Y'))
                                    — {{ $event->end_at->format('d M Y') }}
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center font-semibold text-warm">{{ $event->participants_count }}</td>
                            <td class="px-6 py-4">
                                @php
                                    $statusColors = [
                                        'draft' => 'warm/30',
                                        'published' => 'jade',
                                        'registration_open' => 'amber',
                                        'ongoing' => 'amber',
                                        'completed' => 'jade',
                                        'cancelled' => 'clay',
                                        'closed' => 'warm/50',
                                    ];
                                    $color = $statusColors[$event->status] ?? 'warm/30';
                                @endphp
                                <span class="px-2.5 py-1 rounded-full bg-{{ $color }}/10 border border-{{ $color }}/30 text-{{ $color }} text-xs font-semibold capitalize">{{ str_replace('_', ' ', $event->status) }}</span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ route('pengurus.events.show', $event) }}" class="text-xs text-amber hover:underline">Detail</a>
                                <a href="{{ route('pengurus.events.edit', $event) }}" class="text-xs text-warm/50 hover:underline">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-warm/30">Belum ada event terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($events->hasPages())
            <div class="px-6 py-4 border-t border-amber/10">{{ $events->links() }}</div>
        @endif
    </div>
</div>
@endsection