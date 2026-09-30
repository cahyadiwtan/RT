<?php

namespace App\Services;

use App\Models\Resident;
use App\Models\RtProfile;
use Illuminate\Support\Carbon;

class PopulationReportService
{
    /**
     * Nama bulan dalam bahasa Indonesia untuk kop laporan.
     */
    public const MONTHS = [
        1 => 'JANUARI', 2 => 'FEBRUARI', 3 => 'MARET', 4 => 'APRIL',
        5 => 'MEI', 6 => 'JUNI', 7 => 'JULI', 8 => 'AGUSTUS',
        9 => 'SEPTEMBER', 10 => 'OKTOBER', 11 => 'NOVEMBER', 12 => 'DESEMBER',
    ];

    /**
     * Header 3 level laporan: grup kolom + sub-kolomnya.
     * Dipakai bersama oleh view HTML dan export CSV.
     */
    public const HEADER_GROUPS = [
        ['label' => 'LAHIR', 'keys' => ['lahir_l', 'lahir_p', 'lahir_jml']],
        ['label' => 'MENINGGAL', 'keys' => ['meninggal_l', 'meninggal_p', 'meninggal_jml']],
        ['label' => 'PINDAH', 'keys' => ['pindah_l', 'pindah_p', 'pindah_jml']],
        ['label' => 'DATANG', 'keys' => ['datang_l', 'datang_p', 'datang_jml']],
        ['label' => 'JUMLAH PENDUDUK BULAN INI', 'keys' => ['penduduk_l', 'penduduk_p', 'penduduk_jml']],
        ['label' => 'KEPALA KELUARGA', 'keys' => ['kk_dd', 'kk_ld', 'kk_jml']],
        ['label' => 'KARTU KELUARGA', 'keys' => ['kartu_kk_dd', 'kartu_kk_ld', 'kartu_kk_jml']],
        ['label' => 'KTP', 'keys' => ['ktp_dd', 'ktp_ld', 'ktp_jml']],
        ['label' => 'WAJIB MEMILIKI KTP', 'keys' => ['wajib_l', 'wajib_p', 'wajib_jml']],
        ['label' => 'TELAH MEMILIKI KTP', 'keys' => ['ada_ktp_l', 'ada_ktp_p', 'ada_ktp_jml']],
        ['label' => 'BELUM MEMILIKI KTP', 'keys' => ['belum_ktp_l', 'belum_ktp_p', 'belum_ktp_jml']],
    ];

    /**
     * Sub-label kolom: DD/LD untuk dokumen, L/P untuk kelompok gender.
     */
    public const SUBCOLUMNS = [
        'document' => ['DD', 'LD', 'JML'],
        'gender' => ['L', 'P', 'JML'],
    ];

    /**
     * Grup mana yang memakai sub-label DD/LD.
     */
    public const DOCUMENT_GROUPS = ['KEPALA KELUARGA', 'KARTU KELUARGA', 'KTP'];

    /**
     * Urutan 33 kolom numerik laporan (tanpa NO, URAIAN, KET).
     */
    public const COLUMNS = [
        'lahir_l', 'lahir_p', 'lahir_jml',
        'meninggal_l', 'meninggal_p', 'meninggal_jml',
        'pindah_l', 'pindah_p', 'pindah_jml',
        'datang_l', 'datang_p', 'datang_jml',
        'penduduk_l', 'penduduk_p', 'penduduk_jml',
        'kk_dd', 'kk_ld', 'kk_jml',
        'kartu_kk_dd', 'kartu_kk_ld', 'kartu_kk_jml',
        'ktp_dd', 'ktp_ld', 'ktp_jml',
        'wajib_l', 'wajib_p', 'wajib_jml',
        'ada_ktp_l', 'ada_ktp_p', 'ada_ktp_jml',
        'belum_ktp_l', 'belum_ktp_p', 'belum_ktp_jml',
    ];

    /**
     * Sub-label untuk sebuah grup kolom.
     *
     * @return array<int,string>
     */
    public static function subColumnsFor(string $label): array
    {
        return in_array($label, self::DOCUMENT_GROUPS, true)
            ? self::SUBCOLUMNS['document']
            : self::SUBCOLUMNS['gender'];
    }

    /**
     * Baris laporan siap tampil: BULAN INI / BULAN LALU x WNI / WNA / JUMLAH.
     *
     * @return array<int,array{period:string,nationality:string,values:array<string,int>}>
     */
    public static function buildRows(array $report): array
    {
        $rows = [];

        foreach (['BULAN INI', 'BULAN LALU'] as $period) {
            $source = $period === 'BULAN INI' ? $report['current'] : $report['previous'];

            foreach ([
                'WNI' => $source['wni'],
                'WNA' => $source['wna'],
                'JUMLAH' => $source['jumlah'],
            ] as $nationality => $values) {
                $rows[] = [
                    'period' => $period,
                    'nationality' => $nationality,
                    'values' => $values,
                ];
            }
        }

        return $rows;
    }

    public function getReport(?int $year = null, ?int $month = null): array
    {
        $now = now();
        $year = $year ?: (int) $now->format('Y');
        $month = $month ?: (int) $now->format('n');

        $current = Carbon::create($year, $month, 1)->startOfMonth();
        $previous = $current->copy()->subMonthNoOverflow();

        return [
            'year' => $year,
            'month' => $month,
            'bulan' => self::MONTHS[$month] ?? (string) $month,
            'tahun' => $year,
            'profile' => RtProfile::active(),
            'current' => $this->rowsForMonth($current),
            'previous' => $this->rowsForMonth($previous),
        ];
    }

    /**
     * Daftar tahun yang punya data, untuk form pilih periode.
     *
     * Tahun dihitung di PHP, bukan lewat YEAR() di SQL, supaya tetap jalan
     * di SQLite (testing) maupun MySQL (produksi).
     */
    public function availablePeriods(): array
    {
        $currentYear = (int) now()->format('Y');

        $years = Resident::query()
            ->whereNotNull('tanggal_tinggal')
            ->pluck('tanggal_tinggal')
            ->map(fn ($date) => (int) Carbon::parse($date)->format('Y'))
            ->push($currentYear)
            ->unique()
            ->sortDesc()
            ->values();

        return $years->map(fn ($year) => [
            'year' => $year,
            'months' => collect(self::MONTHS)
                ->map(fn ($label, $number) => compact('number', 'label'))
                ->values(),
        ])->all();
    }

    /**
     * Baris laporan (WNI / WNA / JUMLAH) untuk satu bulan.
     *
     * @return array{wni: array<string,int>, wna: array<string,int>, jumlah: array<string,int>}
     */
    private function rowsForMonth(Carbon $monthStart): array
    {
        $monthEnd = $monthStart->copy()->endOfMonth();

        $residents = Resident::query()
            ->get([
                'nomor_kk', 'nomor_ktp', 'tanggal_lahir', 'tanggal_tinggal',
                'tanggal_kematian', 'tanggal_pindah', 'jenis_kelamin',
                'status_warga', 'kewarganegaraan', 'domisili_asal',
                'hubungan_dalam_keluarga',
            ]);

        $inMonth = fn (?Carbon $date): bool => $date !== null
            && $date->year === $monthStart->year
            && $date->month === $monthStart->month;

        $byNationality = ['WNI' => [], 'WNA' => []];

        foreach ($residents as $resident) {
            $nationality = $resident->kewarganegaraan === 'WNA' ? 'WNA' : 'WNI';
            $byNationality[$nationality][] = $resident;
        }

        $rows = [];

        foreach ($byNationality as $nationality => $group) {
            $lahir = ['L' => 0, 'P' => 0];
            $meninggal = ['L' => 0, 'P' => 0];
            $pindah = ['L' => 0, 'P' => 0];
            $datang = ['L' => 0, 'P' => 0];
            $penduduk = ['L' => 0, 'P' => 0];
            $wajib = ['L' => 0, 'P' => 0];
            $adaKtp = ['L' => 0, 'P' => 0];

            $kepalaDd = 0;
            $kepalaLd = 0;
            $nomorKk = ['DD' => [], 'LD' => []];
            $nomorKtp = ['DD' => [], 'LD' => []];

            foreach ($group as $resident) {
                $gender = $resident->jenis_kelamin === 'P' ? 'P' : 'L';

                if ($inMonth($resident->tanggal_lahir)) {
                    $lahir[$gender]++;
                }

                if ($inMonth($resident->tanggal_kematian)) {
                    $meninggal[$gender]++;
                }

                if ($inMonth($resident->tanggal_pindah)) {
                    $pindah[$gender]++;
                }

                if ($inMonth($resident->tanggal_tinggal)) {
                    $datang[$gender]++;
                }

                if (! $this->isActiveAt($resident, $monthEnd)) {
                    continue;
                }

                $penduduk[$gender]++;

                if ($resident->hubungan_dalam_keluarga === 'kepala_keluarga') {
                    if ($resident->domisili_asal === 'LD') {
                        $kepalaLd++;
                    } else {
                        $kepalaDd++;
                    }
                }

                if (filled($resident->nomor_kk)) {
                    $nomorKk[$resident->domisili_asal][] = $resident->nomor_kk;
                }

                if (filled($resident->nomor_ktp)) {
                    $nomorKtp[$resident->domisili_asal][] = $resident->nomor_ktp;
                }

                if ($this->ageAt($resident->tanggal_lahir, $monthEnd) >= 17) {
                    $wajib[$gender]++;

                    if (filled($resident->nomor_ktp)) {
                        $adaKtp[$gender]++;
                    }
                }
            }

            $kartuKkDd = count(array_unique($nomorKk['DD']));
            $kartuKkLd = count(array_unique($nomorKk['LD']));
            $ktpDd = count(array_unique($nomorKtp['DD']));
            $ktpLd = count(array_unique($nomorKtp['LD']));
            $belumKtpL = $wajib['L'] - $adaKtp['L'];
            $belumKtpP = $wajib['P'] - $adaKtp['P'];

            $rows[$nationality] = [
                'lahir_l' => $lahir['L'],
                'lahir_p' => $lahir['P'],
                'lahir_jml' => $lahir['L'] + $lahir['P'],
                'meninggal_l' => $meninggal['L'],
                'meninggal_p' => $meninggal['P'],
                'meninggal_jml' => $meninggal['L'] + $meninggal['P'],
                'pindah_l' => $pindah['L'],
                'pindah_p' => $pindah['P'],
                'pindah_jml' => $pindah['L'] + $pindah['P'],
                'datang_l' => $datang['L'],
                'datang_p' => $datang['P'],
                'datang_jml' => $datang['L'] + $datang['P'],
                'penduduk_l' => $penduduk['L'],
                'penduduk_p' => $penduduk['P'],
                'penduduk_jml' => $penduduk['L'] + $penduduk['P'],
                'kk_dd' => $kepalaDd,
                'kk_ld' => $kepalaLd,
                'kk_jml' => $kepalaDd + $kepalaLd,
                'kartu_kk_dd' => $kartuKkDd,
                'kartu_kk_ld' => $kartuKkLd,
                'kartu_kk_jml' => $kartuKkDd + $kartuKkLd,
                'ktp_dd' => $ktpDd,
                'ktp_ld' => $ktpLd,
                'ktp_jml' => $ktpDd + $ktpLd,
                'wajib_l' => $wajib['L'],
                'wajib_p' => $wajib['P'],
                'wajib_jml' => $wajib['L'] + $wajib['P'],
                'ada_ktp_l' => $adaKtp['L'],
                'ada_ktp_p' => $adaKtp['P'],
                'ada_ktp_jml' => $adaKtp['L'] + $adaKtp['P'],
                'belum_ktp_l' => $belumKtpL,
                'belum_ktp_p' => $belumKtpP,
                'belum_ktp_jml' => $belumKtpL + $belumKtpP,
            ];
        }

        $rows['jumlah'] = [];
        foreach (self::COLUMNS as $column) {
            $rows['jumlah'][$column] = $rows['WNI'][$column] + $rows['WNA'][$column];
        }

        return [
            'wni' => $rows['WNI'],
            'wna' => $rows['WNA'],
            'jumlah' => $rows['jumlah'],
        ];
    }

    /**
     * Warga berstatus aktif pada akhir bulan yang dipilih.
     */
    private function isActiveAt(Resident $resident, Carbon $monthEnd): bool
    {
        if ($resident->status_warga !== 'aktif') {
            return false;
        }

        if ($resident->tanggal_tinggal && $resident->tanggal_tinggal->gt($monthEnd)) {
            return false;
        }

        if ($resident->tanggal_pindah && $resident->tanggal_pindah->lte($monthEnd)) {
            return false;
        }

        if ($resident->tanggal_kematian && $resident->tanggal_kematian->lte($monthEnd)) {
            return false;
        }

        return true;
    }

    /**
     * Usia warga pada tanggal acuan, null bila tanggal lahir belum diisi.
     */
    private function ageAt(?Carbon $tanggalLahir, Carbon $reference): ?int
    {
        if (! $tanggalLahir) {
            return null;
        }

        $age = (int) $reference->format('Y') - (int) $tanggalLahir->format('Y');

        if ($reference->format('m-d') < $tanggalLahir->format('m-d')) {
            $age--;
        }

        return max(0, $age);
    }
}
