<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\User;
use App\Services\AssetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetTest extends TestCase
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

    private function createCategory(): AssetCategory
    {
        return AssetCategory::create([
            'name' => 'Elektronik',
            'description' => 'Peralatan elektronik',
            'is_active' => true,
        ]);
    }

    public function test_pengurus_can_view_assets_index(): void
    {
        $pengurus = $this->createPengurus();

        $response = $this->actingAs($pengurus)->get('/pengurus/assets');
        $response->assertStatus(200);
        $response->assertSee('Inventaris / Aset RT');
    }

    public function test_pengurus_can_create_asset(): void
    {
        $pengurus = $this->createPengurus();
        $category = $this->createCategory();

        $response = $this->actingAs($pengurus)->post('/pengurus/assets', [
            'category_id' => $category->id,
            'name' => 'Proyektor',
            'quantity' => 1,
            'unit' => 'pcs',
            'condition' => 'baik',
            'status' => 'tersedia',
            'location' => 'Balai Warga',
        ]);

        $response->assertRedirect('/pengurus/assets');
        $this->assertDatabaseHas('assets', ['name' => 'Proyektor']);
        $this->assertDatabaseHas('asset_movements', ['type' => 'addition', 'quantity' => 1]);
    }

    public function test_pengurus_can_view_asset_detail(): void
    {
        $pengurus = $this->createPengurus();
        $category = $this->createCategory();
        $asset = Asset::create([
            'asset_code' => 'AST-0001',
            'category_id' => $category->id,
            'name' => 'Proyektor',
            'quantity' => 1,
            'unit' => 'pcs',
            'condition' => 'baik',
            'status' => 'tersedia',
            'created_by' => $pengurus->id,
        ]);

        $response = $this->actingAs($pengurus)->get("/pengurus/assets/{$asset->id}");
        $response->assertStatus(200);
        $response->assertSee('Proyektor');
        $response->assertSee('AST-0001');
    }

    public function test_warga_cannot_access_pengurus_assets(): void
    {
        $warga = User::create([
            'username' => 'A01',
            'password' => bcrypt('password'),
            'role' => 'warga',
        ]);

        $this->actingAs($warga)->get('/pengurus/assets')->assertStatus(403);
    }

    public function test_warga_can_view_own_assets_page(): void
    {
        $warga = User::create([
            'username' => 'A01',
            'password' => bcrypt('password'),
            'role' => 'warga',
        ]);

        $response = $this->actingAs($warga)->get('/warga/assets');
        $response->assertStatus(200);
        $response->assertSee('Inventaris / Aset RT');
    }

    public function test_asset_code_generation(): void
    {
        $service = new AssetService;
        $code = $service->generateAssetCode();
        $this->assertMatchesRegularExpression('/^AST-\d{4}$/', $code);
        $this->assertEquals('AST-0001', $code);
    }

    public function test_asset_code_increments(): void
    {
        Asset::create([
            'asset_code' => 'AST-0001',
            'category_id' => $this->createCategory()->id,
            'name' => 'Test',
            'quantity' => 1,
            'condition' => 'baik',
            'status' => 'tersedia',
            'created_by' => $this->createPengurus()->id,
        ]);

        $service = new AssetService;
        $code = $service->generateAssetCode();
        $this->assertEquals('AST-0002', $code);
    }

    public function test_asset_report_route(): void
    {
        $pengurus = $this->createPengurus();

        $response = $this->actingAs($pengurus)->get('/pengurus/reports/assets');
        $response->assertStatus(200);
        $response->assertSee('Laporan Inventaris');
    }

    public function test_export_inventaris_csv(): void
    {
        $pengurus = $this->createPengurus();

        $response = $this->actingAs($pengurus)->get('/pengurus/reports/export/inventaris');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_pengurus_can_create_category(): void
    {
        $pengurus = $this->createPengurus();

        $response = $this->actingAs($pengurus)->post('/pengurus/categories', [
            'name' => 'Olahraga',
            'description' => 'Peralatan olahraga',
        ]);

        $response->assertRedirect('/pengurus/categories');
        $this->assertDatabaseHas('asset_categories', ['name' => 'Olahraga']);
    }
}
