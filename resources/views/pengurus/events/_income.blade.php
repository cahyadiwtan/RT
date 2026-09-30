<div class="bg-surface border border-amber/10 rounded-2xl p-5 space-y-4">
    <h3 class="text-sm font-semibold text-warm">Pemasukan Tambahan</h3>

    <div class="bg-ink/50 rounded-xl p-4 border border-amber/10">
        <form action="{{ route('pengurus.events.incomes.store', $event) }}" method="POST" class="space-y-3">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <select name="income_type" required class="px-3 py-2 rounded-xl bg-surface border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-amber/50">
                    <option value="donation">Donasi</option>
                    <option value="sponsor">Sponsor</option>
                    <option value="rt_fund">Dana RT</option>
                    <option value="other">Lainnya</option>
                </select>
                <input type="text" name="description" placeholder="Deskripsi" required class="px-3 py-2 rounded-xl bg-surface border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-amber/50">
                <input type="number" name="amount" placeholder="Jumlah" required min="1" class="px-3 py-2 rounded-xl bg-surface border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-amber/50">
                <input type="date" name="income_date" value="{{ date('Y-m-d') }}" required class="px-3 py-2 rounded-xl bg-surface border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-amber/50">
            </div>
            <button type="submit" class="px-4 py-2 rounded-xl bg-jade hover:bg-jade/90 text-ink text-sm font-bold transition">Catat Pemasukan</button>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-warm/80">
            <thead class="table-head text-xs uppercase tracking-wider text-warm/50 border-b border-amber/10">
                <tr>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Tipe</th>
                    <th class="px-4 py-3">Deskripsi</th>
                    <th class="px-4 py-3 text-right">Jumlah</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-amber/5">
                @forelse($event->incomes()->orderBy('income_date', 'desc')->get() as $income)
                    <tr class="hover:bg-warm/5 transition">
                        <td class="px-4 py-3 text-warm/50 text-xs">{{ $income->income_date->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-warm/50 text-xs capitalize">{{ str_replace('_', ' ', $income->income_type) }}</td>
                        <td class="px-4 py-3 font-semibold text-warm text-sm">{{ $income->description }}</td>
                        <td class="px-4 py-3 text-right font-mono text-xs text-jade">Rp{{ number_format($income->amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right">
                            @if($income->linked_payment_id)
                                <span class="text-xs text-warm/30">Otomatis</span>
                            @elseif($income->status === 'active')
                                <form action="{{ route('pengurus.events.incomes.void', [$event, $income]) }}" method="POST" class="inline" onsubmit="return confirm('Batalkan pemasukan ini?')">
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
                    <tr><td colspan="5" class="px-4 py-6 text-center text-warm/30">Belum ada pemasukan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
