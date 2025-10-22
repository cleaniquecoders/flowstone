<?php

namespace CleaniqueCoders\LaravelWorklfow\Commands;

use Illuminate\Console\Command;

class LaravelWorklfowCommand extends Command
{
    public $signature = 'laravel-worklfow';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
