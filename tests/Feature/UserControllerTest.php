<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_pengurus_can_view_and_create_user(): void
    {
        $admin = User::create([
            'username' => 'admin',
            'password' => bcrypt('password'),
            'role' => 'pengurus',
        ]);

        $response = $this->actingAs($admin)->get('/pengurus/users');
        $response->assertStatus(200);

        $responsePost = $this->actingAs($admin)->post('/pengurus/users', [
            'username' => 'bendahara01',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'role' => 'pengurus',
            'is_active' => '1',
        ]);

        $responsePost->assertRedirect('/pengurus/users');
        $this->assertDatabaseHas('users', ['username' => 'bendahara01', 'role' => 'pengurus']);
    }

    public function test_warga_cannot_access_pengurus_user_management(): void
    {
        $warga = User::create([
            'username' => 'A01',
            'password' => bcrypt('password'),
            'role' => 'warga',
        ]);

        $response = $this->actingAs($warga)->get('/pengurus/users');
        $response->assertStatus(403);
    }

    public function test_cannot_delete_own_account(): void
    {
        $admin = User::create([
            'username' => 'admin',
            'password' => bcrypt('password'),
            'role' => 'pengurus',
        ]);

        $response = $this->actingAs($admin)->delete("/pengurus/users/{$admin->id}");
        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_cannot_deactivate_own_account(): void
    {
        $admin = User::create([
            'username' => 'admin',
            'password' => bcrypt('password'),
            'role' => 'pengurus',
        ]);

        $response = $this->actingAs($admin)->post("/pengurus/users/{$admin->id}/toggle-active");
        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'is_active' => true]);
    }

    public function test_cannot_demote_self_to_warga(): void
    {
        $admin = User::create([
            'username' => 'admin',
            'password' => bcrypt('password'),
            'role' => 'pengurus',
        ]);

        $response = $this->actingAs($admin)->put("/pengurus/users/{$admin->id}", [
            'username' => 'admin',
            'role' => 'warga',
            'is_active' => '1',
        ]);

        $response->assertRedirect('/pengurus/users');

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'role' => 'pengurus']);
    }
}