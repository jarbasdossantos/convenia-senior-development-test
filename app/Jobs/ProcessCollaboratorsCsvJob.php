<?php

namespace App\Jobs;

use App\Mail\ProcessCollaboratorsCsvJobNotification;
use App\Models\Collaborator;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;

class ProcessCollaboratorsCsvJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(protected string $path, protected int $userId)
    {
    }

    public function handle(): void
    {
        Log::info('Processing CSV file', ['path' => $this->path, 'user_id' => $this->userId]);

        $user = User::findOrFail($this->userId);
        $fullPath = Storage::path($this->path);

        $processed = 0;
        $errors = [];

        $reader = Reader::from($fullPath)->setDelimiter(',');
        $reader->setHeaderOffset(0);

        foreach ($reader as $i => $row) {
            try {
                Collaborator::firstOrCreate(
                    [
                    'cpf' => $row['cpf'],
                    'email' => $row['email'],
                    ],
                    [
                        'user_id' => $this->userId,
                        'name' => $row['name'],
                        'email' => $row['email'],
                        'city' => $row['city'],
                        'state' => $row['state'],
                    ]
                );
            } catch (\Exception $e) {
                $errors[] = [
                    'line' => $i + 1,
                    'message' => $e->getMessage(),
                ];

                Log::error('Error processing line ' . ($i + 1), ['message' => $e->getMessage()]);
            } finally {
                $processed++;
            }
        }

        Mail::to($user->email)->queue(new ProcessCollaboratorsCsvJobNotification($processed, $errors));
    }
}
