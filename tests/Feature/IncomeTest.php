<?php

namespace Tests\Feature;

use App\Models\Income;
use App\Models\IncomeCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncomeTest extends TestCase
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

    private function createCategory(string $name = 'Donasi'): IncomeCategory
    {
        return IncomeCategory::create([
            'name' => $name,
            'description' => 'Keterangan '.$name,
            'is_active' => true,
        ]);
    }

    public function test_pengurus_can_view_income_index(): void
    {
        $pengurus = $this->createPengurus();
        $category = $this->createCategory();

        Income::create([
            'category_id' => $category->id,
            'description' => 'Donasi warga untuk posyandu',
            'amount' => 500000,
            'income_date' => '2026-01-15',
            'source_name' => 'Bu Sari',
            'recorded_by' => $pengurus->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($pengurus)->get(route('pengurus.incomes.index'));
        $response->assertStatus(200);
        $response->assertSee('Pemasukan Kas Umum');
        $response->assertSee('Donasi warga untuk posyandu');
        $response->assertSee('Rp500.000');
    }

    public function test_pengurus_can_store_income(): void
    {
        $pengurus = $this->createPengurus();
        $category = $this->createCategory();

        $response = $this->actingAs($pengurus)->post(route('pengurus.incomes.store'), [
            'category_id' => $category->id,
            'description' => 'Sumbangan kotak Amal',
            'amount' => 250000,
            'income_date' => '2026-02-10',
            'source_name' => 'Yayasan Kasih Sayap',
            'reference_number' => 'REF-001',
            'notes' => 'Untuk kegiatan bulan ini',
        ]);

        $response->assertRedirect(route('pengurus.incomes.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('incomes', [
            'category_id' => $category->id,
            'description' => 'Sumbangan kotak Amal',
            'amount' => 250000,
            'status' => 'active',
            'recorded_by' => $pengurus->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $pengurus->id,
            'action' => 'CREATE_INCOME',
        ]);
    }

    public function test_income_requires_valid_data(): void
    {
        $pengurus = $this->createPengurus();

        $response = $this->actingAs($pengurus)->post(route('pengurus.incomes.store'), [
            'category_id' => 999,
            'description' => '',
            'amount' => 0,
            'income_date' => '2026-02-10',
        ]);

        $response->assertSessionHasErrors(['description', 'amount', 'category_id']);
        $this->assertDatabaseCount('incomes', 0);
    }

    public function test_pengurus_can_open_edit_income_form(): void
    {
        $pengurus = $this->createPengurus();
        $category = $this->createCategory();

        $income = Income::create([
            'category_id' => $category->id,
            'description' => 'Donasi warga RT 05',
            'amount' => 75000,
            'income_date' => '2026-03-05',
            'recorded_by' => $pengurus->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($pengurus)->get(route('pengurus.incomes.edit', $income));
        $response->assertStatus(200);
        $response->assertSee('Edit Pemasukan');
        $response->assertSee('Donasi warga RT 05');
    }

    public function test_void_income_edit_form_returns_404(): void
    {
        $pengurus = $this->createPengurus();
        $category = $this->createCategory();

        $income = Income::create([
            'category_id' => $category->id,
            'description' => 'Sudah dibatalkan',
            'amount' => 50000,
            'income_date' => '2026-03-05',
            'recorded_by' => $pengurus->id,
            'status' => 'void',
        ]);

        $this->actingAs($pengurus)->get(route('pengurus.incomes.edit', $income))->assertStatus(404);
    }

    public function test_pengurus_can_update_income(): void
    {
        $pengurus = $this->createPengurus();
        $category = $this->createCategory();

        $income = Income::create([
            'category_id' => $category->id,
            'description' => 'Donasi warga',
            'amount' => 50000,
            'income_date' => '2026-01-15',
            'recorded_by' => $pengurus->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($pengurus)->put(route('pengurus.incomes.update', $income), [
            'category_id' => $category->id,
            'description' => 'Donasi warga RT 07',
            'amount' => 65000,
            'income_date' => '2026-01-20',
        ]);

        $response->assertRedirect(route('pengurus.incomes.index'));
        $this->assertDatabaseHas('incomes', [
            'id' => $income->id,
            'amount' => 65000,
            'description' => 'Donasi warga RT 07',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'auditable_id' => $income->id,
            'action' => 'UPDATE_INCOME',
        ]);
    }

    public function test_pengurus_can_void_income(): void
    {
        $pengurus = $this->createPengurus();
        $category = $this->createCategory();

        $income = Income::create([
            'category_id' => $category->id,
            'description' => 'Pemasukan keliru',
            'amount' => 100000,
            'income_date' => '2026-04-01',
            'recorded_by' => $pengurus->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($pengurus)->patch(route('pengurus.incomes.void', $income));
        $response->assertRedirect();

        $this->assertDatabaseHas('incomes', ['id' => $income->id, 'status' => 'void']);
        $this->assertDatabaseHas('audit_logs', [
            'auditable_id' => $income->id,
            'action' => 'VOID_INCOME',
        ]);
    }

    public function test_void_income_hidden_from_index(): void
    {
        $pengurus = $this->createPengurus();
        $category = $this->createCategory();

        Income::create([
            'category_id' => $category->id,
            'description' => 'Pemasukan dibatalkan',
            'amount' => 20000,
            'income_date' => '2026-04-01',
            'recorded_by' => $pengurus->id,
            'status' => 'void',
        ]);

        $response = $this->actingAs($pengurus)->get(route('pengurus.incomes.index'));
        $response->assertStatus(200);
        $response->assertDontSee('Pemasukan dibatalkan');
    }

    public function test_income_index_can_filter_by_category_and_date(): void
    {
        $pengurus = $this->createPengurus();
        $donasi = $this->createCategory('Donasi');
        $subsidi = $this->createCategory('Subsidi');

        Income::create([
            'category_id' => $donasi->id,
            'description' => 'Donasi bulan Juni',
            'amount' => 30000,
            'income_date' => '2026-06-10',
            'recorded_by' => $pengurus->id,
            'status' => 'active',
        ]);

        Income::create([
            'category_id' => $subsidi->id,
            'description' => 'Subsidi bulan Juli',
            'amount' => 40000,
            'income_date' => '2026-07-10',
            'recorded_by' => $pengurus->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($pengurus)->get(route('pengurus.incomes.index', ['category_id' => $donasi->id]));
        $response->assertStatus(200);
        $response->assertSee('Donasi bulan Juni');
        $response->assertDontSee('Subsidi bulan Juli');
        $response->assertSee('Rp30.000');

        $response = $this->actingAs($pengurus)->get(route('pengurus.incomes.index', ['date_from' => '2026-07-01']));
        $response->assertStatus(200);
        $response->assertSee('Subsidi bulan Juli');
        $response->assertDontSee('Donasi bulan Juni');
    }

    public function test_warga_cannot_access_income_module(): void
    {
        $warga = $this->createWarga();

        $this->actingAs($warga)->get(route('pengurus.incomes.index'))->assertStatus(403);
        $this->actingAs($warga)->get(route('pengurus.incomes.create'))->assertStatus(403);
        $this->actingAs($warga)->get(route('pengurus.income-categories.index'))->assertStatus(403);
    }

    public function test_guest_cannot_access_income_module(): void
    {
        $this->get(route('pengurus.incomes.index'))->assertRedirect(route('login'));
    }

    public function test_pengurus_can_create_and_update_income_category(): void
    {
        $pengurus = $this->createPengurus();

        $this->actingAs($pengurus)->post(route('pengurus.income-categories.store'), [
            'name' => 'Donasi',
            'description' => 'Donasi dari warga dan pihak ketiga',
        ])->assertRedirect();

        $this->assertDatabaseHas('income_categories', ['name' => 'Donasi']);

        $category = IncomeCategory::where('name', 'Donasi')->firstOrFail();

        $this->actingAs($pengurus)->put(route('pengurus.income-categories.update', $category), [
            'name' => 'Donasi pihak ketiga',
            'description' => 'Donasi diperbarui',
            'is_active' => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('income_categories', ['id' => $category->id, 'name' => 'Donasi pihak ketiga']);
    }

    public function test_income_category_name_must_be_unique(): void
    {
        $pengurus = $this->createPengurus();
        $this->createCategory('Donasi');

        $this->actingAs($pengurus)->post(route('pengurus.income-categories.store'), [
            'name' => 'Donasi',
        ])->assertSessionHasErrors('name');
    }

    public function test_pengurus_can_destroy_income_category(): void
    {
        $pengurus = $this->createPengurus();
        $category = $this->createCategory('Operasional');

        Income::create([
            'category_id' => $category->id,
            'description' => 'Pemasukan terkait kategori',
            'amount' => 30000,
            'income_date' => '2026-05-01',
            'recorded_by' => $pengurus->id,
            'status' => 'active',
        ]);

        $this->actingAs($pengurus)->delete(route('pengurus.income-categories.destroy', $category))->assertRedirect();

        $this->assertDatabaseMissing('income_categories', ['id' => $category->id]);

        // income terkait kategori tetap ada, category_id jadi null (nullOnDelete)
        $this->assertDatabaseHas('incomes', [
            'category_id' => null,
            'description' => 'Pemasukan terkait kategori',
        ]);
    }

    public function test_income_appears_in_cash_flow_report(): void
    {
        $pengurus = $this->createPengurus();
        $category = $this->createCategory('Donasi');

        Income::create([
            'category_id' => $category->id,
            'description' => 'Donasi untuk perbaikan jalan',
            'amount' => 1000000,
            'income_date' => '2026-06-15',
            'recorded_by' => $pengurus->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($pengurus)->get('/pengurus/reports/cashflow');
        $response->assertStatus(200);
        $response->assertSee('Donasi untuk perbaikan jalan');
        $response->assertSee('Rp1.000.000');
    }
}
