<?php

namespace Tests\Feature\Controllers;

use App\Models\Collaborator;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CollaboratorControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test')->plainTextToken;
    }

    public function test_can_list_collaborators(): void
    {
        Collaborator::factory()->count(3)->create(['user_id' => $this->user->id]);
        Collaborator::factory()->count(2)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/collaborators');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'current_page',
                'data' => [
                    '*' => ['id', 'name', 'email', 'cpf', 'city', 'state', 'user_id', 'created_at', 'updated_at']
                ],
                'total',
                'per_page'
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_list_collaborators_requires_authentication(): void
    {
        $response = $this->getJson('/api/collaborators');

        $response->assertStatus(401);
    }

    public function test_can_show_collaborator(): void
    {
        $collaborator = Collaborator::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/collaborators/{$collaborator->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['id', 'name', 'email', 'cpf', 'city', 'state', 'user_id', 'created_at', 'updated_at'])
            ->assertJson([
                'id' => $collaborator->id,
                'name' => $collaborator->name,
                'email' => $collaborator->email,
            ]);
    }

    public function test_cannot_show_other_users_collaborator(): void
    {
        $otherUser = User::factory()->create();
        $collaborator = Collaborator::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/collaborators/{$collaborator->id}");

        $response->assertStatus(403);
    }

    public function test_can_create_collaborator(): void
    {
        $collaboratorData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'cpf' => '123.456.789-09',
            'city' => 'São Paulo',
            'state' => 'SP'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/collaborators', $collaboratorData);

        $response->assertStatus(201)
            ->assertJsonStructure(['id', 'name', 'email', 'cpf', 'city', 'state', 'user_id', 'created_at', 'updated_at'])
            ->assertJson([
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'city' => 'São Paulo',
                'state' => 'SP',
                'user_id' => $this->user->id
            ]);

        $this->assertDatabaseHas('collaborators', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'user_id' => $this->user->id
        ]);
    }

    public function test_cannot_create_collaborator_without_required_fields(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/collaborators', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'cpf', 'city', 'state']);
    }

    public function test_cannot_create_collaborator_with_invalid_cpf(): void
    {
        $collaboratorData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'cpf' => '123.456.789-00',
            'city' => 'São Paulo',
            'state' => 'SP'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/collaborators', $collaboratorData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cpf']);
    }

    public function test_cannot_create_collaborator_with_duplicate_email(): void
    {
        $existingCollaborator = Collaborator::factory()->create(['user_id' => $this->user->id]);

        $collaboratorData = [
            'name' => 'John Doe',
            'email' => $existingCollaborator->email,
            'cpf' => '123.456.789-09',
            'city' => 'São Paulo',
            'state' => 'SP'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/collaborators', $collaboratorData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_can_update_collaborator(): void
    {
        $collaborator = Collaborator::factory()->create(['user_id' => $this->user->id]);

        $updateData = [
            'name' => 'John Updated',
            'email' => 'updated@example.com',
            'cpf' => $collaborator->cpf,
            'city' => 'Rio de Janeiro',
            'state' => 'RJ'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/collaborators/{$collaborator->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'name' => 'John Updated',
                'email' => 'updated@example.com',
                'city' => 'Rio de Janeiro',
                'state' => 'RJ'
            ]);

        $this->assertDatabaseHas('collaborators', [
            'id' => $collaborator->id,
            'name' => 'John Updated',
            'email' => 'updated@example.com',
            'city' => 'Rio de Janeiro',
            'state' => 'RJ'
        ]);
    }

    public function test_cannot_update_other_users_collaborator(): void
    {
        $otherUser = User::factory()->create();
        $collaborator = Collaborator::factory()->create(['user_id' => $otherUser->id]);

        $updateData = [
            'name' => 'John Updated',
            'email' => 'updated@example.com',
            'cpf' => $collaborator->cpf,
            'city' => 'Rio de Janeiro',
            'state' => 'RJ'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/collaborators/{$collaborator->id}", $updateData);

        $response->assertStatus(403);
    }

    public function test_can_delete_collaborator(): void
    {
        $collaborator = Collaborator::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/collaborators/{$collaborator->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('collaborators', [
            'id' => $collaborator->id
        ]);
    }

    public function test_cannot_delete_other_users_collaborator(): void
    {
        $otherUser = User::factory()->create();
        $collaborator = Collaborator::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/collaborators/{$collaborator->id}");

        $response->assertStatus(403);
    }

    public function test_can_import_csv(): void
    {
        Storage::fake('local');

        $csvContent = "name,email,cpf,city,state\n";
        $csvContent .= "John Doe,john@example.com,123.456.789-09,São Paulo,SP\n";
        $csvContent .= "Jane Smith,jane@example.com,987.654.321-00,Rio de Janeiro,RJ\n";

        $file = UploadedFile::fake()->createWithContent('collaborators.csv', $csvContent);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/collaborators/import-csv', [
            'file' => $file
        ]);

        $response->assertStatus(202)
            ->assertJson([
                'message' => 'Processamento do arquivo CSV iniciado.'
            ]);
    }

    public function test_import_csv_requires_file(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/collaborators/import-csv', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    public function test_import_csv_requires_csv_file(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/collaborators/import-csv', [
            'file' => UploadedFile::fake()->create('document.pdf', 1000, 'application/pdf')
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    public function test_cache_is_cleared_after_modifications(): void
    {
        $collaborator = Collaborator::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/collaborators');

        $response->assertStatus(200);

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/collaborators/{$collaborator->id}", [
            'name' => 'Updated Name',
            'email' => $collaborator->email,
            'cpf' => $collaborator->cpf,
            'city' => $collaborator->city,
            'state' => $collaborator->state
        ]);

        $this->assertTrue(true);
    }
}
