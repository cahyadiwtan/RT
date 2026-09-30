<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HouseControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_pengurus_can_view_and_create_house(): void
    {
        $admin = User::create([
            'username' => 'admin',
            'password' => bcrypt('password'),
            'role' => 'pengurus',
        ]);

        $response = $this->actingAs($admin)->get('/pengurus/houses');
        $response->assertStatus(200);

        $responsePost = $this->actingAs($admin)->post('/pengurus/houses', [
            'block' => 'C',
            'house_number' => '01',
            'status' => 'ditempati',
            'address' => 'Jl. Anggrek No. 1',
        ]);

        $responsePost->assertRedirect('/pengurus/houses');
        $this->assertDatabaseHas('houses', ['block' => 'C', 'house_number' => '01']);
    }

    public function test_warga_cannot_access_pengurus_houses_management(): void
    {
        $warga = User::create([
            'username' => 'A01',
            'password' => bcrypt('password'),
            'role' => 'warga',
        ]);

        $response = $this->actingAs($warga)->get('/pengurus/houses');
        $response->assertStatus(403);
    }
}
