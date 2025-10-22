<?php

namespace Tests\Unit\Jobs;

use App\Jobs\ProcessCollaboratorsCsvJob;
use App\Mail\ProcessCollaboratorsCsvJobNotification;
use App\Models\Collaborator;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProcessCollaboratorsCsvJobTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_process_valid_csv_file(): void
    {
        Mail::fake();
        Storage::fake('local');

        $csvContent = "name,email,cpf,city,state\n";
        $csvContent .= "John Doe,john@example.com,123.456.789-09,São Paulo,SP\n";
        $csvContent .= "Jane Smith,jane@example.com,987.654.321-00,Rio de Janeiro,RJ\n";

        $file = UploadedFile::fake()->createWithContent('collaborators.csv', $csvContent);
        $path = $file->store('uploads/collaborators');

        $job = new ProcessCollaboratorsCsvJob($path, $this->user->id);
        $job->handle();

        $this->assertDatabaseHas('collaborators', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'cpf' => '12345678909',
            'city' => 'São Paulo',
            'state' => 'SP',
            'user_id' => $this->user->id,
        ]);

        $this->assertDatabaseHas('collaborators', [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'cpf' => '98765432100',
            'city' => 'Rio de Janeiro',
            'state' => 'RJ',
            'user_id' => $this->user->id,
        ]);

        Mail::assertQueued(ProcessCollaboratorsCsvJobNotification::class, function ($mail) {
            return $mail->hasTo($this->user->email);
        });
    }

    public function test_does_not_create_duplicate_collaborators(): void
    {
        Mail::fake();
        Storage::fake('local');

        $existingCollaborator = Collaborator::factory()->create([
            'user_id' => $this->user->id,
            'email' => 'john@example.com',
            'cpf' => '12345678909',
        ]);

        $csvContent = "name,email,cpf,city,state\n";
        $csvContent .= "John Updated,john@example.com,123.456.789-09,São Paulo,SP\n";

        $file = UploadedFile::fake()->createWithContent('collaborators.csv', $csvContent);
        $path = $file->store('uploads/collaborators');

        $job = new ProcessCollaboratorsCsvJob($path, $this->user->id);
        $job->handle();

        $this->assertDatabaseHas('collaborators', [
            'id' => $existingCollaborator->id,
            'name' => $existingCollaborator->name,
            'email' => 'john@example.com',
            'cpf' => '12345678909',
        ]);

        $this->assertEquals(1, Collaborator::where('email', 'john@example.com')->count());
    }

    public function test_handles_csv_with_missing_columns(): void
    {
        Mail::fake();
        Storage::fake('local');

        $csvContent = "name,email,cpf,city,state\n";
        $csvContent .= "John Doe,john@example.com,123.456.789-09,São Paulo,SP\n";
        $csvContent .= "Invalid Row,missing@example.com\n";

        $file = UploadedFile::fake()->createWithContent('collaborators.csv', $csvContent);
        $path = $file->store('uploads/collaborators');

        $job = new ProcessCollaboratorsCsvJob($path, $this->user->id);
        $job->handle();

        $this->assertDatabaseHas('collaborators', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        Mail::assertQueued(ProcessCollaboratorsCsvJobNotification::class, function ($mail) {
            return $mail->hasTo($this->user->email);
        });
    }

    public function test_handles_empty_csv_file(): void
    {
        Mail::fake();
        Storage::fake('local');

        $csvContent = "name,email,cpf,city,state\n";

        $file = UploadedFile::fake()->createWithContent('collaborators.csv', $csvContent);
        $path = $file->store('uploads/collaborators');

        $job = new ProcessCollaboratorsCsvJob($path, $this->user->id);
        $job->handle();

        $this->assertEquals(0, Collaborator::count());

        Mail::assertQueued(ProcessCollaboratorsCsvJobNotification::class, function ($mail) {
            return $mail->hasTo($this->user->email);
        });
    }

    public function test_sends_email_with_processing_results(): void
    {
        Mail::fake();
        Storage::fake('local');

        $csvContent = "name,email,cpf,city,state\n";
        $csvContent .= "John Doe,john@example.com,123.456.789-09,São Paulo,SP\n";
        $csvContent .= "Jane Smith,jane@example.com,987.654.321-00,Rio de Janeiro,RJ\n";

        $file = UploadedFile::fake()->createWithContent('collaborators.csv', $csvContent);
        $path = $file->store('uploads/collaborators');

        $job = new ProcessCollaboratorsCsvJob($path, $this->user->id);
        $job->handle();

        Mail::assertQueued(ProcessCollaboratorsCsvJobNotification::class, function ($mail) use ($path) {
            return $mail->hasTo($this->user->email);
        });

        $mails = Mail::queued(ProcessCollaboratorsCsvJobNotification::class);
        $this->assertCount(1, $mails);
    }

    public function test_handles_nonexistent_user(): void
    {
        Mail::fake();
        Storage::fake('local');

        $csvContent = "name,email,cpf,city,state\n";
        $csvContent .= "John Doe,john@example.com,123.456.789-09,São Paulo,SP\n";

        $file = UploadedFile::fake()->createWithContent('collaborators.csv', $csvContent);
        $path = $file->store('uploads/collaborators');

        $job = new ProcessCollaboratorsCsvJob($path, 99999);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $job->handle();
    }

    public function test_cleans_cpf_formatting(): void
    {
        Mail::fake();
        Storage::fake('local');

        $csvContent = "name,email,cpf,city,state\n";
        $csvContent .= "John Doe,john@example.com,123.456.789-09,São Paulo,SP\n";
        $csvContent .= "Jane Smith,jane@example.com,987.654.321-00,Rio de Janeiro,RJ\n";

        $file = UploadedFile::fake()->createWithContent('collaborators.csv', $csvContent);
        $path = $file->store('uploads/collaborators');

        $job = new ProcessCollaboratorsCsvJob($path, $this->user->id);
        $job->handle();

        $this->assertDatabaseHas('collaborators', [
            'name' => 'John Doe',
            'cpf' => '12345678909',
        ]);

        $this->assertDatabaseHas('collaborators', [
            'name' => 'Jane Smith',
            'cpf' => '98765432100',
        ]);
    }
}
