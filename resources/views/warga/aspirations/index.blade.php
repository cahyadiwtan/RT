@extends('layouts.app')

@section('title', 'Aspirasi Saya — Sistem RT')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-display text-warm tracking-tight">Aspirasi Saya</h1>
            <p class="text-xs text-warm/50 mt-1">Daftar aspirasi, laporan, atau usulan yang telah Anda kirimkan kepada pengurus RT.</p>
        </div>
        <div>
            <a href="{{ route('warga.aspirations.create') }}" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-gradient-to-r from-jade to-jade text-ink font-semibold text-sm shadow-lg shadow-jade/20 hover:from-jade/90 hover:to-jade/90 transition gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                Kirim Aspirasi Baru
            </a>
        </div>
    </div>

    <!-- Aspirations Table -->
    <div class="bg-surface border border-amber/10 rounded-2xl overflow-hidden shadow-xl">
        <div class="p-4 border-b border-amber/10 font-bold text-warm">Riwayat Aspirasi</div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-warm/80">
                <thead class="table-head text-xs uppercase tracking-wider text-warm/50 border-b border-amber/10">
                    <tr>
                        <th class="px-6 py-4">Kategori</th>
                        <th class="px-6 py-4">Judul Aspirasi</th>
                        <th class="px-6 py-4">Tanggal Kirim</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-amber/5">
                    @forelse($aspirations as $aspiration)
                        <tr class="hover:bg-warm/5 transition">
                            <td class="px-6 py-4 font-semibold text-warm/70 capitalize">
                                {{ $aspiration->category }}
                            </td>
                            <td class="px-6 py-4 font-medium text-warm">
                                {{ $aspiration->title }}
                            </td>
                            <td class="px-6 py-4 text-xs text-warm/50">
                                {{ $aspiration->created_at->format('d M Y H:i') }}
                            </td>
                            <td class="px-6 py-4 text-center">
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
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('warga.aspirations.show', $aspiration->id) }}" class="inline-flex px-3 py-1.5 rounded-lg bg-warm/10 hover:bg-warm/10 border border-amber/20 text-xs text-warm font-semibold transition">
                                    Lihat Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-warm/30">
                                Anda belum pernah mengirimkan aspirasi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($aspirations instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator && $aspirations->hasPages())
        <div class="mt-4">
            {{ $aspirations->links() }}
        </div>
    @endif
</div>
@endsection
