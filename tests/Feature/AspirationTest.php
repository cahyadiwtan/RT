<?php

namespace Tests\Feature;

use App\Models\Aspiration;
use App\Models\House;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AspirationTest extends TestCase
{
    use RefreshDatabase;

    protected User $wargaUser;

    protected User $otherWargaUser;

    protected User $pengurusUser;

    protected Resident $wargaResident;

    protected Resident $otherWargaResident;

    protected House $house;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Rumah
        $this->house = House::create([
            'block' => 'A',
            'house_number' => '01',
            'address' => 'Jl. Mawar No. 1',
            'status' => 'ditempati',
        ]);

        // Setup Resident Warga 1
        $this->wargaResident = Resident::create([
            'house_id' => $this->house->id,
            'nik' => '3201010101010001',
            'nama_lengkap' => 'Budi Santoso',
            'jenis_kelamin' => 'L',
            'status_warga' => 'aktif',
            'hubungan_dalam_keluarga' => 'kepala_keluarga',
            'is_verified' => true,
        ]);

        // User Warga 1
        $this->wargaUser = User::create([
            'resident_id' => $this->wargaResident->id,
            'username' => 'A01',
            'password' => bcrypt('password'),
            'role' => 'warga',
            'is_active' => true,
        ]);

        // Setup Resident Warga 2 (Other)
        $this->otherWargaResident = Resident::create([
            'house_id' => $this->house->id,
            'nik' => '3201010101010002',
            'nama_lengkap' => 'Siti Aminah',
            'jenis_kelamin' => 'P',
            'status_warga' => 'aktif',
            'hubungan_dalam_keluarga' => 'istri',
            'is_verified' => true,
        ]);

        // User Warga 2
        $this->otherWargaUser = User::create([
            'resident_id' => $this->otherWargaResident->id,
            'username' => 'A02',
            'password' => bcrypt('password'),
            'role' => 'warga',
            'is_active' => true,
        ]);

        // User Pengurus
        $this->pengurusUser = User::create([
            'username' => 'admin',
            'password' => bcrypt('password'),
            'role' => 'pengurus',
            'is_active' => true,
        ]);
    }

    public function test_warga_can_view_own_aspirations_index(): void
    {
        $response = $this->actingAs($this->wargaUser)->get('/warga/aspirations');
        $response->assertStatus(200);
    }

    public function test_warga_can_submit_aspiration_with_file(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('keluhan.jpg');

        $response = $this->actingAs($this->wargaUser)->post('/warga/aspirations', [
            'category' => 'keamanan',
            'title' => 'Pencurian helm di parkir',
            'description' => 'Ada pencurian helm kemarin siang mohon dipasang CCTV.',
            'attachment' => $file,
        ]);

        $response->assertRedirect('/warga/aspirations');
        $this->assertDatabaseHas('aspirations', [
            'resident_id' => $this->wargaResident->id,
            'category' => 'keamanan',
            'title' => 'Pencurian helm di parkir',
        ]);

        // Pastikan file terupload ke storage public/attachments
        $aspiration = Aspiration::where('title', 'Pencurian helm di parkir')->first();
        $this->assertNotNull($aspiration->attachment);
        Storage::disk('public')->assertExists($aspiration->attachment);

        // Pastikan tercatat ke audit logs
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->wargaUser->id,
            'action' => 'CREATE_ASPIRATION',
            'auditable_type' => Aspiration::class,
            'auditable_id' => $aspiration->id,
        ]);
    }

    public function test_warga_can_view_own_aspiration_detail(): void
    {
        $aspiration = Aspiration::create([
            'resident_id' => $this->wargaResident->id,
            'category' => 'kebersihan',
            'title' => 'Sampah menumpuk',
            'description' => 'Tolong angkut sampah.',
            'status' => 'submitted',
        ]);

        $response = $this->actingAs($this->wargaUser)->get("/warga/aspirations/{$aspiration->id}");
        $response->assertStatus(200);
        $response->assertSee('Sampah menumpuk');
    }

    public function test_warga_cannot_view_others_aspiration_detail(): void
    {
        $aspiration = Aspiration::create([
            'resident_id' => $this->wargaResident->id,
            'category' => 'kebersihan',
            'title' => 'Sampah menumpuk',
            'description' => 'Tolong angkut sampah.',
            'status' => 'submitted',
        ]);

        $response = $this->actingAs($this->otherWargaUser)->get("/warga/aspirations/{$aspiration->id}");
        $response->assertStatus(403);
    }

    public function test_pengurus_can_view_all_aspirations_index(): void
    {
        $response = $this->actingAs($this->pengurusUser)->get('/pengurus/aspirations');
        $response->assertStatus(200);
    }

    public function test_pengurus_can_update_aspiration_status_and_add_comment(): void
    {
        $aspiration = Aspiration::create([
            'resident_id' => $this->wargaResident->id,
            'category' => 'fasilitas',
            'title' => 'Lampu jalan mati',
            'description' => 'Lampu jalan mati.',
            'status' => 'submitted',
        ]);

        $response = $this->actingAs($this->pengurusUser)->post("/pengurus/aspirations/{$aspiration->id}/updates", [
            'status' => 'in_progress',
            'comment' => 'Sedang diganti bohlamnya oleh petugas.',
        ]);

        $response->assertRedirect("/pengurus/aspirations/{$aspiration->id}");

        $this->assertDatabaseHas('aspirations', [
            'id' => $aspiration->id,
            'status' => 'in_progress',
        ]);

        $this->assertDatabaseHas('aspiration_updates', [
            'aspiration_id' => $aspiration->id,
            'user_id' => $this->pengurusUser->id,
            'status' => 'in_progress',
            'comment' => 'Sedang diganti bohlamnya oleh petugas.',
        ]);

        // Verifikasi audit log
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->pengurusUser->id,
            'action' => 'UPDATE_ASPIRATION',
            'auditable_type' => Aspiration::class,
            'auditable_id' => $aspiration->id,
        ]);
    }
}
