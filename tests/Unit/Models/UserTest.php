<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_user(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ];

        $user = User::create($userData);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_user_fillable_attributes(): void
    {
        $user = new User();

        $expectedFillable = [
            'name',
            'email',
            'password',
        ];

        $this->assertEquals($expectedFillable, $user->getFillable());
    }

    public function test_user_hidden_attributes(): void
    {
        $user = new User();

        $expectedHidden = [
            'password',
            'remember_token',
        ];

        $this->assertEquals($expectedHidden, $user->getHidden());
    }

    public function test_user_casts(): void
    {
        $user = new User();

        $expectedCasts = [
            'id' => 'int',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];

        $this->assertEquals($expectedCasts, $user->getCasts());
    }

    public function test_user_factory_creates_valid_data(): void
    {
        $user = User::factory()->create();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
        ]);

        $this->assertNotEmpty($user->name);
        $this->assertNotEmpty($user->email);
        $this->assertNotEmpty($user->password);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_user_factory_unverified_state(): void
    {
        $user = User::factory()->unverified()->create();

        $this->assertNull($user->email_verified_at);
    }

    public function test_password_is_hashed(): void
    {
        $user = User::factory()->create([
            'password' => 'plainpassword'
        ]);

        $this->assertNotEquals('plainpassword', $user->password);
        $this->assertTrue(Hash::check('plainpassword', $user->password));
    }
}
