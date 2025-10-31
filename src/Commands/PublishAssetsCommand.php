<?php

namespace CleaniqueCoders\Flowstone\Commands;

use Illuminate\Console\Command;

class PublishAssetsCommand extends Command
{
    protected $signature = 'flowstone:publish-assets {--force : Overwrite any existing files}';

    protected $description = 'Publish Flowstone UI assets to public/vendor/flowstone';

    public function handle(): int
    {
        $this->call('vendor:publish', [
            '--tag' => 'flowstone-ui-assets',
            '--force' => (bool) $this->option('force'),
        ]);

        $this->info('Flowstone UI assets published.');

        return self::SUCCESS;
    }
}
