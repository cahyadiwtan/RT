@extends('layouts.app')

@section('title', 'Edit Event — Pengurus RT')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div>
        <div class="gelar mb-1"><span class="gelar-label">Event</span></div>
        <h1 class="text-2xl font-display text-warm tracking-tight">Edit: {{ $event->title }}</h1>
    </div>

    <form action="{{ route('pengurus.events.update', $event) }}" method="POST" class="bg-surface border border-amber/10 rounded-2xl p-6 space-y-5">
        @csrf
        @method('PUT')
        <div>
            <label class="block text-xs font-semibold text-warm/50 mb-1.5">Judul Event <span class="text-clay">*</span></label>
            <input type="text" name="title" value="{{ old('title', $event->title) }}" required
                class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-amber/50">
        </div>

        <div>
            <label class="block text-xs font-semibold text-warm/50 mb-1.5">Deskripsi</label>
            <textarea name="description" rows="3"
                class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-amber/50">{{ old('description', $event->description) }}</textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-warm/50 mb-1.5">Kategori</label>
                <input type="text" name="category" value="{{ old('category', $event->category) }}"
                    class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-amber/50">
            </div>
            <div>
                <label class="block text-xs font-semibold text-warm/50 mb-1.5">Lokasi</label>
                <input type="text" name="location" value="{{ old('location', $event->location) }}"
                    class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-amber/50">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-warm/50 mb-1.5">Tanggal Mulai <span class="text-clay">*</span></label>
                <input type="datetime-local" name="start_at" value="{{ old('start_at', $event->start_at->format('Y-m-d\TH:i')) }}" required
                    class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-amber/50">
            </div>
            <div>
                <label class="block text-xs font-semibold text-warm/50 mb-1.5">Tanggal Selesai <span class="text-clay">*</span></label>
                <input type="datetime-local" name="end_at" value="{{ old('end_at', $event->end_at->format('Y-m-d\TH:i')) }}" required
                    class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-amber/50">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-warm/50 mb-1.5">Batas Registrasi</label>
                <input type="datetime-local" name="registration_deadline" value="{{ old('registration_deadline', $event->registration_deadline?->format('Y-m-d\TH:i')) }}"
                    class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-amber/50">
            </div>
            <div>
                <label class="block text-xs font-semibold text-warm/50 mb-1.5">Batas Pembayaran</label>
                <input type="datetime-local" name="payment_deadline" value="{{ old('payment_deadline', $event->payment_deadline?->format('Y-m-d\TH:i')) }}"
                    class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-amber/50">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-warm/50 mb-1.5">Tipe Pendanaan <span class="text-clay">*</span></label>
                <select name="funding_type" required class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-amber/50">
                    <option value="free" {{ old('funding_type', $event->funding_type) === 'free' ? 'selected' : '' }}>Gratis</option>
                    <option value="resident_fee" {{ old('funding_type', $event->funding_type) === 'resident_fee' ? 'selected' : '' }}>Iuran Warga</option>
                    <option value="voluntary" {{ old('funding_type', $event->funding_type) === 'voluntary' ? 'selected' : '' }}>Sukarela</option>
                    <option value="rt_fund" {{ old('funding_type', $event->funding_type) === 'rt_fund' ? 'selected' : '' }}>Dana RT</option>
                    <option value="mixed" {{ old('funding_type', $event->funding_type) === 'mixed' ? 'selected' : '' }}>Campuran</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-warm/50 mb-1.5">Biaya Per Orang (Rp)</label>
                <input type="number" name="required_payment" value="{{ old('required_payment', $event->required_payment) }}" min="0"
                    class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-amber/50">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-warm/50 mb-1.5">Target Dana (Rp)</label>
                <input type="number" name="target_amount" value="{{ old('target_amount', $event->target_amount) }}" min="0"
                    class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-amber/50">
            </div>
            <div>
                <label class="block text-xs font-semibold text-warm/50 mb-1.5">Visibilitas Pembayaran <span class="text-clay">*</span></label>
                <select name="visibility" required class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-amber/50">
                    <option value="summary" {{ old('visibility', $event->visibility) === 'summary' ? 'selected' : '' }}>Ringkasan</option>
                    <option value="private" {{ old('visibility', $event->visibility) === 'private' ? 'selected' : '' }}>Privat</option>
                    <option value="names_only" {{ old('visibility', $event->visibility) === 'names_only' ? 'selected' : '' }}>Nama Saja</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-warm/50 mb-1.5">Status <span class="text-clay">*</span></label>
            <select name="status" required class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-amber/50">
                <option value="draft" {{ old('status', $event->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="published" {{ old('status', $event->status) === 'published' ? 'selected' : '' }}>Published</option>
                <option value="registration_open" {{ old('status', $event->status) === 'registration_open' ? 'selected' : '' }}>Registration Open</option>
                <option value="ongoing" {{ old('status', $event->status) === 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                <option value="completed" {{ old('status', $event->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ old('status', $event->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                <option value="closed" {{ old('status', $event->status) === 'closed' ? 'selected' : '' }}>Closed</option>
            </select>
        </div>

        <div class="flex gap-3 pt-2">
            <a href="{{ route('pengurus.events.show', $event) }}" class="px-4 py-2.5 rounded-xl bg-warm/5 text-warm/70 text-sm font-semibold hover:bg-warm/10 transition">Batal</a>
            <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl bg-amber hover:bg-amber/90 text-ink text-sm font-bold transition shadow-lg shadow-amber/20">Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection