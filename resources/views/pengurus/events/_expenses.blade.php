<div class="bg-surface border border-amber/10 rounded-2xl p-5 space-y-4">
    <h3 class="text-sm font-semibold text-warm">Pengeluaran</h3>

    <div class="bg-ink/50 rounded-xl p-4 border border-amber/10">
        <form action="{{ route('pengurus.events.expenses.store', $event) }}" method="POST" class="space-y-3">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <select name="category_id" required class="px-3 py-2 rounded-xl bg-surface border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-amber/50">
                    <option value="">Pilih Kategori...</option>
                    @foreach(\App\Models\EventExpenseCategory::where('is_active', true)->orderBy('name')->get() as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
                <input type="text" name="description" placeholder="Deskripsi" required class="px-3 py-2 rounded-xl bg-surface border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-amber/50">
                <input type="number" name="amount" placeholder="Jumlah" required min="1" class="px-3 py-2 rounded-xl bg-surface border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-amber/50">
                <input type="date" name="expense_date" value="{{ date('Y-m-d') }}" required class="px-3 py-2 rounded-xl bg-surface border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-amber/50">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <input type="text" name="vendor" placeholder="Vendor / Toko (opsional)" class="px-3 py-2 rounded-xl bg-surface border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-amber/50">
                <input type="text" name="receipt_number" placeholder="No. Nota (opsional)" class="px-3 py-2 rounded-xl bg-surface border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-amber/50">
            </div>
            <button type="submit" class="px-4 py-2 rounded-xl bg-clay hover:bg-clay/90 text-ink text-sm font-bold transition">Catat Pengeluaran</button>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-warm/80">
            <thead class="table-head text-xs uppercase tracking-wider text-warm/50 border-b border-amber/10">
                <tr>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Kategori</th>
                    <th class="px-4 py-3">Deskripsi</th>
                    <th class="px-4 py-3 text-right">Jumlah</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-amber/5">
                @forelse($event->expenses()->with('category')->orderBy('expense_date', 'desc')->get() as $expense)
                    <tr class="hover:bg-warm/5 transition">
                        <td class="px-4 py-3 text-warm/50 text-xs">{{ $expense->expense_date->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-warm/50 text-xs">{{ $expense->category?->name ?? '-' }}</td>
                        <td class="px-4 py-3 font-semibold text-warm text-sm">{{ $expense->description }}</td>
                        <td class="px-4 py-3 text-right font-mono text-xs text-clay">Rp{{ number_format($expense->amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right">
                            @if($expense->status === 'active')
                                <form action="{{ route('pengurus.events.expenses.void', [$event, $expense]) }}" method="POST" class="inline" onsubmit="return confirm('Batalkan pengeluaran ini?')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-xs text-clay hover:underline">Batalkan</button>
                                </form>
                            @else
                                <span class="text-xs text-clay/50">Dibatalkan</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-warm/30">Belum ada pengeluaran.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
