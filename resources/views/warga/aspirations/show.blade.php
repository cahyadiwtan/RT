@extends('layouts.app')

@section('title', 'Detail Aspirasi — Sistem RT')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('warga.aspirations.index') }}" class="w-10 h-10 rounded-xl bg-surface hover:bg-warm/10 border border-amber/10 flex items-center justify-center text-warm/80 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-display text-warm tracking-tight">Detail Aspirasi</h1>
            <p class="text-xs text-warm/50 mt-1">Status dan perkembangan tindak lanjut dari aspirasi yang Anda kirimkan.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Aspiration Card Detail (Col-span 2) -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-surface border border-amber/10 rounded-2xl p-6 shadow-xl space-y-5">
                <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-amber/10">
                    <div>
                        <span class="px-3 py-1 rounded-full bg-warm/10 border border-warm/30 text-warm text-xs font-semibold capitalize">{{ $aspiration->category }}</span>
                    </div>
                    <div class="text-xs text-warm/50 font-medium">
                        Dikirim pada: <span class="font-mono text-warm/80">{{ $aspiration->created_at->format('d M Y H:i') }}</span>
                    </div>
                </div>

                <div class="space-y-2">
                    <h2 class="text-xl font-bold text-warm">{{ $aspiration->title }}</h2>
                    <p class="text-sm text-warm/80 leading-relaxed whitespace-pre-line">{{ $aspiration->description }}</p>
                </div>

                @if($aspiration->attachment)
                    <div class="pt-4 border-t border-amber/10 space-y-2">
                        <span class="block text-xs font-semibold text-warm/50 uppercase tracking-wider">Lampiran Berkas</span>
                        <div class="flex items-center gap-3 bg-ink/60 border border-amber/10 rounded-xl p-3">
                            <svg class="w-8 h-8 text-jade shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <div class="min-w-0 flex-grow">
                                <span class="block text-xs text-warm/50 truncate">Nama File: {{ basename($aspiration->attachment) }}</span>
                            </div>
                            <div class="shrink-0">
                                <a href="{{ asset('storage/' . $aspiration->attachment) }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-jade/10 text-jade border border-jade/30 hover:bg-jade/20 text-xs font-semibold transition">
                                    Buka File
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Progress Timeline (Col-span 1) -->
        <div class="space-y-6">
            <div class="bg-surface border border-amber/10 rounded-2xl p-6 shadow-xl space-y-5">
                <h3 class="text-sm font-bold text-warm uppercase tracking-wider pb-3 border-b border-amber/10">Status & Tindak Lanjut</h3>
                
                <div class="space-y-4">
                    <!-- Current Status Card -->
                    <div class="flex items-center justify-between p-3.5 rounded-xl bg-ink/60 border border-amber/10">
                        <span class="text-xs text-warm/50 font-semibold uppercase">Status Saat Ini</span>
                        @if($aspiration->status === 'submitted')
                            <span class="px-3 py-1 rounded-full bg-warm/10 border border-warm/30 text-warm/50 text-xs font-semibold">Dikirim</span>
                        @elseif($aspiration->status === 'reviewed')
                            <span class="px-3 py-1 rounded-full bg-sky-500/10 border border-sky-500/30 text-sky-400 text-xs font-semibold">Ditinjau</span>
                        @elseif($aspiration->status === 'in_progress')
                            <span class="px-3 py-1 rounded-full bg-amber/10 border border-amber/30 text-amber text-xs font-semibold">Diproses</span>
                        @elseif($aspiration->status === 'resolved')
                            <span class="px-3 py-1 rounded-full bg-jade/10 border border-jade/30 text-jade text-xs font-semibold">Selesai</span>
                        @elseif($aspiration->status === 'rejected')
                            <span class="px-3 py-1 rounded-full bg-clay/10 border border-clay/30 text-clay text-xs font-semibold">Ditolak</span>
                        @endif
                    </div>

                    <!-- Timeline Loop -->
                    <div class="relative pl-6 border-l-2 border-amber/5 space-y-6 mt-6">
                        <!-- Initial Submission Timeline Item -->
                        <div class="relative">
                            <span class="absolute -left-[31px] top-1.5 w-4 h-4 rounded-full bg-warm/10 border-4 border-surface"></span>
                            <div class="text-xs font-semibold text-warm/50">
                                {{ $aspiration->created_at->format('d M Y H:i') }}
                            </div>
                            <div class="text-xs text-warm/80 font-bold mt-1">Aspirasi Berhasil Dikirim</div>
                            <div class="text-xs text-warm/50 mt-0.5">Dikirim oleh Anda</div>
                        </div>

                        <!-- Loop Updates -->
                        @foreach($aspiration->updates->sortBy('created_at') as $update)
                            <div class="relative">
                                <span class="absolute -left-[31px] top-1.5 w-4 h-4 rounded-full bg-jade border-4 border-surface"></span>
                                <div class="text-xs font-semibold text-warm/50">
                                    {{ $update->created_at->format('d M Y H:i') }}
                                </div>
                                <div class="text-xs font-bold mt-1">
                                    Status diubah menjadi: 
                                    <span class="capitalize text-jade font-semibold">
                                        @if($update->status === 'reviewed') Ditinjau
                                        @elseif($update->status === 'in_progress') Diproses
                                        @elseif($update->status === 'resolved') Selesai
                                        @elseif($update->status === 'rejected') Ditolak
                                        @else {{ $update->status }}
                                        @endif
                                    </span>
                                </div>
                                <div class="text-xs text-warm/80 mt-1 p-2 rounded-lg bg-ink/60 border border-amber/5 italic leading-relaxed">
                                    "{{ $update->comment }}"
                                </div>
                                <div class="text-[10px] text-warm/50 mt-1 block">
                                    Oleh: <span class="font-semibold">{{ $update->user->isPengurus() ? 'Pengurus RT' : ($update->user->resident ? $update->user->resident->nama_lengkap : $update->user->username) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
