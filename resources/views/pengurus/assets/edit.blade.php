@extends('layouts.app')

@section('title', 'Edit Aset — ' . $asset->asset_code)

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-display text-warm tracking-tight">Edit Aset</h1>
        <p class="text-xs text-warm/50 mt-1">Kode: <span class="font-mono text-jade">{{ $asset->asset_code }}</span></p>
    </div>

    <form action="{{ route('pengurus.assets.update', $asset) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-surface border border-amber/10 rounded-2xl p-6 space-y-4">
            <h2 class="text-sm font-bold text-warm/80 uppercase tracking-wider">Informasi Dasar</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-warm/50 mb-1">Nama Aset *</label>
                    <input type="text" name="name" value="{{ old('name', $asset->name) }}" required class="w-full px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-warm/50 mb-1">Kategori *</label>
                    <select name="category_id" required class="w-full px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $asset->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-warm/50 mb-1">Deskripsi</label>
                    <textarea name="description" rows="2" class="w-full px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm">{{ old('description', $asset->description) }}</textarea>
                </div>
            </div>
        </div>

        <div class="bg-surface border border-amber/10 rounded-2xl p-6 space-y-4">
            <h2 class="text-sm font-bold text-warm/80 uppercase tracking-wider">Stok & Kondisi</h2>
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-warm/50 mb-1">Jumlah *</label>
                    <input type="number" name="quantity" value="{{ old('quantity', $asset->quantity) }}" min="0" required class="w-full px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-warm/50 mb-1">Satuan</label>
                    <input type="text" name="unit" value="{{ old('unit', $asset->unit) }}" class="w-full px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-warm/50 mb-1">Kondisi *</label>
                    <select name="condition" required class="w-full px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm">
                        @foreach(['baik' => 'Baik', 'rusak_ringan' => 'Rusak Ringan', 'rusak_berat' => 'Rusak Berat', 'tidak_layak' => 'Tidak Layak', 'hilang' => 'Hilang'] as $val => $label)
                            <option value="{{ $val }}" {{ old('condition', $asset->condition) === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-warm/50 mb-1">Status *</label>
                    <select name="status" required class="w-full px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm">
                        @foreach(['tersedia' => 'Tersedia', 'dipinjam' => 'Dipinjam', 'dalam_perbaikan' => 'Perbaikan', 'tidak_aktif' => 'Nonaktif', 'hilang' => 'Hilang'] as $val => $label)
                            <option value="{{ $val }}" {{ old('status', $asset->status) === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-warm/50 mb-1">Lokasi</label>
                    <input type="text" name="location" value="{{ old('location', $asset->location) }}" class="w-full px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-warm/50 mb-1">Foto</label>
                    <input type="file" name="photo" accept="image/*" class="w-full px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:bg-jade/20 file:text-jade file:text-xs file:font-semibold">
                    @if($asset->photo)
                        <p class="text-xs text-warm/30 mt-1">Foto saat ini: {{ basename($asset->photo) }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="bg-surface border border-amber/10 rounded-2xl p-6 space-y-4">
            <h2 class="text-sm font-bold text-warm/80 uppercase tracking-wider">Informasi Akuisisi</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-warm/50 mb-1">Tanggal Akuisisi</label>
                    <input type="date" name="acquisition_date" value="{{ old('acquisition_date', $asset->acquisition_date?->format('Y-m-d')) }}" class="w-full px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-warm/50 mb-1">Sumber</label>
                    <input type="text" name="acquisition_source" value="{{ old('acquisition_source', $asset->acquisition_source) }}" class="w-full px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-warm/50 mb-1">Harga Akuisisi</label>
                    <input type="number" name="acquisition_price" value="{{ old('acquisition_price', $asset->acquisition_price) }}" min="0" class="w-full px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm">
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-warm/50 mb-1">Nilai Sekarang</label>
                    <input type="number" name="current_value" value="{{ old('current_value', $asset->current_value) }}" min="0" class="w-full px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-warm/50 mb-1">Merk</label>
                    <input type="text" name="brand" value="{{ old('brand', $asset->brand) }}" class="w-full px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-warm/50 mb-1">Model</label>
                    <input type="text" name="model" value="{{ old('model', $asset->model) }}" class="w-full px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm">
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-warm/50 mb-1">Serial Number</label>
                    <input type="text" name="serial_number" value="{{ old('serial_number', $asset->serial_number) }}" class="w-full px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-warm/50 mb-1">Catatan</label>
                    <input type="text" name="notes" value="{{ old('notes', $asset->notes) }}" class="w-full px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm">
                </div>
            </div>
        </div>

        <div class="flex gap-3 justify-end">
            <a href="{{ route('pengurus.assets.show', $asset) }}" class="px-5 py-2.5 rounded-xl bg-warm/5 hover:bg-warm/10 text-warm/80 text-sm font-semibold transition">Batal</a>
            <button type="submit" class="px-5 py-2.5 rounded-xl bg-amber hover:bg-amber/90 text-ink text-sm font-bold transition shadow-lg shadow-jade/20">Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection
