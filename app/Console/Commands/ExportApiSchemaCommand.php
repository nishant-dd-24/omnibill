<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ExportApiSchemaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:export-schema';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export the OpenAPI schema using Scramble';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $path = public_path('openapi.json');

        // We can just call scramble:export natively
        $this->call('scramble:export', [
            '--path' => 'public/openapi.json',
        ]);

        $this->info("OpenAPI schema exported successfully to {$path}");
    }
}
