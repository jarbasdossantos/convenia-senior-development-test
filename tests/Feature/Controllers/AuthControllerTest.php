<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'access_token',
                     'token_type',
                     'expires_in'
                 ]);

        $this->assertEquals('bearer', $response->json('token_type'));
        $this->assertNotEmpty($response->json('access_token'));
    }

    public function test_cannot_login_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('correctpassword')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(401)
                 ->assertJson([
                     'message' => 'Credenciais inválidas'
                 ]);
    }

    public function test_cannot_login_with_nonexistent_user(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'anypassword'
        ]);

        $response->assertStatus(401)
                 ->assertJson([
                     'message' => 'Credenciais inválidas'
                 ]);
    }

    public function test_login_requires_email_and_password(): void
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_login_requires_valid_email_format(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'invalid-email',
            'password' => 'password123'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }
}
