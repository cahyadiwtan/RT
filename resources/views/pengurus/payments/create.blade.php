@extends('layouts.app')

@section('title', 'Catat Pembayaran Baru — Pengurus RT')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="bg-surface border border-amber/10 rounded-2xl p-6 shadow-xl">
        <h1 class="text-xl font-bold text-warm mb-6">Catat Pembayaran Iuran Warga</h1>

        <form action="{{ route('pengurus.payments.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">Rumah / Pembayar</label>
                <select name="house_id" required class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:ring-2 focus:ring-jade/50">
                    <option value="">-- Pilih Rumah --</option>
                    @foreach($houses as $h)
                        <option value="{{ $h->id }}" {{ old('house_id') == $h->id ? 'selected' : '' }}>
                            {{ $h->full_address }} ({{ $h->residents->pluck('nama_lengkap')->implode(', ') ?: 'Tanpa nama' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">Tanggal Pembayaran</label>
                    <input type="date" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" required
                        class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:ring-2 focus:ring-jade/50">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">Nominal Diterima (Rp)</label>
                    <input type="number" name="amount" value="{{ old('amount', 30000) }}" required step="1000" min="1000" placeholder="30000"
                        class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:ring-2 focus:ring-jade/50 font-bold text-jade">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">Metode Pembayaran</label>
                    <select name="payment_method" required class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:ring-2 focus:ring-jade/50">
                        <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Tunai / Cash</option>
                        <option value="transfer" {{ old('payment_method') === 'transfer' ? 'selected' : '' }}>Transfer Bank</option>
                        <option value="other" {{ old('payment_method') === 'other' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">No Referensi / Struk (Opsional)</label>
                    <input type="text" name="reference_number" value="{{ old('reference_number') }}" placeholder="TRX-12345"
                        class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:ring-2 focus:ring-jade/50">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">Catatan Pembayaran (Opsional)</label>
                <textarea name="notes" rows="2" class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:ring-2 focus:ring-jade/50" placeholder="Catatan opsional pengurus...">{{ old('notes') }}</textarea>
            </div>

            <div class="p-3 bg-jade/10 border border-jade/30 rounded-xl text-xs text-jade/80">
                ⚡ <strong>Aturan Alokasi FIFO:</strong> Pembayaran ini secara otomatis akan memotong/melunasi tagihan paling lama terlebih dahulu. Jika ada kelebihan pembayaran, sisa uang disimpan sebagai <strong>Saldo Kredit / Deposit</strong> rumah tersebut.
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-amber/10">
                <a href="{{ route('pengurus.payments.index') }}" class="px-4 py-2 rounded-xl bg-warm/5 text-warm/80 text-sm font-semibold hover:bg-warm/10 transition">Batal</a>
                <button type="submit" class="px-5 py-2 rounded-xl bg-jade hover:bg-jade/90 text-ink font-bold text-sm transition">Simpan & Alokasi FIFO</button>
            </div>
        </form>
    </div>
</div>
@endsection
