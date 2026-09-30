<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseTest extends TestCase
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

    private function createCategory(string $name = 'Perlengkapan'): ExpenseCategory
    {
        return ExpenseCategory::create([
            'name' => $name,
            'description' => 'Keterangan '.$name,
            'is_active' => true,
        ]);
    }

    public function test_pengurus_can_view_expense_index(): void
    {
        $pengurus = $this->createPengurus();
        $category = $this->createCategory();

        Expense::create([
            'category_id' => $category->id,
            'description' => 'Beli lampu penerangan',
            'amount' => 50000,
            'expense_date' => '2026-01-15',
            'recorded_by' => $pengurus->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($pengurus)->get(route('pengurus.expenses.index'));
        $response->assertStatus(200);
        $response->assertSee('Beli lampu penerangan');
        $response->assertSee('Rp50.000');
    }

    public function test_pengurus_can_store_expense(): void
    {
        $pengurus = $this->createPengurus();
        $category = $this->createCategory();

        $response = $this->actingAs($pengurus)->post(route('pengurus.expenses.store'), [
            'category_id' => $category->id,
            'description' => 'Pembelian alat kebersihan',
            'amount' => 75000,
            'expense_date' => '2026-02-10',
            'vendor' => 'Toko Sembako',
            'receipt_number' => 'INV-001',
            'notes' => 'Untuk kerja bakti',
        ]);

        $response->assertRedirect(route('pengurus.expenses.index'));

        $this->assertDatabaseHas('expenses', [
            'category_id' => $category->id,
            'description' => 'Pembelian alat kebersihan',
            'amount' => 75000,
            'status' => 'active',
            'recorded_by' => $pengurus->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $pengurus->id,
            'action' => 'CREATE_EXPENSE',
        ]);
    }

    public function test_expense_requires_valid_data(): void
    {
        $pengurus = $this->createPengurus();

        $response = $this->actingAs($pengurus)->post(route('pengurus.expenses.store'), [
            'category_id' => 999,
            'description' => '',
            'amount' => 0,
            'expense_date' => '2026-02-10',
        ]);

        $response->assertSessionHasErrors(['description', 'amount', 'category_id']);
        $this->assertDatabaseCount('expenses', 0);
    }

    public function test_pengurus_can_update_expense(): void
    {
        $pengurus = $this->createPengurus();
        $category = $this->createCategory();

        $expense = Expense::create([
            'category_id' => $category->id,
            'description' => 'Beli lampu',
            'amount' => 50000,
            'expense_date' => '2026-01-15',
            'recorded_by' => $pengurus->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($pengurus)->put(route('pengurus.expenses.update', $expense), [
            'category_id' => $category->id,
            'description' => 'Beli lampu dan kabel',
            'amount' => 65000,
            'expense_date' => '2026-01-20',
        ]);

        $response->assertRedirect(route('pengurus.expenses.index'));
        $this->assertDatabaseHas('expenses', ['id' => $expense->id, 'amount' => 65000, 'description' => 'Beli lampu dan kabel']);
        $this->assertDatabaseHas('audit_logs', [
            'auditable_id' => $expense->id,
            'action' => 'UPDATE_EXPENSE',
        ]);
    }

    public function test_pengurus_can_void_expense(): void
    {
        $pengurus = $this->createPengurus();
        $category = $this->createCategory();

        $expense = Expense::create([
            'category_id' => $category->id,
            'description' => 'Beli cat',
            'amount' => 100000,
            'expense_date' => '2026-03-05',
            'recorded_by' => $pengurus->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($pengurus)->patch(route('pengurus.expenses.void', $expense));
        $response->assertRedirect();

        $this->assertDatabaseHas('expenses', ['id' => $expense->id, 'status' => 'void']);
        $this->assertDatabaseHas('audit_logs', [
            'auditable_id' => $expense->id,
            'action' => 'VOID_EXPENSE',
        ]);
    }

    public function test_void_expense_hidden_from_index(): void
    {
        $pengurus = $this->createPengurus();
        $category = $this->createCategory();

        $expense = Expense::create([
            'category_id' => $category->id,
            'description' => 'Pengeluaran dibatalkan',
            'amount' => 20000,
            'expense_date' => '2026-04-01',
            'recorded_by' => $pengurus->id,
            'status' => 'void',
        ]);

        $response = $this->actingAs($pengurus)->get(route('pengurus.expenses.index'));
        $response->assertStatus(200);
        $response->assertDontSee('Pengeluaran dibatalkan');
    }

    public function test_warga_cannot_access_expense_module(): void
    {
        $warga = $this->createWarga();

        $this->actingAs($warga)->get(route('pengurus.expenses.index'))->assertStatus(403);
        $this->actingAs($warga)->get(route('pengurus.expenses.create'))->assertStatus(403);
        $this->actingAs($warga)->get(route('pengurus.expense-categories.index'))->assertStatus(403);
    }

    public function test_pengurus_can_create_and_update_category(): void
    {
        $pengurus = $this->createPengurus();

        $this->actingAs($pengurus)->post(route('pengurus.expense-categories.store'), [
            'name' => 'Konsumsi',
            'description' => 'Makanan dan minuman',
        ])->assertRedirect();

        $this->assertDatabaseHas('expense_categories', ['name' => 'Konsumsi']);

        $category = ExpenseCategory::where('name', 'Konsumsi')->firstOrFail();

        $this->actingAs($pengurus)->put(route('pengurus.expense-categories.update', $category), [
            'name' => 'Konsumsi Rapat',
            'description' => 'Makanan dan minuman rapat',
            'is_active' => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('expense_categories', ['id' => $category->id, 'name' => 'Konsumsi Rapat']);
    }

    public function test_pengurus_can_destroy_category(): void
    {
        $pengurus = $this->createPengurus();
        $category = $this->createCategory('Operasional');

        Expense::create([
            'category_id' => $category->id,
            'description' => 'Pengeluaran terkait kategori',
            'amount' => 30000,
            'expense_date' => '2026-05-01',
            'recorded_by' => $pengurus->id,
            'status' => 'active',
        ]);

        $this->actingAs($pengurus)->delete(route('pengurus.expense-categories.destroy', $category))->assertRedirect();

        $this->assertDatabaseMissing('expense_categories', ['id' => $category->id]);

        // Saat kategori dihapus, refrensi pada expense menjadi null (cascade nullOnDelete)
        $this->assertDatabaseHas('expenses', [
            'category_id' => null,
            'description' => 'Pengeluaran terkait kategori',
        ]);
    }
}
