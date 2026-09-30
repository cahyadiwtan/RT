@extends('layouts.app')

@section('title', 'Data Warga — Pengurus RT')

@section('content')
<div x-data="arrearsModal" class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-display text-warm tracking-tight">Manajemen Data Warga</h1>
            <p class="text-xs text-warm/50 mt-1">Kelola data identitas warga, NIK, dan status verifikasi</p>
        </div>
        <a href="{{ route('pengurus.residents.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber hover:bg-amber/90 text-ink font-bold text-sm transition shadow-lg shadow-jade/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Warga Baru
        </a>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-surface/80 border border-amber/10 rounded-2xl p-4">
        <form action="{{ route('pengurus.residents.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / NIK / No KK..."
                class="w-full sm:w-56 px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-jade/50">
            <select name="house_id" class="w-full sm:w-44 px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-jade/50">
                <option value="">Semua Rumah</option>
                @foreach($houses as $house)
                    <option value="{{ $house->id }}" {{ request('house_id') == $house->id ? 'selected' : '' }}>{{ $house->full_address }}</option>
                @endforeach
            </select>
            <select name="status_warga" class="w-full sm:w-44 px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-jade/50">
                <option value="">Semua Status</option>
                <option value="aktif" {{ request('status_warga') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                <option value="pindah" {{ request('status_warga') === 'pindah' ? 'selected' : '' }}>Pindah</option>
                <option value="meninggal" {{ request('status_warga') === 'meninggal' ? 'selected' : '' }}>Meninggal</option>
            </select>
            <select name="is_verified" class="w-full sm:w-44 px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-jade/50">
                <option value="">Verifikasi</option>
                <option value="1" {{ request('is_verified') === '1' ? 'selected' : '' }}>Verified</option>
                <option value="0" {{ request('is_verified') === '0' ? 'selected' : '' }}>Belum</option>
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
                        <th class="px-6 py-4">Nama Lengkap</th>
                        <th class="px-6 py-4">NIK / KK</th>
                        <th class="px-6 py-4">Rumah</th>
                        <th class="px-6 py-4 text-right">Tunggakan</th>
                        <th class="px-6 py-4">Telepon</th>
                        <th class="px-6 py-4 text-center">Verifikasi NIK</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-amber/5">
                    @forelse($residents as $resident)
                        <tr class="hover:bg-warm/5 transition">
                            <td class="px-6 py-4">
                                <div class="font-bold text-warm">{{ $resident->nama_lengkap }}</div>
                                <div class="text-xs text-warm/50 capitalize">{{ str_replace('_', ' ', $resident->hubungan_dalam_keluarga) }}</div>
                            </td>
                            <td class="px-6 py-4 font-mono text-xs text-warm/80">
                                <div>NIK: {{ $resident->nik }}</div>
                                <div class="text-warm/30">KK: {{ $resident->nomor_kk ?: '-' }}</div>
                            </td>
                            <td class="px-6 py-4 text-jade font-semibold">
                                {{ $resident->house ? $resident->house->full_address : '-' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                @php $count = $arrearsCountByHouse[$resident->house_id] ?? 0; @endphp
                                @if($count > 0)
                                    @php $bills = $arrearsDetailsByHouse->get($resident->house_id, collect())->toArray(); @endphp
                                    <button
                                        type="button"
                                        @click="openModal('{{ $resident->house?->full_address ?? 'Rumah' }}', {{ json_encode($bills) }})"
                                        class="font-semibold text-clay hover:underline cursor-pointer text-sm"
                                    >
                                        {{ $count }} periode
                                    </button>
                                @else
                                    <span class="text-warm/30">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-warm/50 text-xs">
                                {{ $resident->nomor_telepon ?: '-' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <form action="{{ route('pengurus.residents.toggle-verify', $resident) }}" method="POST">
                                    @csrf
                                    @if($resident->is_verified)
                                        <button type="submit" class="px-3 py-1 rounded-full bg-jade/10 border border-jade/30 text-jade text-xs font-semibold hover:bg-clay/10 hover:border-clay/30 hover:text-clay transition" title="Klik untuk membatalkan verifikasi">
                                            ✓ Verified
                                        </button>
                                    @else
                                        <button type="submit" class="px-3 py-1 rounded-full bg-amber/10 border border-amber/30 text-amber text-xs font-semibold hover:bg-jade/10 hover:border-jade/30 hover:text-jade transition" title="Klik untuk memverifikasi NIK">
                                            Verifikasi NIK
                                        </button>
                                    @endif
                                </form>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ route('pengurus.residents.edit', $resident) }}" class="text-xs text-amber hover:underline">Edit</a>
                                <form action="{{ route('pengurus.residents.destroy', $resident) }}" method="POST" class="inline" onsubmit="return confirm('Hapus data warga ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-clay hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-warm/30">
                                Belum ada data warga terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($residents->hasPages())
            <div class="px-6 py-4 border-t border-amber/10">
                {{ $residents->links() }}
            </div>
        @endif
    </div>

    <!-- Arrears Detail Modal -->
    <div x-show="arrearsModalOpen" x-cloak class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" @keydown.escape.window="arrearsModalOpen = false">
        <div class="bg-surface border border-amber/20 rounded-2xl p-6 w-full max-w-lg shadow-2xl shadow-black/25" @click.stop>
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-bold text-warm" x-text="arrearsModalTitle"></h2>
                    <p class="text-xs text-warm/50">Daftar periode yang belum terbayar</p>
                </div>
                <button @click="arrearsModalOpen = false" class="text-warm/50 hover:text-warm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="space-y-2 max-h-80 overflow-y-auto" x-html="arrearsModalBody"></div>
            <div class="flex justify-between items-center mt-4 pt-4 border-t border-amber/10">
                <span class="text-xs text-warm/50">Total Tunggakan</span>
                <span class="text-lg font-bold text-clay" x-text="arrearsModalTotal"></span>
            </div>
            <button @click="arrearsModalOpen = false" class="mt-4 w-full px-4 py-2 rounded-xl bg-warm/5 hover:bg-warm/10 text-warm/80 text-sm font-semibold transition">Tutup</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('arrearsModal', () => ({
            arrearsModalOpen: false,
            arrearsModalTitle: '',
            arrearsModalBody: '',
            arrearsModalTotal: '',
            openModal(title, bills) {
                this.arrearsModalTitle = title + ' — Tunggakan';
                let total = 0;
                const rows = bills.map(b => {
                    total += parseFloat(b.remaining_amount);
                    const badge = b.status === 'unpaid'
                        ? '<span class="arrear-badge arrear-badge--unpaid">Belum Bayar</span>'
                        : '<span class="arrear-badge arrear-badge--partial">Partial</span>';
                    return `<div class="arrear-row">
                        <div>
                            <span class="text-sm font-semibold text-warm">${b.billing_period}</span>
                            ${badge}
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-warm/30">Rp${new Intl.NumberFormat('id-ID').format(b.paid_amount)} dibayar</div>
                            <div class="text-sm font-bold text-clay">Rp${new Intl.NumberFormat('id-ID').format(b.remaining_amount)}</div>
                        </div>
                    </div>`;
                }).join('');
                this.arrearsModalBody = rows;
                this.arrearsModalTotal = 'Rp' + new Intl.NumberFormat('id-ID').format(total);
                this.arrearsModalOpen = true;
            }
        }));
    });
</script>
@endpush
