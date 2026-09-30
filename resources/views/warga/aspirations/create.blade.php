@extends('layouts.app')

@section('title', 'Kirim Aspirasi Baru — Sistem RT')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('warga.aspirations.index') }}" class="w-10 h-10 rounded-xl bg-surface hover:bg-warm/10 border border-amber/10 flex items-center justify-center text-warm/80 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-display text-warm tracking-tight">Kirim Aspirasi Baru</h1>
            <p class="text-xs text-warm/50 mt-1">Sampaikan aspirasi, saran, keluhan, atau usulan Anda.</p>
        </div>
    </div>

    <!-- Form Container -->
    <div class="bg-surface border border-amber/10 rounded-2xl p-6 shadow-xl">
        <form action="{{ route('warga.aspirations.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <!-- Kategori -->
            <div>
                <label for="category" class="block text-sm font-semibold text-warm/80 mb-2">Kategori Aspirasi</label>
                <select id="category" name="category" class="bg-ink/60 border border-amber/10 rounded-xl px-4 py-2.5 text-warm focus:outline-none focus:border-jade transition w-full" required>
                    <option value="" disabled selected>-- Pilih Kategori --</option>
                    <option value="keamanan" {{ old('category') === 'keamanan' ? 'selected' : '' }}>Keamanan</option>
                    <option value="kebersihan" {{ old('category') === 'kebersihan' ? 'selected' : '' }}>Kebersihan</option>
                    <option value="fasilitas" {{ old('category') === 'fasilitas' ? 'selected' : '' }}>Fasilitas</option>
                    <option value="lingkungan" {{ old('category') === 'lingkungan' ? 'selected' : '' }}>Lingkungan</option>
                    <option value="sosial" {{ old('category') === 'sosial' ? 'selected' : '' }}>Sosial</option>
                    <option value="lainnya" {{ old('category') === 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                </select>
            </div>

            <!-- Judul -->
            <div>
                <label for="title" class="block text-sm font-semibold text-warm/80 mb-2">Judul Aspirasi</label>
                <input type="text" id="title" name="title" value="{{ old('title') }}" placeholder="Contoh: Lampu Penerangan Jalan Blok A Mati" class="bg-ink/60 border border-amber/10 rounded-xl px-4 py-2.5 text-warm placeholder-warm/30 focus:outline-none focus:border-jade transition w-full" required>
            </div>

            <!-- Deskripsi -->
            <div>
                <label for="description" class="block text-sm font-semibold text-warm/80 mb-2">Deskripsi Lengkap</label>
                <textarea id="description" name="description" rows="5" placeholder="Tuliskan keluhan atau saran Anda secara mendetail di sini..." class="bg-ink/60 border border-amber/10 rounded-xl px-4 py-2.5 text-warm placeholder-warm/30 focus:outline-none focus:border-jade transition w-full" required>{{ old('description') }}</textarea>
            </div>

            <!-- Lampiran Berkas -->
            <div>
                <label for="attachment" class="block text-sm font-semibold text-warm/80 mb-2">Lampiran Berkas (Opsional)</label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-amber/10 border-dashed rounded-xl bg-ink/40 hover:bg-ink/60 transition">
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-12 w-12 text-warm/50" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="flex text-sm text-warm/50">
                            <label for="attachment" class="relative cursor-pointer rounded-md font-semibold text-jade hover:text-jade/80 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-jade/50">
                                <span>Unggah Berkas</span>
                                <input id="attachment" name="attachment" type="file" class="sr-only">
                            </label>
                            <p class="pl-1">atau seret dan lepas berkas ke sini</p>
                        </div>
                        <p class="text-xs text-warm/30">Gambar (JPG, JPEG, PNG) atau PDF hingga 2MB</p>
                    </div>
                </div>
                <div id="file-name-preview" class="text-xs text-jade font-semibold mt-2 hidden"></div>
            </div>

            <!-- Submit Button -->
            <div class="pt-4 border-t border-amber/10 flex items-center justify-end gap-3">
                <a href="{{ route('warga.aspirations.index') }}" class="px-5 py-2.5 rounded-xl bg-warm/10 hover:bg-warm/10 border border-amber/20 text-warm font-semibold text-sm transition">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-jade to-jade text-ink font-bold text-sm shadow-lg shadow-jade/20 hover:from-jade/90 hover:to-jade/90 transition">
                    Kirim Aspirasi
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Tampilkan nama file yang dipilih
    document.getElementById('attachment').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('file-name-preview');
        if (file) {
            preview.textContent = 'Berkas terpilih: ' + file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';
            preview.classList.remove('hidden');
        } else {
            preview.classList.add('hidden');
        }
    });
</script>
@endsection
