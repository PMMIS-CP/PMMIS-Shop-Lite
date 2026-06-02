<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

#[Signature('logs:clear')]
#[Description('Clear all log files in storage/logs')]
class ClearLogs extends Command
{
    public function handle()
    {
        $logFiles = File::files(storage_path('logs'));

        foreach ($logFiles as $file) {
            if ($file->getExtension() === 'log') {
                File::put($file->getPathname(), '');
            }
        }

        $this->info('All log files have been cleared!');
    }
}