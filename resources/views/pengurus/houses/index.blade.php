@extends('layouts.app')

@section('title', 'Data Rumah — Pengurus RT')

@section('content')
<div x-data="arrearsModal" class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-display text-warm tracking-tight">Manajemen Data Rumah</h1>
            <p class="text-xs text-warm/50 mt-1">Kelola data rumah, blok, dan status hunian RT</p>
        </div>
        <a href="{{ route('pengurus.houses.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber hover:bg-amber/90 text-ink font-bold text-sm transition shadow-lg shadow-jade/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Rumah
        </a>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-surface/80 border border-amber/10 rounded-2xl p-4 flex flex-col sm:flex-row items-center gap-3">
        <form action="{{ route('pengurus.houses.index') }}" method="GET" class="w-full flex flex-col sm:flex-row items-center gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari blok / nomor rumah..."
                class="w-full sm:w-64 px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-jade/50">
            <select name="status" class="w-full sm:w-48 px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-jade/50">
                <option value="">Semua Status</option>
                <option value="ditempati" {{ request('status') === 'ditempati' ? 'selected' : '' }}>Ditempati</option>
                <option value="kosong" {{ request('status') === 'kosong' ? 'selected' : '' }}>Kosong</option>
                <option value="renovasi" {{ request('status') === 'renovasi' ? 'selected' : '' }}>Renovasi</option>
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
                        <th class="px-6 py-4">Blok & No</th>
                        <th class="px-6 py-4">Alamat Complete</th>
                        <th class="px-6 py-4">Status Hunian</th>
                        <th class="px-6 py-4 text-center">Jumlah Penghuni</th>
                        <th class="px-6 py-4 text-right">Tunggakan</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-amber/5">
                    @forelse($houses as $house)
                        <tr class="hover:bg-warm/5 transition">
                            <td class="px-6 py-4 font-bold text-warm">
                                {{ $house->full_address }}
                            </td>
                            <td class="px-6 py-4 text-warm/50">
                                {{ $house->address ?: '-' }}
                            </td>
                            <td class="px-6 py-4">
                                @if($house->status === 'ditempati')
                                    <span class="px-2.5 py-1 rounded-full bg-jade/10 border border-jade/30 text-jade text-xs font-semibold">Ditempati</span>
                                @elseif($house->status === 'kosong')
                                    <span class="px-2.5 py-1 rounded-full bg-clay/10 border border-clay/30 text-clay text-xs font-semibold">Kosong</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full bg-amber/10 border border-amber/30 text-amber text-xs font-semibold">Renovasi</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center font-semibold text-warm">
                                {{ $house->residents_count }} Orang
                            </td>
                            <td class="px-6 py-4 text-right">
                                @php $count = $arrearsCountByHouse[$house->id] ?? 0; @endphp
                                @if($count > 0)
                                    @php $bills = $arrearsDetailsByHouse->get($house->id, collect())->toArray(); @endphp
                                    <button
                                        type="button"
                                        @click="openModal('{{ $house->full_address }}', {{ json_encode($bills) }}, {{ $house->id }})"
                                        class="font-semibold text-clay hover:underline cursor-pointer text-sm"
                                    >
                                        {{ $count }} periode
                                    </button>
                                @else
                                    <span class="text-warm/30">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ route('pengurus.houses.edit', $house) }}" class="text-xs text-amber hover:underline">Edit</a>
                                <form action="{{ route('pengurus.houses.destroy', $house) }}" method="POST" class="inline" onsubmit="return confirm('Hapus data rumah ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-clay hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-warm/30">
                                Belum ada data rumah terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($houses->hasPages())
            <div class="px-6 py-4 border-t border-amber/10">
                {{ $houses->links() }}
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
            <button @click="arrearsModalOpen = false; confirmAmnestyOpen = true" class="mt-2 w-full px-4 py-2 rounded-xl bg-clay/10 border border-clay/30 text-clay text-sm font-semibold hover:bg-clay/20 transition">Pemutihan Semua Tunggakan</button>
        </div>
    </div>

    <!-- Pemutihan Confirmation Modal -->
    <div x-show="confirmAmnestyOpen" x-cloak class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[60] flex items-center justify-center p-4" @keydown.escape.window="confirmAmnestyOpen = false">
        <div class="bg-surface border border-amber/10 rounded-2xl p-6 w-full max-w-md shadow-2xl" @click.stop>
            <div class="flex items-center gap-3 mb-4">
                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-clay/10 border border-clay/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-clay" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-warm">Konfirmasi Pemutihan</h2>
                    <p class="text-xs text-warm/50">Tindakan ini tidak dapat dibatalkan</p>
                </div>
            </div>
            <div class="bg-clay/5 border border-clay/20 rounded-xl p-4 mb-4">
                <p class="text-sm text-warm/80">Anda akan memutihkan <span class="font-bold text-clay" x-text="arrearsModalTotal"></span> tunggakan untuk <span class="font-bold text-warm" x-text="arrearsModalTitle"></span></p>
            </div>
            <form :action="'{{ url('pengurus/houses') }}/' + amnestyHouseId + '/amnesty'" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-warm/50 mb-1.5">Alasan Pemutihan <span class="text-clay">*</span></label>
                    <input type="text" name="amnesty_reason" required maxlength="255" placeholder="Contoh: Pembayaran offline cash, Keputusan rapat RT, dll."
                        class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-clay/50">
                </div>
                <div class="flex gap-3">
                    <button type="button" @click="confirmAmnestyOpen = false; arrearsModalOpen = true"
                        class="flex-1 px-4 py-2.5 rounded-xl bg-warm/5 hover:bg-warm/10 text-warm/80 text-sm font-semibold transition">Batal</button>
                    <button type="submit"
                        class="flex-1 px-4 py-2.5 rounded-xl bg-clay hover:bg-clay/90 text-ink text-sm font-bold transition shadow-lg shadow-clay/20">Ya, Pemutihan</button>
                </div>
            </form>
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
            amnestyHouseId: null,
            confirmAmnestyOpen: false,
            openModal(title, bills, houseId) {
                this.arrearsModalTitle = title + ' — Tunggakan';
                this.amnestyHouseId = houseId;
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
