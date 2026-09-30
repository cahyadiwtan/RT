<?php

namespace Tests\Feature;

use App\Models\Resident;
use App\Models\RtProfile;
use App\Models\User;
use App\Services\PopulationReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PopulationReportTest extends TestCase
{
    use RefreshDatabase;

    private User $pengurus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pengurus = $this->createUser('pengurus', 'pengurus01');

        RtProfile::create([
            'rt' => '008',
            'rw' => '024',
            'kelurahan' => 'Gebang Raya',
            'kecamatan' => 'Periuk',
            'kota' => 'Tangerang',
            'provinsi' => 'Banten',
            'ketua_rt' => 'SIMON LIU',
            'is_active' => true,
        ]);
    }

    private function createUser(string $role, string $username): User
    {
        return User::create([
            'username' => $username,
            'password' => bcrypt('password'),
            'role' => $role,
        ]);
    }

    private function resident(array $attributes = []): Resident
    {
        static $seq = 0;
        $seq++;

        return Resident::create(array_merge([
            'nik' => str_pad((string) (3201010000000000 + $seq), 16, '0', STR_PAD_LEFT),
            'nama_lengkap' => 'Warga '.$seq,
            'jenis_kelamin' => 'L',
            'status_warga' => 'aktif',
            'kewarganegaraan' => 'WNI',
            'domisili_asal' => 'DD',
            'hubungan_dalam_keluarga' => 'anak',
            'tanggal_lahir' => '2000-01-01',
        ], $attributes));
    }

    public function test_pengurus_can_open_report(): void
    {
        $response = $this->actingAs($this->pengurus)
            ->get(route('pengurus.reports.kependudukan'));

        $response->assertOk();
        $response->assertSee('REKAPITULASI REGISTRASI KEPENDUDUKAN RT.008 RW.024');
        $response->assertSee('GEBANG RAYA KECAMATAN PERIUK');
        $response->assertSee('SIMON LIU');
        $response->assertSee('BULAN INI - WNI');
        $response->assertSee('BULAN LALU - WNI');
    }

    public function test_report_uses_selected_month(): void
    {
        $this->resident(['tanggal_lahir' => '2026-03-10']);

        $service = app(PopulationReportService::class);
        $report = $service->getReport(2026, 3);

        $this->assertSame('MARET', $report['bulan']);
        $this->assertSame(2026, $report['tahun']);
        $this->assertSame(1, $report['current']['jumlah']['lahir_jml']);
        $this->assertSame(0, $report['previous']['jumlah']['lahir_jml']);
    }

    public function test_previous_month_row_is_computed_from_previous_month(): void
    {
        $this->resident(['tanggal_lahir' => '2026-01-05']);

        $report = app(PopulationReportService::class)->getReport(2026, 2);

        $this->assertSame(1, $report['previous']['jumlah']['lahir_jml']);
        $this->assertSame(0, $report['current']['jumlah']['lahir_jml']);
    }

    public function test_births_deaths_moves_and_arrivals_split_by_gender(): void
    {
        $this->resident(['jenis_kelamin' => 'L', 'tanggal_lahir' => '2026-04-02']);
        $this->resident(['jenis_kelamin' => 'P', 'tanggal_lahir' => '2026-04-03']);
        $this->resident(['jenis_kelamin' => 'L', 'tanggal_kematian' => '2026-04-10', 'status_warga' => 'meninggal']);
        $this->resident(['jenis_kelamin' => 'P', 'tanggal_pindah' => '2026-04-11', 'status_warga' => 'pindah']);
        $this->resident(['jenis_kelamin' => 'L', 'tanggal_tinggal' => '2026-04-12']);
        $this->resident(['jenis_kelamin' => 'P', 'tanggal_tinggal' => '2026-04-13']);

        $rows = app(PopulationReportService::class)->getReport(2026, 4)['current']['jumlah'];

        $this->assertSame(1, $rows['lahir_l']);
        $this->assertSame(1, $rows['lahir_p']);
        $this->assertSame(2, $rows['lahir_jml']);
        $this->assertSame(1, $rows['meninggal_l']);
        $this->assertSame(1, $rows['meninggal_jml']);
        $this->assertSame(1, $rows['pindah_p']);
        $this->assertSame(1, $rows['pindah_jml']);
        $this->assertSame(1, $rows['datang_l']);
        $this->assertSame(1, $rows['datang_p']);
        $this->assertSame(2, $rows['datang_jml']);
    }

    public function test_population_excludes_deceased_and_moved_residents(): void
    {
        $this->resident(['status_warga' => 'aktif']);
        $this->resident(['status_warga' => 'meninggal', 'tanggal_kematian' => '2026-05-20']);
        $this->resident(['status_warga' => 'pindah', 'tanggal_pindah' => '2026-05-21']);
        $this->resident(['status_warga' => 'tidak_aktif']);

        $rows = app(PopulationReportService::class)->getReport(2026, 5)['current']['jumlah'];

        $this->assertSame(1, $rows['penduduk_jml']);
    }

    public function test_kepala_keluarga_and_kk_ktp_split_by_domisili(): void
    {
        $this->resident([
            'hubungan_dalam_keluarga' => 'kepala_keluarga',
            'domisili_asal' => 'DD',
            'nomor_kk' => '3201010101010001',
            'nomor_ktp' => '3201010101010002',
            'tanggal_lahir' => '1980-01-01',
        ]);
        $this->resident([
            'hubungan_dalam_keluarga' => 'kepala_keluarga',
            'domisili_asal' => 'LD',
            'nomor_kk' => '3201010101010003',
            'nomor_ktp' => '3201010101010004',
            'tanggal_lahir' => '1985-01-01',
        ]);

        $rows = app(PopulationReportService::class)->getReport(2026, 6)['current']['jumlah'];

        $this->assertSame(1, $rows['kk_dd']);
        $this->assertSame(1, $rows['kk_ld']);
        $this->assertSame(2, $rows['kk_jml']);
        $this->assertSame(1, $rows['kartu_kk_dd']);
        $this->assertSame(1, $rows['kartu_kk_ld']);
        $this->assertSame(1, $rows['ktp_dd']);
        $this->assertSame(1, $rows['ktp_ld']);
    }

    public function test_distinct_kk_is_not_double_counted(): void
    {
        $kk = '3201010101010009';

        $this->resident(['nomor_kk' => $kk, 'tanggal_lahir' => '1990-01-01']);
        $this->resident(['nomor_kk' => $kk, 'jenis_kelamin' => 'P', 'tanggal_lahir' => '1995-01-01']);
        $this->resident(['nomor_kk' => $kk, 'tanggal_lahir' => '2015-01-01']);

        $rows = app(PopulationReportService::class)->getReport(2026, 6)['current']['jumlah'];

        $this->assertSame(1, $rows['kartu_kk_jml']);
        $this->assertSame(3, $rows['penduduk_jml']);
    }

    public function test_ktp_requirement_applies_only_to_residents_17_and_older(): void
    {
        // Lahir 2010 -> masih 16 tahun per 30 Juni 2026, jadi belum wajib KTP.
        $this->resident(['tanggal_lahir' => '2010-05-10', 'jenis_kelamin' => 'L', 'nomor_ktp' => null]);
        $this->resident(['tanggal_lahir' => '1990-05-10', 'jenis_kelamin' => 'L', 'nomor_ktp' => '3201010101010010']);
        $this->resident(['tanggal_lahir' => '1992-05-10', 'jenis_kelamin' => 'P', 'nomor_ktp' => null]);

        $rows = app(PopulationReportService::class)->getReport(2026, 6)['current']['jumlah'];

        $this->assertSame(3, $rows['penduduk_jml']);
        $this->assertSame(1, $rows['wajib_l']);
        $this->assertSame(1, $rows['wajib_p']);
        $this->assertSame(2, $rows['wajib_jml']);
        $this->assertSame(1, $rows['ada_ktp_l']);
        $this->assertSame(0, $rows['ada_ktp_p']);
        $this->assertSame(1, $rows['ada_ktp_jml']);
        $this->assertSame(0, $rows['belum_ktp_l']);
        $this->assertSame(1, $rows['belum_ktp_p']);
        $this->assertSame(1, $rows['belum_ktp_jml']);
    }

    public function test_ktp_requirement_uses_age_at_end_of_month(): void
    {
        // Lahir 10 Juli 2009 -> belum 17 tahun per 30 Juni 2026, sudah 17 tahun per 31 Juli 2026.
        $this->resident(['tanggal_lahir' => '2009-07-10', 'nomor_ktp' => null]);

        $service = app(PopulationReportService::class);

        $this->assertSame(0, $service->getReport(2026, 6)['current']['jumlah']['wajib_jml']);
        $this->assertSame(1, $service->getReport(2026, 7)['current']['jumlah']['wajib_jml']);
    }

    public function test_foreigner_row_is_separated_from_national(): void
    {
        $this->resident(['kewarganegaraan' => 'WNA', 'tanggal_tinggal' => '2026-08-01']);
        $this->resident(['kewarganegaraan' => 'WNI', 'tanggal_tinggal' => '2026-08-01']);

        $report = app(PopulationReportService::class)->getReport(2026, 8);

        $this->assertSame(1, $report['current']['wna']['penduduk_jml']);
        $this->assertSame(1, $report['current']['wni']['penduduk_jml']);
        $this->assertSame(2, $report['current']['jumlah']['penduduk_jml']);
    }

    public function test_jumlah_row_is_sum_of_wni_and_wna(): void
    {
        $this->resident(['kewarganegaraan' => 'WNI', 'tanggal_lahir' => '2026-09-01']);
        $this->resident(['kewarganegaraan' => 'WNA', 'tanggal_lahir' => '2026-09-01', 'jenis_kelamin' => 'P']);

        $report = app(PopulationReportService::class)->getReport(2026, 9);

        foreach (PopulationReportService::COLUMNS as $column) {
            $this->assertSame(
                $report['current']['wni'][$column] + $report['current']['wna'][$column],
                $report['current']['jumlah'][$column],
                "Kolom {$column} tidak konsisten pada baris JUMLAH.",
            );
        }
    }

    public function test_resident_arriving_next_month_is_not_counted(): void
    {
        $this->resident(['tanggal_tinggal' => '2026-11-01']);

        $rows = app(PopulationReportService::class)->getReport(2026, 10)['current']['jumlah'];

        $this->assertSame(0, $rows['penduduk_jml']);
        $this->assertSame(0, $rows['datang_jml']);
    }

    public function test_build_rows_returns_six_lines_in_official_order(): void
    {
        $report = app(PopulationReportService::class)->getReport(2026, 9);
        $rows = PopulationReportService::buildRows($report);

        $this->assertCount(6, $rows);
        $this->assertSame(
            ['BULAN INI', 'BULAN INI', 'BULAN INI', 'BULAN LALU', 'BULAN LALU', 'BULAN LALU'],
            array_column($rows, 'period'),
        );
        $this->assertSame(
            ['WNI', 'WNA', 'JUMLAH', 'WNI', 'WNA', 'JUMLAH'],
            array_column($rows, 'nationality'),
        );
    }

    public function test_csv_export_matches_official_layout(): void
    {
        $response = $this->actingAs($this->pengurus)
            ->get(route('pengurus.reports.export', [
                'type' => 'kependudukan',
                'year' => 2026,
                'month' => 9,
            ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=utf-8');

        $csv = $response->streamedContent();

        $this->assertStringContainsString('LAPORAN BULANAN RUKUN TETANGGA', $csv);
        $this->assertStringContainsString('REKAPITULASI REGISTRASI KEPENDUDUKAN RT.008 RW.024', $csv);
        $this->assertStringContainsString('BULAN,:', $csv);
        $this->assertStringContainsString('SEPTEMBER', $csv);
        $this->assertStringContainsString('JUMLAH PENDUDUK BULAN INI', $csv);
        $this->assertStringContainsString('BULAN INI - WNI', $csv);
        $this->assertStringContainsString('BULAN LALU', $csv);
        $this->assertStringContainsString('DD,:', $csv);
        $this->assertStringContainsString('SIMON LIU', $csv);
    }

    public function test_warga_cannot_access_report(): void
    {
        $warga = $this->createUser('warga', 'warga01');

        $this->actingAs($warga)
            ->get(route('pengurus.reports.kependudukan'))
            ->assertForbidden();
    }

    public function test_pengurus_can_update_rt_identity(): void
    {
        $response = $this->actingAs($this->pengurus)
            ->put(route('pengurus.rt-profile.update'), [
                'rt' => '009',
                'rw' => '025',
                'kelurahan' => 'Gebang Raya',
                'kecamatan' => 'Periuk',
                'kota' => 'Tangerang',
                'provinsi' => 'Banten',
                'ketua_rt' => 'BUDI SANTOSO',
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('pengurus.rt-profile.index'));
        $response->assertSessionHas('success');

        $profile = RtProfile::active();
        $this->assertSame('009', $profile->rt);
        $this->assertSame('BUDI SANTOSO', $profile->ketua_rt);
        $this->assertSame(1, RtProfile::count());
    }

    public function test_rt_identity_requires_required_fields(): void
    {
        $this->actingAs($this->pengurus)
            ->put(route('pengurus.rt-profile.update'), ['rt' => ''])
            ->assertSessionHasErrors(['rw', 'kelurahan', 'kecamatan', 'kota', 'provinsi']);
    }

    public function test_warga_cannot_update_rt_identity(): void
    {
        $warga = $this->createUser('warga', 'warga02');

        $this->actingAs($warga)
            ->put(route('pengurus.rt-profile.update'), ['rt' => '001'])
            ->assertForbidden();
    }
}
