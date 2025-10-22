<?php

namespace Tests\Unit\Models;

use App\Models\Collaborator;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CollaboratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_collaborator(): void
    {
        $user = User::factory()->create();

        $collaboratorData = [
            'user_id' => $user->id,
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'cpf' => '123.456.789-09',
            'city' => 'São Paulo',
            'state' => 'SP',
        ];

        $collaborator = Collaborator::create($collaboratorData);

        $this->assertDatabaseHas('collaborators', [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'cpf' => '12345678909',
        ]);

        $this->assertEquals($user->id, $collaborator->user_id);
        $this->assertEquals('João Silva', $collaborator->name);
        $this->assertEquals('joao@example.com', $collaborator->email);
        $this->assertEquals('12345678909', $collaborator->cpf);
        $this->assertEquals('São Paulo', $collaborator->city);
        $this->assertEquals('SP', $collaborator->state);
    }

    public function test_collaborator_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $collaborator = Collaborator::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $collaborator->user);
        $this->assertEquals($user->id, $collaborator->user->id);
    }

    public function test_collaborator_fillable_attributes(): void
    {
        $collaborator = new Collaborator();

        $expectedFillable = [
            'user_id',
            'name',
            'email',
            'cpf',
            'city',
            'state',
        ];

        $this->assertEquals($expectedFillable, $collaborator->getFillable());
    }

    public function test_collaborator_factory_creates_valid_data(): void
    {
        $collaborator = Collaborator::factory()->create();

        $this->assertDatabaseHas('collaborators', [
            'id' => $collaborator->id,
        ]);

        $this->assertNotEmpty($collaborator->name);
        $this->assertNotEmpty($collaborator->email);
        $this->assertNotEmpty($collaborator->cpf);
        $this->assertNotEmpty($collaborator->city);
        $this->assertNotEmpty($collaborator->state);
        $this->assertNotNull($collaborator->user_id);
    }
}
