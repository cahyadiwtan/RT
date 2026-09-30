@extends('layouts.app')

@section('title', 'Manajemen Pengguna — Pengurus RT')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-display text-warm tracking-tight">Manajemen Pengguna</h1>
            <p class="text-xs text-warm/50 mt-1">Kelola akun login pengurus & warga</p>
        </div>
        <a href="{{ route('pengurus.users.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber hover:bg-amber/90 text-ink font-bold text-sm transition shadow-lg shadow-amber/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Pengguna
        </a>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-surface/80 border border-amber/10 rounded-2xl p-4">
        <form action="{{ route('pengurus.users.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari username / nama warga..."
                class="w-full sm:w-64 px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-jade/50">
            <select name="role" class="w-full sm:w-44 px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-jade/50">
                <option value="">Semua Role</option>
                <option value="pengurus" {{ request('role') === 'pengurus' ? 'selected' : '' }}>Pengurus</option>
                <option value="warga" {{ request('role') === 'warga' ? 'selected' : '' }}>Warga</option>
            </select>
            <select name="is_active" class="w-full sm:w-44 px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-jade/50">
                <option value="">Semua Status</option>
                <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Aktif</option>
                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Nonaktif</option>
            </select>
            <button type="submit" class="px-4 py-2 rounded-xl bg-warm/5 hover:bg-warm/10 text-warm text-sm font-semibold transition">
                Filter
            </button>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-surface/80 border border-amber/10 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-warm/80">
                <thead class="table-head text-xs uppercase tracking-wider text-warm/50 border-b border-amber/10">
                    <tr>
                        <th class="px-6 py-4">Akun</th>
                        <th class="px-6 py-4">Warga Terhubung</th>
                        <th class="px-6 py-4">Terakhir Login</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-amber/5">
                    @forelse($users as $user)
                        <tr class="hover:bg-warm/5 transition {{ request('is_active') === '0' ? '' : '' }}">
                            <td class="px-6 py-4">
                                <div class="font-bold text-warm">{{ $user->username }}</div>
                                @if($user->role === 'pengurus')
                                    <span class="px-2 py-0.5 rounded-full bg-amber/10 border border-amber/30 text-amber text-[10px] font-semibold">Pengurus</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full bg-jade/10 border border-jade/30 text-jade text-[10px] font-semibold">Warga</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($user->resident)
                                    <div class="font-medium text-warm">{{ $user->resident->nama_lengkap }}</div>
                                    <div class="text-xs text-warm/50">{{ $user->resident->house ? $user->resident->house->full_address : '-' }}</div>
                                @else
                                    <span class="text-warm/30">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-mono text-xs text-warm/50">
                                {{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($user->id === auth()->id())
                                    <span class="px-3 py-1 rounded-full bg-jade/10 border border-jade/30 text-jade text-xs font-semibold">Aktif</span>
                                @else
                                    <form action="{{ route('pengurus.users.toggle-active', $user) }}" method="POST">
                                        @csrf
                                        @if($user->is_active)
                                            <button type="submit" class="px-3 py-1 rounded-full bg-jade/10 border border-jade/30 text-jade text-xs font-semibold hover:bg-clay/10 hover:border-clay/30 hover:text-clay transition" title="Klik untuk menonaktifkan">
                                                Aktif
                                            </button>
                                        @else
                                            <button type="submit" class="px-3 py-1 rounded-full bg-clay/10 border border-clay/30 text-clay text-xs font-semibold hover:bg-jade/10 hover:border-jade/30 hover:text-jade transition" title="Klik untuk mengaktifkan">
                                                Nonaktif
                                            </button>
                                        @endif
                                    </form>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ route('pengurus.users.edit', $user) }}" class="text-xs text-amber hover:underline">Edit</a>
                                @if($user->id !== auth()->id())
                                    <form action="{{ route('pengurus.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Hapus akun ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-clay hover:underline">Hapus</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-warm/30">
                                Belum ada akun pengguna.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-amber/10">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection