@extends('layouts.app')

@section('title', 'Daftar Aspirasi Warga — Pengurus RT')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-display text-warm tracking-tight">Manajemen Aspirasi Warga</h1>
        <p class="text-xs text-warm/50 mt-1">Pantau dan berikan tanggapan atau tindak lanjut pada aspirasi dan usulan warga.</p>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-surface border border-amber/10 rounded-2xl p-4 shadow-lg">
        <form action="{{ route('pengurus.aspirations.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
            <!-- Search -->
            <div class="w-full sm:w-72">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul / nama warga..."
                    class="w-full px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-jade/50 placeholder-warm/30">
            </div>

            <!-- Kategori Filter -->
            <div class="w-full sm:w-48">
                <select name="category" class="w-full px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-jade/50">
                    <option value="">Semua Kategori</option>
                    <option value="keamanan" {{ request('category') === 'keamanan' ? 'selected' : '' }}>Keamanan</option>
                    <option value="kebersihan" {{ request('category') === 'kebersihan' ? 'selected' : '' }}>Kebersihan</option>
                    <option value="fasilitas" {{ request('category') === 'fasilitas' ? 'selected' : '' }}>Fasilitas</option>
                    <option value="lingkungan" {{ request('category') === 'lingkungan' ? 'selected' : '' }}>Lingkungan</option>
                    <option value="sosial" {{ request('category') === 'sosial' ? 'selected' : '' }}>Sosial</option>
                    <option value="lainnya" {{ request('category') === 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                </select>
            </div>

            <!-- Status Filter -->
            <div class="w-full sm:w-48">
                <select name="status" class="w-full px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-jade/50">
                    <option value="">Semua Status</option>
                    <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Dikirim (Baru)</option>
                    <option value="reviewed" {{ request('status') === 'reviewed' ? 'selected' : '' }}>Ditinjau</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>Diproses</option>
                    <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Selesai</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>

            <!-- Button -->
            <div class="w-full sm:w-auto">
                <button type="submit" class="w-full px-5 py-2 rounded-xl bg-warm/5 hover:bg-warm/10 text-warm text-sm font-semibold transition">
                    Cari & Saring
                </button>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-surface border border-amber/10 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-warm/80">
                <thead class="table-head text-xs uppercase tracking-wider text-warm/50 border-b border-amber/10">
                    <tr>
                        <th class="px-6 py-4">Warga</th>
                        <th class="px-6 py-4">Kategori</th>
                        <th class="px-6 py-4">Judul Aspirasi</th>
                        <th class="px-6 py-4">Tanggal Masuk</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-amber/5">
                    @forelse($aspirations as $aspiration)
                        <tr class="hover:bg-warm/5 transition">
                            <td class="px-6 py-4">
                                <div class="font-bold text-warm">{{ $aspiration->resident ? $aspiration->resident->nama_lengkap : 'Warga Tanpa Nama' }}</div>
                                <div class="text-xs text-jade font-semibold">{{ $aspiration->resident && $aspiration->resident->house ? $aspiration->resident->house->full_address : '-' }}</div>
                            </td>
                            <td class="px-6 py-4 font-medium text-warm/70 capitalize">
                                {{ $aspiration->category }}
                            </td>
                            <td class="px-6 py-4 font-semibold text-warm">
                                {{ $aspiration->title }}
                            </td>
                            <td class="px-6 py-4 text-xs text-warm/50 font-mono">
                                {{ $aspiration->created_at->format('d M Y H:i') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($aspiration->status === 'submitted')
                                    <span class="px-3 py-1 rounded-full bg-warm/5 border border-warm/10 text-warm/50 text-xs font-semibold">Dikirim</span>
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
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('pengurus.aspirations.show', $aspiration->id) }}" class="inline-flex px-3 py-1.5 rounded-lg bg-jade/10 text-jade hover:bg-jade/20 border border-jade/30 text-xs font-semibold transition">
                                    Detail & Tindak Lanjut
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-warm/30">
                                Belum ada data aspirasi masuk.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($aspirations->hasPages())
            <div class="px-6 py-4 border-t border-amber/10">
                {{ $aspirations->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
