<?php

namespace CleaniqueCoders\Flowstone\Commands;

use Illuminate\Console\Command;

class FlowstoneCommand extends Command
{
    public $signature = 'flowstone';

    public $description = 'Flowstone workflow management command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
