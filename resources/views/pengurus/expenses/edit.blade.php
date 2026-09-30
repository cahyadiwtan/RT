@extends('layouts.app')

@section('title', 'Edit Pengeluaran — Pengurus RT')

@section('content')
<div class="max-w-2xl">
    <a href="{{ route('pengurus.expenses.index') }}" class="text-sm text-warm/50 hover:text-warm transition">&larr; Kembali ke Pengeluaran</a>

    <div class="gelar mt-4 mb-1"><span class="gelar-label">Kas RT</span></div>
    <h1 class="text-2xl font-display text-warm tracking-tight mb-6">Edit Pengeluaran</h1>

    @if($errors->any())
        <div class="mb-6 rounded-2xl bg-clay/10 border border-clay/30 p-4 text-sm text-clay space-y-1">
            @foreach($errors->all() as $error)
                <div>• {{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form action="{{ route('pengurus.expenses.update', $expense) }}" method="POST" class="bg-surface border border-amber/10 rounded-2xl p-6 shadow-xl space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-semibold text-warm mb-1.5">Deskripsi Pengeluaran</label>
            <input type="text" name="description" value="{{ old('description', $expense->description) }}" required
                class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-amber/50">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-semibold text-warm mb-1.5">Jumlah (Rp)</label>
                <input type="number" name="amount" value="{{ old('amount', $expense->amount) }}" min="1" step="0.01" required
                    class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-amber/50">
            </div>
            <div>
                <label class="block text-sm font-semibold text-warm mb-1.5">Tanggal Pengeluaran</label>
                <input type="date" name="expense_date" value="{{ old('expense_date', $expense->expense_date->toDateString()) }}" required
                    class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-amber/50">
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-warm mb-1.5">Kategori</label>
            <select name="category_id" class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-amber/50">
                <option value="">— Tanpa Kategori —</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('category_id', $expense->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-semibold text-warm mb-1.5">Vendor / Penerima</label>
                <input type="text" name="vendor" value="{{ old('vendor', $expense->vendor) }}"
                    class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-amber/50">
            </div>
            <div>
                <label class="block text-sm font-semibold text-warm mb-1.5">No. Bukti / Kwitansi</label>
                <input type="text" name="receipt_number" value="{{ old('receipt_number', $expense->receipt_number) }}"
                    class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-amber/50">
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-warm mb-1.5">Catatan</label>
            <textarea name="notes" rows="3" class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-amber/50">{{ old('notes', $expense->notes) }}</textarea>
        </div>

        <button type="submit" class="w-full px-4 py-3 rounded-xl bg-amber hover:bg-amber/90 text-ink font-bold text-sm transition shadow-lg shadow-amber/20">
            Simpan Perubahan
        </button>
    </form>
</div>
@endsection
