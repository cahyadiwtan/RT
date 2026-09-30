<div class="bg-surface border border-amber/10 rounded-2xl p-5 space-y-4">
    <h3 class="text-sm font-semibold text-warm">Anggaran</h3>

    <div class="bg-ink/50 rounded-xl p-4 border border-amber/10">
        <form action="{{ route('pengurus.events.budgets.store', $event) }}" method="POST" class="space-y-3">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <select name="category_id" required class="px-3 py-2 rounded-xl bg-surface border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-amber/50">
                    <option value="">Pilih Kategori...</option>
                    @foreach(\App\Models\EventExpenseCategory::where('is_active', true)->orderBy('name')->get() as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
                <input type="number" name="estimated_amount" placeholder="Estimasi (Rp)" required min="0" class="px-3 py-2 rounded-xl bg-surface border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-amber/50">
                <button type="submit" class="px-4 py-2 rounded-xl bg-amber hover:bg-amber/90 text-ink text-sm font-bold transition">Tambah Anggaran</button>
            </div>
        </form>
    </div>

    @php $budgets = $event->budgets()->with('category')->get(); @endphp
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-warm/80">
            <thead class="table-head text-xs uppercase tracking-wider text-warm/50 border-b border-amber/10">
                <tr>
                    <th class="px-4 py-3">Kategori</th>
                    <th class="px-4 py-3 text-right">Estimasi</th>
                    <th class="px-4 py-3 text-right">Realisasi</th>
                    <th class="px-4 py-3 text-right">Selisih</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-amber/5">
                @forelse($budgets as $b)
                    @php $selisih = (float)$b->estimated_amount - (float)$b->actual_amount; @endphp
                    <tr class="hover:bg-warm/5 transition">
                        <td class="px-4 py-3 font-semibold text-warm text-sm">{{ $b->category?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-right font-mono text-xs text-warm/80">Rp{{ number_format($b->estimated_amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-mono text-xs text-warm/80">Rp{{ number_format($b->actual_amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-mono text-xs {{ $selisih >= 0 ? 'text-jade' : 'text-clay' }}">Rp{{ number_format(abs($selisih), 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right">
                            <form action="{{ route('pengurus.events.budgets.destroy', [$event, $b]) }}" method="POST" class="inline" onsubmit="return confirm('Hapus anggaran ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-clay hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-warm/30">Belum ada anggaran.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
