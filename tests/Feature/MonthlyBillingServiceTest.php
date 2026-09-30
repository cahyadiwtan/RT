<?php

namespace Tests\Feature;

use App\Models\House;
use App\Models\MonthlyBill;
use App\Models\MonthlyFeeSetting;
use App\Models\Payment;
use App\Models\Resident;
use App\Services\MonthlyBillingService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonthlyBillingServiceTest extends TestCase
{
    use RefreshDatabase;

    private function createSetting(): void
    {
        MonthlyFeeSetting::create([
            'amount' => 30000,
            'effective_from' => '2026-01-01',
            'is_active' => true,
        ]);
    }

    public function test_can_generate_monthly_bills_idempotently(): void
    {
        // 1. Setup
        MonthlyFeeSetting::create([
            'amount' => 30000,
            'effective_from' => '2026-01-01',
            'is_active' => true,
        ]);

        House::create(['block' => 'A', 'house_number' => '01', 'status' => 'ditempati']);
        House::create(['block' => 'A', 'house_number' => '02', 'status' => 'ditempati']);

        $service = new MonthlyBillingService;

        // 2. First Run
        $result = $service->generateMonthlyBills('2026-06');
        $this->assertEquals(2, $result['generated_count']);
        $this->assertEquals(0, $result['skipped_count']);
        $this->assertDatabaseCount('monthly_bills', 2);

        // 3. Second Run (Idempotency test)
        $resultSecond = $service->generateMonthlyBills('2026-06');
        $this->assertEquals(0, $resultSecond['generated_count']);
        $this->assertEquals(2, $resultSecond['skipped_count']);
        $this->assertDatabaseCount('monthly_bills', 2);
    }

    public function test_billing_skips_move_in_month_and_starts_next_month(): void
    {
        MonthlyFeeSetting::create([
            'amount' => 30000,
            'effective_from' => '2026-01-01',
            'is_active' => true,
        ]);

        // Kepala keluarga pindah 20 Januari 2026 -> billing mulai Februari 2026
        $house = House::create(['block' => 'A', 'house_number' => '03', 'status' => 'ditempati']);
        House::create(['block' => 'A', 'house_number' => '04', 'status' => 'ditempati']); // tidak punya tanggal_tinggal -> billing normal

        Resident::create([
            'house_id' => $house->id,
            'nik' => '3201012001010001',
            'nama_lengkap' => 'Kepala Keluarga',
            'status_warga' => 'aktif',
            'hubungan_dalam_keluarga' => 'kepala_keluarga',
            'tanggal_tinggal' => '2026-01-20',
        ]);

        $service = new MonthlyBillingService;

        // Bulan Januari: rumah dengan tanggal_tinggal 20 Jan tidak ditagih (skip, bukan duplikat)
        $resultJan = $service->generateMonthlyBills('2026-01');
        $this->assertEquals(1, $resultJan['generated_count']);
        $this->assertEquals(1, $resultJan['skipped_count']);
        $this->assertDatabaseMissing('monthly_bills', [
            'house_id' => $house->id,
            'billing_period' => '2026-01',
        ]);

        // Bulan Februari: mulai ditagih
        $resultFeb = $service->generateMonthlyBills('2026-02');
        $this->assertEquals(2, $resultFeb['generated_count']);
        $this->assertDatabaseHas('monthly_bills', [
            'house_id' => $house->id,
            'billing_period' => '2026-02',
        ]);
    }

    public function test_generate_up_to_backfills_from_move_in_month_to_target(): void
    {
        MonthlyFeeSetting::create([
            'amount' => 30000,
            'effective_from' => '2026-01-01',
            'is_active' => true,
        ]);

        // KK pindah 20 Jan 2026 -> billing mulai Feb 2026
        $house = House::create(['block' => 'A', 'house_number' => '05', 'status' => 'ditempati']);

        Resident::create([
            'house_id' => $house->id,
            'nik' => '3201012001010005',
            'nama_lengkap' => 'Kepala Keluarga',
            'status_warga' => 'aktif',
            'hubungan_dalam_keluarga' => 'kepala_keluarga',
            'tanggal_tinggal' => '2026-01-20',
        ]);

        $service = new MonthlyBillingService;

        // Generate sampai bulan April -> harus backfill Feb, Mar, Apr (3 tagihan)
        $result = $service->generateMonthlyBillsUpTo('2026-04');
        $this->assertEquals(3, $result['generated_count']);
        $this->assertEquals(0, $result['skipped_count']);

        foreach (['2026-02', '2026-03', '2026-04'] as $period) {
            $this->assertDatabaseHas('monthly_bills', [
                'house_id' => $house->id,
                'billing_period' => $period,
            ]);
        }
        $this->assertDatabaseMissing('monthly_bills', [
            'house_id' => $house->id,
            'billing_period' => '2026-01',
        ]);

        // Jalankan lagi -> idempotent, tidak ada yang baru dibuat
        $resultSecond = $service->generateMonthlyBillsUpTo('2026-04');
        $this->assertEquals(0, $resultSecond['generated_count']);
        $this->assertEquals(3, $resultSecond['skipped_count']);
        $this->assertDatabaseCount('monthly_bills', 3);
    }

    public function test_generate_up_to_only_bills_target_for_house_without_tanggal_tinggal(): void
    {
        MonthlyFeeSetting::create([
            'amount' => 30000,
            'effective_from' => '2026-01-01',
            'is_active' => true,
        ]);

        // Tidak ada tanggal_tinggal -> hanya bulan target yang dibuat
        House::create(['block' => 'A', 'house_number' => '06', 'status' => 'ditempati']);

        $service = new MonthlyBillingService;

        $result = $service->generateMonthlyBillsUpTo('2026-04');
        $this->assertEquals(1, $result['generated_count']);
        $this->assertEquals(0, $result['skipped_count']);

        $this->assertDatabaseHas('monthly_bills', [
            'billing_period' => '2026-04',
        ]);
        $this->assertDatabaseMissing('monthly_bills', [
            'billing_period' => '2026-03',
        ]);
    }

    public function test_credit_balance_covers_next_month_bill_and_carries_remainder(): void
    {
        $this->createSetting();

        $house = House::create(['block' => 'A', 'house_number' => '07', 'status' => 'ditempati']);
        $admin = \App\Models\User::create(['username' => 'admin7', 'password' => bcrypt('pw'), 'role' => 'pengurus']);

        $service = new MonthlyBillingService;
        $service->generateMonthlyBills('2026-06');

        // Warga bayar 80k atas tagihan Jun (30k) -> sisa 50k jadi deposit
        (new PaymentService)->recordPayment([
            'house_id' => $house->id,
            'payment_date' => '2026-06-15',
            'amount' => 80000,
            'payment_method' => 'cash',
        ], $admin->id);

        // Generate bill Jul -> deposit 50k otomatis menutup tagihan 30k
        $service->generateMonthlyBills('2026-07');

        $julBill = MonthlyBill::where('house_id', $house->id)->where('billing_period', '2026-07')->firstOrFail();
        $this->assertSame(30000.0, (float) $julBill->paid_amount);
        $this->assertSame(0.0, (float) $julBill->remaining_amount);
        $this->assertSame('paid', $julBill->status);

        // Deposit sisa = 50k - 30k = 20k
        $credit = (float) Payment::where('house_id', $house->id)->where('status', 'active')->sum('amount')
            - (float) \App\Models\PaymentAllocation::whereHas('payment', fn ($q) => $q->where('house_id', $house->id)->where('status', 'active'))->sum('amount');
        $this->assertSame(20000.0, $credit);
    }

    public function test_credit_balance_settles_consecutive_periods_until_exhausted(): void
    {
        $this->createSetting();

        $house = House::create(['block' => 'A', 'house_number' => '08', 'status' => 'ditempati']);
        $admin = \App\Models\User::create(['username' => 'admin8', 'password' => bcrypt('pw'), 'role' => 'pengurus']);

        // KK pindah 1 Mei 2026 -> billing mulai Juni 2026
        Resident::create([
            'house_id' => $house->id,
            'nik' => '3201012001010008',
            'nama_lengkap' => 'Kepala Keluarga',
            'status_warga' => 'aktif',
            'hubungan_dalam_keluarga' => 'kepala_keluarga',
            'tanggal_tinggal' => '2026-05-01',
        ]);

        $service = new MonthlyBillingService;
        $service->generateMonthlyBills('2026-06');

        // Deposit 70k (bayar 100k atas tagihan 30k Jun)
        (new PaymentService)->recordPayment([
            'house_id' => $house->id,
            'payment_date' => '2026-06-15',
            'amount' => 100000,
            'payment_method' => 'cash',
        ], $admin->id);

        // Backfill Jul, Agu, Sep (3 x 30k). Deposit 70k menutup Jul & Agu penuh, Sep sisa 10k.
        $service->generateMonthlyBillsUpTo('2026-09');

        $julBill = MonthlyBill::where('house_id', $house->id)->where('billing_period', '2026-07')->firstOrFail();
        $augBill = MonthlyBill::where('house_id', $house->id)->where('billing_period', '2026-08')->firstOrFail();
        $sepBill = MonthlyBill::where('house_id', $house->id)->where('billing_period', '2026-09')->firstOrFail();

        $this->assertSame('paid', $julBill->status);
        $this->assertSame('paid', $augBill->status);
        $this->assertSame('partial', $sepBill->status);
        $this->assertSame(20000.0, (float) $sepBill->remaining_amount);
    }

    public function test_credit_allocation_is_idempotent_on_reregister(): void
    {
        $this->createSetting();

        $house = House::create(['block' => 'A', 'house_number' => '09', 'status' => 'ditempati']);
        $admin = \App\Models\User::create(['username' => 'admin9', 'password' => bcrypt('pw'), 'role' => 'pengurus']);

        $service = new MonthlyBillingService;
        $service->generateMonthlyBills('2026-06');
        (new PaymentService)->recordPayment([
            'house_id' => $house->id,
            'payment_date' => '2026-06-15',
            'amount' => 80000,
            'payment_method' => 'cash',
        ], $admin->id);

        $service->generateMonthlyBills('2026-07');
        $allocCountAfterFirst = \App\Models\PaymentAllocation::count();

        // Generate ulang -> idempotent, tidak double-terserap
        $service->generateMonthlyBills('2026-07');
        $this->assertSame($allocCountAfterFirst, \App\Models\PaymentAllocation::count());

        $julBill = MonthlyBill::where('house_id', $house->id)->where('billing_period', '2026-07')->firstOrFail();
        $this->assertSame(30000.0, (float) $julBill->paid_amount);
        $this->assertSame(0.0, (float) $julBill->remaining_amount);
    }

    public function test_generate_monthly_bills_range_covers_inclusive_periods(): void
    {
        $this->createSetting();

        House::create(['block' => 'A', 'house_number' => '10', 'status' => 'ditempati']);

        $service = new MonthlyBillingService;

        // Tidak ada tanggal_tinggal -> semua periode dalam range dibuat
        $result = $service->generateMonthlyBillsRange('2026-06', '2026-08');
        $this->assertSame(3, $result['generated_count']);
        $this->assertSame(0, $result['skipped_count']);

        foreach (['2026-06', '2026-07', '2026-08'] as $period) {
            $this->assertDatabaseHas('monthly_bills', ['billing_period' => $period]);
        }

        // Idempotent: generate ulang -> semua skip
        $resultSecond = $service->generateMonthlyBillsRange('2026-06', '2026-08');
        $this->assertSame(0, $resultSecond['generated_count']);
        $this->assertSame(3, $resultSecond['skipped_count']);
    }

    public function test_generate_monthly_bills_range_rejects_reversed_periods(): void
    {
        $this->createSetting();

        House::create(['block' => 'A', 'house_number' => '11', 'status' => 'ditempati']);

        $service = new MonthlyBillingService;

        $this->expectException(\InvalidArgumentException::class);
        $service->generateMonthlyBillsRange('2026-08', '2026-06');
    }

    public function test_generate_monthly_bills_range_respects_move_in_month(): void
    {
        $this->createSetting();

        // KK pindah 15 Mei 2026 -> billing mulai Juni 2026
        $house = House::create(['block' => 'A', 'house_number' => '12', 'status' => 'ditempati']);

        Resident::create([
            'house_id' => $house->id,
            'nik' => '3201012001010012',
            'nama_lengkap' => 'Kepala Keluarga',
            'status_warga' => 'aktif',
            'hubungan_dalam_keluarga' => 'kepala_keluarga',
            'tanggal_tinggal' => '2026-05-15',
        ]);

        $service = new MonthlyBillingService;

        // Range Mei s.d. Jul -> bulan pindah (Mei) dilewati, Jun & Jul dibuat
        $result = $service->generateMonthlyBillsRange('2026-05', '2026-07');
        $this->assertSame(2, $result['generated_count']);
        $this->assertSame(1, $result['skipped_count']);

        $this->assertDatabaseMissing('monthly_bills', ['house_id' => $house->id, 'billing_period' => '2026-05']);
        $this->assertDatabaseHas('monthly_bills', ['house_id' => $house->id, 'billing_period' => '2026-06']);
        $this->assertDatabaseHas('monthly_bills', ['house_id' => $house->id, 'billing_period' => '2026-07']);
    }

    public function test_range_generation_auto_allocates_deposit(): void
    {
        $this->createSetting();

        $house = House::create(['block' => 'A', 'house_number' => '13', 'status' => 'ditempati']);
        $admin = \App\Models\User::create(['username' => 'admin13', 'password' => bcrypt('pw'), 'role' => 'pengurus']);

        $service = new MonthlyBillingService;
        $service->generateMonthlyBills('2026-06');

        // Bayar 80k atas tagihan Jun (30k) -> deposit 50k
        (new PaymentService)->recordPayment([
            'house_id' => $house->id,
            'payment_date' => '2026-06-15',
            'amount' => 80000,
            'payment_method' => 'cash',
        ], $admin->id);

        // Generate range Jul & Agu -> deposit 50k menutup Jul penuh (30k), Agu 20k (sisa 10k)
        $service->generateMonthlyBillsRange('2026-07', '2026-08');

        $julBill = MonthlyBill::where('house_id', $house->id)->where('billing_period', '2026-07')->firstOrFail();
        $augBill = MonthlyBill::where('house_id', $house->id)->where('billing_period', '2026-08')->firstOrFail();

        $this->assertSame('paid', $julBill->status);
        $this->assertSame('partial', $augBill->status);
        $this->assertSame(10000.0, (float) $augBill->remaining_amount);
    }

    public function test_pengurus_can_generate_bills_via_range_endpoint(): void
    {
        $this->createSetting();

        House::create(['block' => 'A', 'house_number' => '14', 'status' => 'ditempati']);
        $admin = \App\Models\User::create(['username' => 'admin14', 'password' => bcrypt('pw'), 'role' => 'pengurus']);

        $response = $this->actingAs($admin)->post('/pengurus/bills/generate', [
            'from_period' => '2026-06',
            'to_period' => '2026-07',
        ]);

        $response->assertRedirect('/pengurus/bills');
        $response->assertSessionHas('success');
        $this->assertDatabaseCount('monthly_bills', 2);
    }

    public function test_warga_cannot_generate_bills(): void
    {
        $warga = \App\Models\User::create(['username' => 'warga14', 'password' => bcrypt('pw'), 'role' => 'warga']);

        $this->actingAs($warga)->post('/pengurus/bills/generate', [
            'from_period' => '2026-06',
            'to_period' => '2026-07',
        ])->assertStatus(403);
    }

    public function test_new_kk_billing_starts_month_after_move_in_within_wide_range(): void
    {
        $this->createSetting();

        // KK baru aktif 9 September 2026 -> billing mulai Oktober 2026
        $house = House::create(['block' => 'A', 'house_number' => '15', 'status' => 'ditempati']);

        Resident::create([
            'house_id' => $house->id,
            'nik' => '3201012001010015',
            'nama_lengkap' => 'Kepala Keluarga Baru',
            'status_warga' => 'aktif',
            'hubungan_dalam_keluarga' => 'kepala_keluarga',
            'tanggal_tinggal' => '2026-09-15',
        ]);

        $service = new MonthlyBillingService;

        // Generate range lebar Jan s.d. Des 2026
        $result = $service->generateMonthlyBillsRange('2026-01', '2026-12');

        // Hanya Okt, Nov, Des yang ditagih (bulan setelah pindah)
        $this->assertSame(3, $result['generated_count']);
        $this->assertSame(9, $result['skipped_count']);

        foreach (['2026-10', '2026-11', '2026-12'] as $period) {
            $this->assertDatabaseHas('monthly_bills', ['house_id' => $house->id, 'billing_period' => $period]);
        }
        foreach (['2026-01', '2026-02', '2026-03', '2026-04', '2026-05', '2026-06', '2026-07', '2026-08', '2026-09'] as $period) {
            $this->assertDatabaseMissing('monthly_bills', ['house_id' => $house->id, 'billing_period' => $period]);
        }
    }

    public function test_deposit_paid_before_bills_are_generated_covers_new_bills(): void
    {
        $this->createSetting();

        // Warga membayar 60.000 di Juni 2026, tetapi tagihan belum ada saat itu
        $house = House::create(['block' => 'A', 'house_number' => '16', 'status' => 'ditempati']);
        $admin = \App\Models\User::create(['username' => 'admin16', 'password' => bcrypt('pw'), 'role' => 'pengurus']);
        $resident = Resident::create([
            'house_id' => $house->id,
            'nik' => '3201012001010016',
            'nama_lengkap' => 'Kepala Keluarga',
            'status_warga' => 'aktif',
            'hubungan_dalam_keluarga' => 'kepala_keluarga',
            'tanggal_tinggal' => '2026-01-10',
        ]);

        $paymentService = new PaymentService;

        // Juni 2026: bayar 60.000, belum ada tagihan -> seluruhnya jadi saldo deposit
        $payment = $paymentService->recordPayment([
            'house_id' => $house->id,
            'resident_id' => $resident->id,
            'payment_date' => '2026-06-10',
            'amount' => 60000,
            'payment_method' => 'cash',
        ], $admin->id);

        // Saat pembayaran berlangsung, belum ada tagihan yang dapat dialokasi
        $this->assertSame(0, \App\Models\PaymentAllocation::where('payment_id', $payment->id)->count());

        // Tagihan baru di-generate pada Agustus 2026 (range Jun s.d. Agu)
        $service = new MonthlyBillingService;
        $result = $service->generateMonthlyBillsRange('2026-06', '2026-08');

        // Karena KK tanggal_tinggal Jan -> Juni, Juli, Agustus ikut ditagih
        $this->assertSame(3, $result['generated_count']);

        // Deposit 60.000 diterapkan FIFO ke tagihan: Juni 30k lunas, Juli 30k lunas,
        // Agustus 30k -> hanya sisa deposit 0 (60k habis), Agustus tetap belum dibayar
        $juniBill = MonthlyBill::where('house_id', $house->id)->where('billing_period', '2026-06')->firstOrFail();
        $juliBill = MonthlyBill::where('house_id', $house->id)->where('billing_period', '2026-07')->firstOrFail();
        $agustusBill = MonthlyBill::where('house_id', $house->id)->where('billing_period', '2026-08')->firstOrFail();

        $this->assertSame('paid', $juniBill->status);
        $this->assertSame('paid', $juliBill->status);
        $this->assertSame(0.0, (float) $agustusBill->paid_amount);
        $this->assertSame(30000.0, (float) $agustusBill->remaining_amount);
        $this->assertSame('unpaid', $agustusBill->status);
    }
}
