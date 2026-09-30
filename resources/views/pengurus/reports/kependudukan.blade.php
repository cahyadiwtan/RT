@extends('layouts.app')

@section('title', 'Rekapitulasi Registrasi Kependudukan - Pengurus RT')

@section('content')
<div class="space-y-6">
    <div class="no-print">
        <h1 class="text-2xl font-display text-warm tracking-tight">Rekapitulasi Registrasi Kependudukan</h1>
        <p class="text-xs text-warm/50 mt-1">Format administrasi RT/RW, digenerate otomatis per bulan</p>
    </div>

    <!-- Tabs -->
    <div class="no-print flex flex-wrap gap-1 bg-surface border border-amber/10 rounded-2xl p-1 w-fit">
        <a href="{{ route('pengurus.reports.iuran') }}" class="px-4 py-2 rounded-xl text-warm/50 hover:text-warm hover:bg-warm/5 text-sm font-medium transition">Iuran</a>
        <a href="{{ route('pengurus.reports.payments') }}" class="px-4 py-2 rounded-xl text-warm/50 hover:text-warm hover:bg-warm/5 text-sm font-medium transition">Pembayaran</a>
        <a href="{{ route('pengurus.reports.arrears') }}" class="px-4 py-2 rounded-xl text-warm/50 hover:text-warm hover:bg-warm/5 text-sm font-medium transition">Tunggakan</a>
        <a href="{{ route('pengurus.reports.deposits') }}" class="px-4 py-2 rounded-xl text-warm/50 hover:text-warm hover:bg-warm/5 text-sm font-medium transition">Deposit</a>
        <a href="{{ route('pengurus.reports.cashflow') }}" class="px-4 py-2 rounded-xl text-warm/50 hover:text-warm hover:bg-warm/5 text-sm font-medium transition">Arus Kas</a>
        <a href="{{ route('pengurus.reports.assets') }}" class="px-4 py-2 rounded-xl text-warm/50 hover:text-warm hover:bg-warm/5 text-sm font-medium transition">Inventaris</a>
        <a href="{{ route('pengurus.reports.kependudukan') }}" class="px-4 py-2 rounded-xl bg-jade/10 text-jade font-semibold text-sm">Kependudukan</a>
    </div>

    <!-- Filter -->
    <div class="no-print bg-surface border border-amber/10 rounded-2xl p-4">
        <form action="{{ route('pengurus.reports.kependudukan') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3 flex-wrap">
            <select name="month" class="w-full sm:w-48 px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm">
                @foreach ($periods as $period)
                    @foreach ($period['months'] as $monthOption)
                        <option value="{{ $monthOption['number'] }}" data-year="{{ $period['year'] }}" {{ (int) request('month', $report['month']) === $monthOption['number'] ? 'selected' : '' }}>{{ $monthOption['label'] }}</option>
                    @endforeach
                @endforeach
            </select>
            <select name="year" class="w-full sm:w-32 px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm">
                @foreach ($periods as $period)
                    <option value="{{ $period['year'] }}" {{ $report['year'] === $period['year'] ? 'selected' : '' }}>{{ $period['year'] }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 rounded-xl bg-warm/10 hover:bg-warm/15 text-warm text-sm font-semibold transition">Tampilkan</button>
            <a href="{{ route('pengurus.reports.kependudukan') }}" class="px-4 py-2 rounded-xl text-warm/50 hover:text-warm text-sm">Reset</a>

            <div class="sm:ml-auto flex items-center gap-2">
                <a href="{{ route('pengurus.rt-profile.index') }}" class="px-4 py-2 rounded-xl text-warm/50 hover:text-warm text-sm">Identitas RT</a>
                <a href="{{ route('pengurus.reports.export', ['type' => 'kependudukan', 'year' => $report['year'], 'month' => $report['month']]) }}" class="px-4 py-2 rounded-xl bg-jade/10 hover:bg-jade/15 text-jade text-sm font-semibold transition">Export CSV</a>
                <button type="button" onclick="window.print()" class="px-4 py-2 rounded-xl bg-warm text-ink text-sm font-semibold transition">Cetak</button>
            </div>
        </form>
    </div>

    @if (! $report['profile'])
        <div class="no-print bg-amber/10 border border-amber/30 rounded-2xl p-4 text-sm text-warm">
            Identitas RT belum diisi, laporan memakai nilai bawaan.
            <a href="{{ route('pengurus.rt-profile.index') }}" class="underline font-semibold">Lengkapi sekarang</a>.
        </div>
    @endif

    <!-- Sheet -->
    <div class="bg-white text-black rounded-2xl shadow-xl overflow-hidden print:shadow-none print:rounded-none">
        <div class="p-6 sm:p-10 font-serif">
            <p class="text-center font-bold tracking-wide">LAPORAN BULANAN RUKUN TETANGGA</p>
            <p class="text-center font-bold tracking-wide mt-0.5">REKAPITULASI REGISTRASI KEPENDUDUKAN {{ $report['profile']?->label ?? 'RT.000 RW.000' }}</p>
            <p class="text-center font-bold tracking-wide mt-0.5">{{ strtoupper($report['profile']?->kelurahan ?? '-') }} KECAMATAN {{ strtoupper($report['profile']?->kecamatan ?? '-') }}</p>
            <p class="text-center font-bold tracking-wide mt-0.5">{{ strtoupper($report['profile']?->kota ?? '-') }} PROVINSI {{ strtoupper($report['profile']?->provinsi ?? '-') }}</p>
            <p class="text-center font-bold tracking-wide mt-3">3. LAPORAN REKAPITULASI REGISTRASI KEPENDUDUKAN</p>

            <div class="mt-4 text-sm space-y-0.5">
                <p>BULAN,: {{ $report['bulan'] }}</p>
                <p>TAHUN,: {{ $report['tahun'] }}</p>
            </div>

            <div class="mt-5 overflow-x-auto">
                <table class="w-full border-collapse text-[10px] sm:text-xs whitespace-nowrap">
                    <thead>
                        <tr>
                            <th rowspan="2" class="border border-black/70 px-1 py-1 w-8 font-semibold">NO.</th>
                            <th rowspan="2" class="border border-black/70 px-2 py-1 text-left font-semibold">URAIAN</th>
                            @foreach (\App\Services\PopulationReportService::HEADER_GROUPS as $group)
                                <th colspan="3" class="border border-black/70 px-1 py-1 text-center font-semibold leading-tight">{{ $group['label'] }}</th>
                            @endforeach
                            <th rowspan="2" class="border border-black/70 px-1 py-1 w-10 font-semibold">KET</th>
                        </tr>
                        <tr>
                            @foreach (\App\Services\PopulationReportService::HEADER_GROUPS as $group)
                                @foreach (\App\Services\PopulationReportService::subColumnsFor($group['label']) as $sub)
                                    <th class="border border-black/70 px-1 py-0.5 text-center font-medium">{{ $sub }}</th>
                                @endforeach
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (\App\Services\PopulationReportService::buildRows($report) as $index => $row)
                            <tr class="{{ $row['period'] === 'BULAN LALU' ? 'border-t-2 border-black/60' : '' }}">
                                <td class="border border-black/70 px-1 py-1 text-center">{{ $index + 1 }}</td>
                                <td class="border border-black/70 px-2 py-1 {{ $row['nationality'] === 'JUMLAH' ? 'font-bold' : '' }}">
                                    {{ $row['nationality'] === 'WNI' ? $row['period'].' - ' : '' }}{{ $row['nationality'] }}
                                </td>
                                @foreach ($row['values'] as $column => $value)
                                    <td class="border border-black/70 px-1 py-1 text-center tabular-nums">{{ $value }}</td>
                                @endforeach
                                <td class="border border-black/70 px-1 py-1"></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6 text-xs space-y-0.5">
                <p class="font-semibold">Keterangan:</p>
                <p>L,: Laki-laki</p>
                <p>P,: Perempuan</p>
                <p>DD,: Dalam Daerah (Kota Tangerang)</p>
                <p>LD,: Luar Daerah (di luar Kota Tangerang)</p>
            </div>

            <div class="mt-10 flex justify-end">
                <div class="text-center text-xs">
                    <p class="font-semibold">KETUA {{ $report['profile']?->label ?? 'RT.000 RW.000' }}</p>
                    <p class="mt-16 underline font-semibold">{{ $report['profile']?->ketua_rt ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    @media print {
        .no-print { display: none !important; }
        body { background: #fff !important; }
        @page { size: landscape; margin: 10mm; }
        table { page-break-inside: auto; }
        tr { page-break-inside: avoid; }
    }
</style>
@endpush
