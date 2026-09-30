<div class="bg-surface border border-amber/10 rounded-2xl p-5 space-y-4">
    <div class="flex items-center justify-between">
        <h3 class="text-sm font-semibold text-warm">Pembayaran Peserta</h3>
    </div>

    <div class="bg-ink/50 rounded-xl p-4 border border-amber/10">
        <form action="{{ route('pengurus.events.payments.store', $event) }}" method="POST" class="space-y-3">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <select name="event_participant_id" required class="px-3 py-2 rounded-xl bg-surface border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-amber/50">
                    <option value="">Pilih Peserta...</option>
                    @foreach($event->participants()->with('resident')->get() as $p)
                        <option value="{{ $p->id }}">{{ $p->resident?->nama_lengkap }} — Rp{{ number_format($p->payment_amount, 0, ',', '.') }}</option>
                    @endforeach
                </select>
                <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required class="px-3 py-2 rounded-xl bg-surface border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-amber/50">
                <input type="number" name="amount" placeholder="Jumlah Bayar" required min="1" class="px-3 py-2 rounded-xl bg-surface border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-amber/50">
                <button type="submit" class="px-4 py-2 rounded-xl bg-jade hover:bg-jade/90 text-ink text-sm font-bold transition">Catat Bayar</button>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-warm/80">
            <thead class="table-head text-xs uppercase tracking-wider text-warm/50 border-b border-amber/10">
                <tr>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Peserta</th>
                    <th class="px-4 py-3 text-right">Jumlah</th>
                    <th class="px-4 py-3">Metode</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-amber/5">
                @forelse($event->eventPayments()->with('participant.resident')->orderBy('payment_date', 'desc')->get() as $payment)
                    <tr class="hover:bg-warm/5 transition">
                        <td class="px-4 py-3 text-warm/50 text-xs">{{ $payment->payment_date->format('d M Y') }}</td>
                        <td class="px-4 py-3 font-semibold text-warm text-sm">{{ $payment->participant?->resident?->nama_lengkap ?? '-' }}</td>
                        <td class="px-4 py-3 text-right font-mono text-xs text-jade">Rp{{ number_format($payment->amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-warm/50 text-xs capitalize">{{ $payment->payment_method ?: '-' }}</td>
                        <td class="px-4 py-3 text-right">
                            @if($payment->status === 'active')
                                <form action="{{ route('pengurus.events.payments.void', [$event, $payment]) }}" method="POST" class="inline" onsubmit="return confirm('Batalkan pembayaran ini?')">
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
                    <tr><td colspan="5" class="px-4 py-6 text-center text-warm/30">Belum ada pembayaran.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
