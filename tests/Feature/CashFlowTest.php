<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventExpense;
use App\Models\EventIncome;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\House;
use App\Models\Income;
use App\Models\Payment;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashFlowTest extends TestCase
{
    use RefreshDatabase;

    private function createPengurus(): User
    {
        return User::create([
            'username' => 'admin',
            'password' => bcrypt('password'),
            'role' => 'pengurus',
        ]);
    }

    private function createWarga(): User
    {
        return User::create([
            'username' => 'A01',
            'password' => bcrypt('password'),
            'role' => 'warga',
        ]);
    }

    private function createHouse(): House
    {
        return House::create([
            'block' => 'A',
            'house_number' => '01',
            'status' => 'ditempati',
            'address' => 'Jl. Mawar No. 1',
        ]);
    }

    public function test_cashflow_summary_combines_all_sources(): void
    {
        $pengurus = $this->createPengurus();
        $house = $this->createHouse();
        $resident = Resident::create([
            'house_id' => $house->id,
            'nik' => '3201234567890001',
            'nama_lengkap' => 'Budi',
            'jenis_kelamin' => 'L',
            'status_warga' => 'aktif',
            'hubungan_dalam_keluarga' => 'kepala_keluarga',
        ]);
        $category = ExpenseCategory::create(['name' => 'Operasional', 'is_active' => true]);
        $event = Event::create([
            'event_code' => 'EVT-2026-001',
            'title' => 'Kerja Bakti',
            'slug' => 'kerja-bakti-2026',
            'created_by' => $pengurus->id,
        ]);

        // Pemasukan iuran 100.000
        Payment::create([
            'house_id' => $house->id,
            'resident_id' => $resident->id,
            'payment_date' => '2026-06-10',
            'amount' => 100000,
            'payment_method' => 'cash',
            'recorded_by' => $pengurus->id,
            'status' => 'active',
        ]);

        // Pemasukan event 50.000 (tanpa linked payment)
        EventIncome::create([
            'event_id' => $event->id,
            'income_type' => 'donation',
            'description' => 'Donasi warga',
            'amount' => 50000,
            'income_date' => '2026-06-11',
            'recorded_by' => $pengurus->id,
            'status' => 'active',
        ]);

        // Pengeluaran kas umum 70.000
        Expense::create([
            'category_id' => $category->id,
            'description' => 'Beli terpal',
            'amount' => 70000,
            'expense_date' => '2026-06-12',
            'recorded_by' => $pengurus->id,
            'status' => 'active',
        ]);

        // Pengeluaran event 30.000
        EventExpense::create([
            'event_id' => $event->id,
            'category_id' => null,
            'description' => 'Konsumsi panitia',
            'amount' => 30000,
            'expense_date' => '2026-06-13',
            'recorded_by' => $pengurus->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($pengurus)->get('/pengurus/reports/cashflow');
        $response->assertStatus(200);
        $response->assertSee('Arus Kas');
        // Total pemasukan 150.000, total pengeluaran 100.000, saldo 50.000
        $response->assertSee('Rp150.000');
        $response->assertSee('Rp100.000');
        $response->assertSee('Rp50.000');
    }

    public function test_pengurus_can_view_cashflow_page(): void
    {
        $pengurus = $this->createPengurus();

        $response = $this->actingAs($pengurus)->get('/pengurus/reports/cashflow');
        $response->assertStatus(200);
        $response->assertSee('Arus Kas');
    }

    public function test_warga_cannot_access_pengurus_cashflow(): void
    {
        $warga = $this->createWarga();

        $this->actingAs($warga)->get('/pengurus/reports/cashflow')->assertStatus(403);
    }

    public function test_warga_can_view_cashflow_page(): void
    {
        $warga = $this->createWarga();

        $response = $this->actingAs($warga)->get('/warga/arus-kas');
        $response->assertStatus(200);
        $response->assertSee('Arus Kas');
    }

    public function test_warga_cashflow_shows_summary(): void
    {
        $pengurus = $this->createPengurus();
        $warga = $this->createWarga();
        $house = $this->createHouse();
        $resident = Resident::create([
            'house_id' => $house->id,
            'nik' => '3201234567890001',
            'nama_lengkap' => 'Budi',
            'jenis_kelamin' => 'L',
            'status_warga' => 'aktif',
            'hubungan_dalam_keluarga' => 'kepala_keluarga',
        ]);

        Payment::create([
            'house_id' => $house->id,
            'resident_id' => $resident->id,
            'payment_date' => '2026-06-10',
            'amount' => 60000,
            'payment_method' => 'cash',
            'recorded_by' => $pengurus->id,
            'status' => 'active',
        ]);

        Expense::create([
            'category_id' => null,
            'description' => 'Beli lampu',
            'amount' => 30000,
            'expense_date' => '2026-06-12',
            'recorded_by' => $pengurus->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($warga)->get('/warga/arus-kas');
        $response->assertStatus(200);
        $response->assertSee('Rp60.000');
        $response->assertSee('Rp30.000');
        $response->assertSee('Rp30.000');
    }

    public function test_cashflow_transactions_include_all_sources_without_overwriting(): void
    {
        $pengurus = $this->createPengurus();
        $house = $this->createHouse();
        $resident = Resident::create([
            'house_id' => $house->id,
            'nik' => '3201234567890001',
            'nama_lengkap' => 'Budi',
            'jenis_kelamin' => 'L',
            'status_warga' => 'aktif',
            'hubungan_dalam_keluarga' => 'kepala_keluarga',
        ]);
        $category = ExpenseCategory::create(['name' => 'Operasional', 'is_active' => true]);
        $event = Event::create([
            'event_code' => 'EVT-2026-002',
            'title' => 'Bazar Sehat',
            'slug' => 'bazar-sehat-2026',
            'created_by' => $pengurus->id,
        ]);

        Payment::create([
            'house_id' => $house->id,
            'resident_id' => $resident->id,
            'payment_date' => '2026-06-01',
            'amount' => 10000,
            'payment_method' => 'cash',
            'recorded_by' => $pengurus->id,
            'status' => 'active',
        ]);

        Income::create([
            'category_id' => null,
            'description' => 'TRANSAKSI_DARI_PEMASUKAN_KAS',
            'amount' => 20000,
            'income_date' => '2026-06-02',
            'recorded_by' => $pengurus->id,
            'status' => 'active',
        ]);

        EventIncome::create([
            'event_id' => $event->id,
            'income_type' => 'donation',
            'description' => 'Donasi bazar',
            'amount' => 30000,
            'income_date' => '2026-06-03',
            'recorded_by' => $pengurus->id,
            'status' => 'active',
        ]);

        Expense::create([
            'category_id' => $category->id,
            'description' => 'TRANSAKSI_DARI_PENGELUARAN_KAS',
            'amount' => 40000,
            'expense_date' => '2026-06-04',
            'recorded_by' => $pengurus->id,
            'status' => 'active',
        ]);

        EventExpense::create([
            'event_id' => $event->id,
            'category_id' => null,
            'description' => 'Biaya bazar',
            'amount' => 50000,
            'expense_date' => '2026-06-05',
            'recorded_by' => $pengurus->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($pengurus)->get('/pengurus/reports/cashflow');
        $response->assertStatus(200);

        // Kelima sumber harus tampil semua, tidak saling menimpa.
        // Catatan: baris event dilabeli dengan judul event, bukan field description miliknya.
        $response->assertSee('TRANSAKSI_DARI_PEMASUKAN_KAS');
        $response->assertSee('TRANSAKSI_DARI_PENGELUARAN_KAS');
        $response->assertSee('Event: Bazar Sehat');

        // Nominal tiap sumber muncul di tabel transaksi
        $response->assertSee('Rp10.000'); // iuran warga
        $response->assertSee('Rp20.000'); // pemasukan kas
        $response->assertSee('Rp40.000'); // pengeluaran kas

        // Total pemasukan 10k + 20k + 30k = 60k, pengeluaran 40k + 50k = 90k
        $response->assertSee('Rp60.000');
        $response->assertSee('Rp90.000');
    }

    public function test_cashflow_transactions_can_filter_by_type(): void
    {
        $pengurus = $this->createPengurus();
        $category = ExpenseCategory::create(['name' => 'Operasional', 'is_active' => true]);

        Income::create([
            'category_id' => null,
            'description' => 'Pemasukan yang harus muncul',
            'amount' => 20000,
            'income_date' => '2026-06-02',
            'recorded_by' => $pengurus->id,
            'status' => 'active',
        ]);

        Expense::create([
            'category_id' => $category->id,
            'description' => 'Pengeluaran yang harus disembunyikan',
            'amount' => 40000,
            'expense_date' => '2026-06-04',
            'recorded_by' => $pengurus->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($pengurus)->get('/pengurus/reports/cashflow?type=income');
        $response->assertStatus(200);
        $response->assertSee('Pemasukan yang harus muncul');
        $response->assertDontSee('Pengeluaran yang harus disembunyikan');
    }

    public function test_cashflow_statuses_excluded_from_summary_for_all_sources(): void
    {
        $this->createPengurus();

        Expense::create([
            'category_id' => null,
            'description' => 'Sudah dibatalkan',
            'amount' => 50000,
            'expense_date' => '2026-06-12',
            'recorded_by' => 1,
            'status' => 'void',
        ]);

        Payment::create([
            'house_id' => $this->createHouse()->id,
            'payment_date' => '2026-06-10',
            'amount' => 90000,
            'payment_method' => 'cash',
            'recorded_by' => 1,
            'status' => 'void',
        ]);

        $response = $this->actingAs(User::find(1))->get('/pengurus/reports/cashflow');
        $response->assertStatus(200);
        $response->assertSee('Rp0');
        $response->assertDontSee('Sudah dibatalkan');
    }
}
