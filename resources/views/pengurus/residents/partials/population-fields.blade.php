{{-- Field rekapitulasi kependudukan: dipakai form create & edit warga --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div>
        <label class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">Nomor KTP</label>
        <input type="text" name="nomor_ktp" value="{{ old('nomor_ktp', $resident->nomor_ktp ?? null) }}" maxlength="16" placeholder="Kosong = belum punya KTP"
            class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:ring-2 focus:ring-jade/50">
    </div>
    <div>
        <label class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">Kewarganegaraan</label>
        <select name="kewarganegaraan" class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:ring-2 focus:ring-jade/50">
            <option value="WNI" {{ old('kewarganegaraan', $resident->kewarganegaraan ?? 'WNI') === 'WNI' ? 'selected' : '' }}>WNI</option>
            <option value="WNA" {{ old('kewarganegaraan', $resident->kewarganegaraan ?? 'WNI') === 'WNA' ? 'selected' : '' }}>WNA</option>
        </select>
    </div>
    <div>
        <label class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">Domisili Asal</label>
        <select name="domisili_asal" class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:ring-2 focus:ring-jade/50">
            <option value="DD" {{ old('domisili_asal', $resident->domisili_asal ?? 'DD') === 'DD' ? 'selected' : '' }}>DD - Dalam Daerah</option>
            <option value="LD" {{ old('domisili_asal', $resident->domisili_asal ?? 'DD') === 'LD' ? 'selected' : '' }}>LD - Luar Daerah</option>
        </select>
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">Tanggal Meninggal</label>
        <input type="date" name="tanggal_kematian" value="{{ old('tanggal_kematian', $resident->tanggal_kematian?->format('Y-m-d')) }}"
            class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:ring-2 focus:ring-jade/50">
        <p class="text-[10px] text-warm/40 mt-1">Dipakai kolom MENINGGAL di laporan bulanan.</p>
    </div>
    <div>
        <label class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">Tanggal Pindah</label>
        <input type="date" name="tanggal_pindah" value="{{ old('tanggal_pindah', $resident->tanggal_pindah?->format('Y-m-d')) }}"
            class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:ring-2 focus:ring-jade/50">
        <p class="text-[10px] text-warm/40 mt-1">Dipakai kolom PINDAH di laporan bulanan.</p>
    </div>
</div>
