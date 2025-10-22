<?php

namespace App\Jobs;

use App\Mail\ProcessCollaboratorsCsvJobNotification;
use App\Models\Collaborator;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;

class ProcessCollaboratorsCsvJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(protected string $path, protected int $userId)
    {
        $user = User::findOrFail($this->userId);
        $fullPath = Storage::path($this->path);

        $processed = 0;
        $errors = [];

        $reader = Reader::from($fullPath)->setDelimiter(',');
        $reader->setHeaderOffset(0);

        foreach ($reader as $i => $row) {
            try {
                $collaborator = Collaborator::firstOrCreate([
                    'cpf' => $row['cpf'],
                    'email' => $row['email'],
                ], [
                    'user_id' => $this->userId,
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'city' => $row['city'],
                    'state' => $row['state'],
                ]);

                $processed++;
            } catch (\Exception $e) {
                $errors[] = [
                    'line' => $i + 1,
                    'message' => $e->getMessage(),
                ];

                $processed++;
            }
        }

        Mail::to($user->email)->queue(new ProcessCollaboratorsCsvJobNotification($processed, $errors));
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
    }
}
