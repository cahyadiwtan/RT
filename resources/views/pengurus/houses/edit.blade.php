@extends('layouts.app')

@section('title', 'Edit Rumah — Pengurus RT')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="bg-surface/80 border border-amber/10 rounded-2xl p-6 shadow-xl">
        <h1 class="text-xl font-bold text-warm mb-6">Edit Data Rumah {{ $house->full_address }}</h1>

        <form action="{{ route('pengurus.houses.update', $house) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">Blok</label>
                    <input type="text" name="block" value="{{ old('block', $house->block) }}" required
                        class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:ring-2 focus:ring-jade/50">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">Nomor Rumah</label>
                    <input type="text" name="house_number" value="{{ old('house_number', $house->house_number) }}" required
                        class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:ring-2 focus:ring-jade/50">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">Alamat Lengkap</label>
                <textarea name="address" rows="3"
                    class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:ring-2 focus:ring-jade/50">{{ old('address', $house->address) }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">Status Hunian</label>
                <select name="status" class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:ring-2 focus:ring-jade/50">
                    <option value="ditempati" {{ old('status', $house->status) === 'ditempati' ? 'selected' : '' }}>Ditempati</option>
                    <option value="kosong" {{ old('status', $house->status) === 'kosong' ? 'selected' : '' }}>Kosong</option>
                    <option value="renovasi" {{ old('status', $house->status) === 'renovasi' ? 'selected' : '' }}>Renovasi</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">Catatan (Opsional)</label>
                <textarea name="notes" rows="2" class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:ring-2 focus:ring-jade/50">{{ old('notes', $house->notes) }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-amber/10">
                <a href="{{ route('pengurus.houses.index') }}" class="px-4 py-2 rounded-xl bg-warm/5 text-warm/80 text-sm font-semibold hover:bg-warm/10 transition">Batal</a>
                <button type="submit" class="px-5 py-2 rounded-xl bg-amber hover:bg-amber/90 text-ink font-bold text-sm transition">Perbarui Rumah</button>
            </div>
        </form>
    </div>
</div>
@endsection
