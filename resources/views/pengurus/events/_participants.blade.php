<div class="bg-surface border border-amber/10 rounded-2xl p-5 space-y-4">
    <div class="flex items-center justify-between">
        <h3 class="text-sm font-semibold text-warm">Daftar Peserta</h3>
        <button @click="showAddParticipant = !showAddParticipant" class="px-3 py-1.5 rounded-lg bg-amber/10 text-amber text-xs font-semibold hover:bg-amber/20 transition">+ Tambah</button>
    </div>

    <div x-show="showAddParticipant" x-cloak class="bg-ink/50 rounded-xl p-4 border border-amber/10">
        <form action="{{ route('pengurus.events.participants.store', $event) }}" method="POST" class="space-y-3">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <select name="resident_id" required class="px-3 py-2 rounded-xl bg-surface border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-amber/50">
                    <option value="">Pilih Warga...</option>
                    @foreach(\App\Models\Resident::where('status_warga', 'aktif')->orderBy('nama_lengkap')->get() as $r)
                        <option value="{{ $r->id }}">{{ $r->nama_lengkap }} — {{ $r->house?->full_address }}</option>
                    @endforeach
                </select>
                <input type="number" name="payment_amount" value="{{ $event->required_payment }}" placeholder="Biaya"
                    class="px-3 py-2 rounded-xl bg-surface border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-amber/50">
                <button type="submit" class="px-4 py-2 rounded-xl bg-amber hover:bg-amber/90 text-ink text-sm font-bold transition">Simpan</button>
            </div>
        </form>
    </div>

    @php $ps = $summary['payment_summary']; @endphp
    <div class="grid grid-cols-4 gap-2 text-center text-xs">
        <div class="bg-jade/10 rounded-lg py-2"><span class="text-jade font-bold block text-lg">{{ $ps['paid'] }}</span><span class="text-warm/50">Lunas</span></div>
        <div class="bg-amber/10 rounded-lg py-2"><span class="text-amber font-bold block text-lg">{{ $ps['partial'] }}</span><span class="text-warm/50">Partial</span></div>
        <div class="bg-clay/10 rounded-lg py-2"><span class="text-clay font-bold block text-lg">{{ $ps['unpaid'] }}</span><span class="text-warm/50">Belum Bayar</span></div>
        <div class="bg-warm/5 rounded-lg py-2"><span class="text-warm/50 font-bold block text-lg">{{ $ps['waived'] }}</span><span class="text-warm/50">Bebas</span></div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-warm/80">
            <thead class="table-head text-xs uppercase tracking-wider text-warm/50 border-b border-amber/10">
                <tr>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Bayar</th>
                    <th class="px-4 py-3">Status Bayar</th>
                    <th class="px-4 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-amber/5">
                @forelse($event->participants()->with('resident')->orderBy('created_at', 'desc')->get() as $p)
                    <tr class="hover:bg-warm/5 transition">
                        <td class="px-4 py-3 font-semibold text-warm">{{ $p->resident?->nama_lengkap ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="text-xs px-2 py-0.5 rounded-full bg-warm/5 text-warm/50 capitalize">{{ $p->participation_status }}</span>
                        </td>
                        <td class="px-4 py-3 text-right font-mono text-xs text-warm/80">Rp{{ number_format($p->payment_amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">
                            @php
                                $psColors = ['paid' => 'jade', 'partial' => 'amber', 'unpaid' => 'clay', 'waived' => 'warm/30'];
                                $psColor = $psColors[$p->payment_status] ?? 'warm/30';
                            @endphp
                            <span class="text-xs px-2 py-0.5 rounded-full bg-{{ $psColor }}/10 text-{{ $psColor }} capitalize">{{ $p->payment_status }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <form action="{{ route('pengurus.events.participants.destroy', [$event, $p]) }}" method="POST" class="inline" onsubmit="return confirm('Hapus peserta ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-clay hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-warm/30">Belum ada peserta.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
