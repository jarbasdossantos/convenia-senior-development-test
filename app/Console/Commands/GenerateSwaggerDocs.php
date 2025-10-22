<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class GenerateSwaggerDocs extends Command
{
    protected $signature = 'swagger:generate';
    
    protected $description = 'Generate Swagger documentation';

    public function handle()
    {
        $this->info('Generating Swagger documentation...');
        
        $process = new Process(['php', 'artisan', 'l5-swagger:generate']);
        $process->run();
        
        if ($process->isSuccessful()) {
            $this->info('Swagger documentation generated successfully!');
            $this->info('Access the documentation at: ' . url('api/documentation'));
        } else {
            $this->error('Failed to generate Swagger documentation');
            $this->error($process->getErrorOutput());
        }
        
        return Command::SUCCESS;
    }
}