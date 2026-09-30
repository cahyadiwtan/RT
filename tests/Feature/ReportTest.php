<?php

namespace Tests\Feature;

use App\Models\House;
use App\Models\MonthlyBill;
use App\Models\Payment;
use App\Models\Resident;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
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

    private function createHouse(): House
    {
        return House::create([
            'block' => 'A',
            'house_number' => '01',
            'status' => 'ditempati',
            'address' => 'Jl. Mawar No. 1',
        ]);
    }

    public function test_pengurus_can_view_iuran_report(): void
    {
        $pengurus = $this->createPengurus();
        $house = $this->createHouse();

        MonthlyBill::create([
            'house_id' => $house->id,
            'billing_period' => '2026-01',
            'amount' => 200000,
            'paid_amount' => 0,
            'remaining_amount' => 200000,
            'status' => 'unpaid',
            'due_date' => '2026-01-31',
        ]);

        $response = $this->actingAs($pengurus)->get('/pengurus/reports/iuran');
        $response->assertStatus(200);
        $response->assertSee('Laporan Iuran');
        $response->assertSee('Rp200.000');
    }

    public function test_pengurus_can_view_payments_report(): void
    {
        $pengurus = $this->createPengurus();
        $house = $this->createHouse();
        $resident = Resident::create([
            'house_id' => $house->id,
            'nik' => '3201234567890001',
            'nama_lengkap' => 'Budi Santoso',
            'jenis_kelamin' => 'L',
            'status_warga' => 'aktif',
            'hubungan_dalam_keluarga' => 'kepala_keluarga',
        ]);

        Payment::create([
            'house_id' => $house->id,
            'resident_id' => $resident->id,
            'payment_date' => '2026-01-15',
            'amount' => 200000,
            'payment_method' => 'transfer',
            'recorded_by' => $pengurus->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($pengurus)->get('/pengurus/reports/payments');
        $response->assertStatus(200);
        $response->assertSee('Laporan Pembayaran');
        $response->assertSee('Rp200.000');
    }

    public function test_pengurus_can_view_arrears_report(): void
    {
        $pengurus = $this->createPengurus();
        $house = $this->createHouse();

        MonthlyBill::create([
            'house_id' => $house->id,
            'billing_period' => '2026-01',
            'amount' => 200000,
            'paid_amount' => 0,
            'remaining_amount' => 200000,
            'status' => 'unpaid',
            'due_date' => '2026-01-31',
        ]);

        $response = $this->actingAs($pengurus)->get('/pengurus/reports/arrears');
        $response->assertStatus(200);
        $response->assertSee('Laporan Tunggakan');
        $response->assertSee('Rp200.000');
    }

    public function test_warga_cannot_access_reports(): void
    {
        $warga = User::create([
            'username' => 'A01',
            'password' => bcrypt('password'),
            'role' => 'warga',
        ]);

        $this->actingAs($warga)->get('/pengurus/reports/iuran')->assertStatus(403);
        $this->actingAs($warga)->get('/pengurus/reports/payments')->assertStatus(403);
        $this->actingAs($warga)->get('/pengurus/reports/arrears')->assertStatus(403);
    }

    public function test_export_iuran_csv(): void
    {
        $pengurus = $this->createPengurus();
        $house = $this->createHouse();

        MonthlyBill::create([
            'house_id' => $house->id,
            'billing_period' => '2026-01',
            'amount' => 200000,
            'paid_amount' => 100000,
            'remaining_amount' => 100000,
            'status' => 'partial',
            'due_date' => '2026-01-31',
        ]);

        $response = $this->actingAs($pengurus)->get('/pengurus/reports/export/iuran');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_export_pembayaran_csv(): void
    {
        $pengurus = $this->createPengurus();

        $response = $this->actingAs($pengurus)->get('/pengurus/reports/export/pembayaran');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_export_tunggakan_csv(): void
    {
        $pengurus = $this->createPengurus();

        $response = $this->actingAs($pengurus)->get('/pengurus/reports/export/tunggakan');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_iuran_report_filter_by_period(): void
    {
        $pengurus = $this->createPengurus();
        $house = $this->createHouse();

        MonthlyBill::create([
            'house_id' => $house->id,
            'billing_period' => '2026-01',
            'amount' => 200000,
            'paid_amount' => 0,
            'remaining_amount' => 200000,
            'status' => 'unpaid',
            'due_date' => '2026-01-31',
        ]);

        MonthlyBill::create([
            'house_id' => $house->id,
            'billing_period' => '2026-02',
            'amount' => 250000,
            'paid_amount' => 0,
            'remaining_amount' => 250000,
            'status' => 'unpaid',
            'due_date' => '2026-02-28',
        ]);

        $response = $this->actingAs($pengurus)->get('/pengurus/reports/iuran?billing_period=2026-01');
        $response->assertStatus(200);
        $response->assertSee('Rp200.000');
        $response->assertDontSee('Rp250.000');
    }

    public function test_redirect_index_to_iuran(): void
    {
        $pengurus = $this->createPengurus();

        $response = $this->actingAs($pengurus)->get('/pengurus/reports');
        $response->assertRedirect(route('pengurus.reports.iuran'));
    }

    public function test_pengurus_can_view_deposits_report(): void
    {
        $pengurus = $this->createPengurus();
        $house = $this->createHouse();

        MonthlyBill::create([
            'house_id' => $house->id,
            'billing_period' => '2026-01',
            'amount' => 30000,
            'paid_amount' => 0,
            'remaining_amount' => 30000,
            'status' => 'unpaid',
            'due_date' => '2026-01-31',
        ]);

        (new PaymentService)->recordPayment([
            'house_id' => $house->id,
            'payment_date' => '2026-01-10',
            'amount' => 50000,
            'payment_method' => 'cash',
        ], $pengurus->id);

        $response = $this->actingAs($pengurus)->get('/pengurus/reports/deposits');
        $response->assertStatus(200);
        $response->assertSee('Laporan Deposit');
        $response->assertSee('Rp20.000');
    }

    public function test_warga_cannot_access_deposits_report(): void
    {
        $warga = User::create([
            'username' => 'A01',
            'password' => bcrypt('password'),
            'role' => 'warga',
        ]);

        $this->actingAs($warga)->get('/pengurus/reports/deposits')->assertStatus(403);
    }

    public function test_export_deposits_csv(): void
    {
        $pengurus = $this->createPengurus();
        $house = $this->createHouse();

        MonthlyBill::create([
            'house_id' => $house->id,
            'billing_period' => '2026-01',
            'amount' => 30000,
            'paid_amount' => 0,
            'remaining_amount' => 30000,
            'status' => 'unpaid',
            'due_date' => '2026-01-31',
        ]);

        (new PaymentService)->recordPayment([
            'house_id' => $house->id,
            'payment_date' => '2026-01-10',
            'amount' => 50000,
            'payment_method' => 'cash',
        ], $pengurus->id);

        $response = $this->actingAs($pengurus)->get('/pengurus/reports/export/deposit');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}
