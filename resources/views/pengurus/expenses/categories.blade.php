@extends('layouts.app')

@section('title', 'Kategori Pengeluaran — Pengurus RT')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between gap-4">
        <div>
            <div class="gelar mb-1"><span class="gelar-label">Kas RT</span></div>
            <h1 class="text-2xl font-display text-warm tracking-tight">Kategori Pengeluaran</h1>
            <p class="text-xs text-warm/50 mt-1">Kelola kategori pengeluaran kas umum RT</p>
        </div>
        <button onclick="document.getElementById('addCategoryModal').classList.remove('hidden')" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber hover:bg-amber/90 text-ink font-bold text-sm transition shadow-lg shadow-amber/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Kategori
        </button>
    </div>

    <div class="bg-surface border border-amber/10 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-warm/80">
                <thead class="table-head text-xs uppercase tracking-wider text-warm/50 border-b border-amber/10">
                    <tr>
                        <th class="px-6 py-4">Nama</th>
                        <th class="px-6 py-4">Deskripsi</th>
                        <th class="px-6 py-4 text-center">Jumlah Pengeluaran</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-amber/5">
                    @forelse($categories as $category)
                        <tr class="hover:bg-warm/5 transition">
                            <td class="px-6 py-4 font-semibold text-warm">{{ $category->name }}</td>
                            <td class="px-6 py-4 text-warm/50">{{ $category->description ?? '-' }}</td>
                            <td class="px-6 py-4 text-center font-mono font-bold text-clay">{{ $category->expenses_count }}</td>
                            <td class="px-6 py-4 text-center">
                                @if($category->is_active)
                                    <span class="px-2.5 py-1 rounded-full bg-jade/10 border border-jade/30 text-jade text-xs font-semibold">Aktif</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full bg-warm/10 border border-warm/30 text-warm/50 text-xs font-semibold">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-3">
                                <button type="button" onclick="openEdit({{ $category->id }}, '{{ $category->name }}', {{ json_encode($category->description) }}, {{ $category->is_active ? 'true' : 'false' }})" class="text-xs text-amber hover:underline font-semibold">Edit</button>
                                <form action="{{ route('pengurus.expense-categories.destroy', $category) }}" method="POST" class="inline" onsubmit="return confirm('Hapus kategori ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-clay hover:underline font-semibold">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-warm/30">Belum ada kategori pengeluaran.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div id="addCategoryModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" @keydown.escape.window="document.getElementById('addCategoryModal').classList.add('hidden')">
    <div class="bg-surface border border-amber/10 rounded-2xl p-6 w-full max-w-md shadow-2xl" @click.stop>
        <h2 class="text-lg font-bold text-warm mb-4">Tambah Kategori Baru</h2>
        <form action="{{ route('pengurus.expense-categories.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-warm/50 mb-1">Nama Kategori</label>
                <input type="text" name="name" required class="w-full px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm" placeholder="Contoh: Kebersihan">
            </div>
            <div>
                <label class="block text-xs font-semibold text-warm/50 mb-1">Deskripsi</label>
                <textarea name="description" rows="2" class="w-full px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm" placeholder="Opsional"></textarea>
            </div>
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="document.getElementById('addCategoryModal').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-warm/5 hover:bg-warm/10 text-warm/80 text-sm font-semibold transition">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-amber hover:bg-amber/90 text-ink text-sm font-bold transition">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Category Modal -->
<div id="editCategoryModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" @keydown.escape.window="document.getElementById('editCategoryModal').classList.add('hidden')">
    <div class="bg-surface border border-amber/10 rounded-2xl p-6 w-full max-w-md shadow-2xl" @click.stop>
        <h2 class="text-lg font-bold text-warm mb-4">Edit Kategori</h2>
        <form action="" method="POST" id="editCategoryForm" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-semibold text-warm/50 mb-1">Nama Kategori</label>
                <input type="text" name="name" id="editName" required class="w-full px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-warm/50 mb-1">Deskripsi</label>
                <textarea name="description" id="editDescription" rows="2" class="w-full px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm"></textarea>
            </div>
            <div>
                <label class="flex items-center gap-2 text-sm font-semibold text-warm/50">
                    <input type="checkbox" name="is_active" id="editActive" value="1" class="rounded border-amber/30 text-amber focus:ring-amber/50">
                    Kategori aktif
                </label>
            </div>
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="document.getElementById('editCategoryModal').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-warm/5 hover:bg-warm/10 text-warm/80 text-sm font-semibold transition">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-amber hover:bg-amber/90 text-ink text-sm font-bold transition">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
@endsection

@push('scripts')
<script>
    function openEdit(id, name, description, isActive) {
        const form = document.getElementById('editCategoryForm');
        form.action = "{{ route('pengurus.expense-categories.index') }}".replace('expense-categories', 'expense-categories/' + id);
        document.getElementById('editName').value = name;
        document.getElementById('editDescription').value = description || '';
        document.getElementById('editActive').checked = isActive;
        document.getElementById('editCategoryModal').classList.remove('hidden');
    }
</script>
@endpush