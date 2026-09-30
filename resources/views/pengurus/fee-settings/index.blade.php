@extends('layouts.app')

@section('title', 'Pengaturan Nominal Iuran — Pengurus RT')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-display text-warm tracking-tight">Pengaturan Nominal Iuran Bulanan</h1>
        <p class="text-xs text-warm/50 mt-1">Konfigurasi besaran tarif iuran rutin per periode (Tagihan lama tidak akan berubah ketika nominal baru dibuat)</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Form Create Setting -->
        <div class="bg-surface border border-amber/10 rounded-2xl p-6 shadow-xl h-fit">
            <h2 class="text-lg font-bold text-warm mb-4">Set Nominal Tarif Baru</h2>

            <form action="{{ route('pengurus.fee-settings.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">Nominal Iuran (Rp / Bulan)</label>
                    <input type="number" name="amount" value="{{ old('amount', 30000) }}" required step="1000" min="1000"
                        class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:ring-2 focus:ring-jade/50 font-bold text-jade">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">Berlaku Mulai Tanggal</label>
                    <input type="date" name="effective_from" value="{{ old('effective_from', date('Y-m-01')) }}" required
                        class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:ring-2 focus:ring-jade/50">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">Berlaku Sampai Tanggal (Opsional)</label>
                    <input type="date" name="effective_until" value="{{ old('effective_until') }}"
                        class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:ring-2 focus:ring-jade/50">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">Keterangan / Peraturan</label>
                    <input type="text" name="description" value="{{ old('description') }}" placeholder="Iuran Kebersihan & Keamanan 2026"
                        class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:ring-2 focus:ring-jade/50">
                </div>

                <button type="submit" class="w-full py-3 rounded-xl bg-jade hover:bg-jade/90 text-ink font-bold text-sm transition shadow-md">
                    Simpan Tarif Iuran Baru
                </button>
            </form>
        </div>

        <!-- History List -->
        <div class="lg:col-span-2 bg-surface border border-amber/10 rounded-2xl overflow-hidden shadow-xl">
            <div class="p-4 border-b border-amber/10 font-bold text-warm">Riwayat Pengaturan Tarif Iuran</div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-warm/80">
                    <thead class="table-head text-xs uppercase tracking-wider text-warm/50 border-b border-amber/10">
                        <tr>
                            <th class="px-6 py-4">Nominal</th>
                            <th class="px-6 py-4">Periode Berlaku</th>
                            <th class="px-6 py-4">Keterangan</th>
                            <th class="px-6 py-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-amber/5">
                        @forelse($settings as $setting)
                            <tr class="hover:bg-warm/5 transition">
                                <td class="px-6 py-4 font-bold text-jade">
                                    Rp{{ number_format($setting->amount, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-xs font-mono">
                                    {{ $setting->effective_from->format('d/m/Y') }} s/d {{ $setting->effective_until ? $setting->effective_until->format('d/m/Y') : 'Seterusnya' }}
                                </td>
                                <td class="px-6 py-4 text-warm/80 text-xs">
                                    {{ $setting->description ?: '-' }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($setting->is_active)
                                        <span class="px-2.5 py-1 rounded-full bg-jade/10 border border-jade/30 text-jade text-xs font-semibold">Aktif</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full bg-warm/5 text-warm/50 text-xs font-semibold">Non-aktif</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-warm/30">
                                    Belum ada pengaturan tarif iuran.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
