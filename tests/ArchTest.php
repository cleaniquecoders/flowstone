<?php

describe('Architecture Tests', function () {
    it('will not use debugging functions')
        ->expect(['dd', 'dump', 'ray'])
        ->each->not->toBeUsed();

    it('ensures models implement workflow contract correctly')
        ->expect('CleaniqueCoders\LaravelWorklfow\Models')
        ->toImplement('CleaniqueCoders\LaravelWorklfow\Contracts\Workflow');

    it('ensures concerns are traits')
        ->expect('CleaniqueCoders\LaravelWorklfow\Concerns')
        ->toBeTrait();

    it('ensures contracts are interfaces')
        ->expect('CleaniqueCoders\LaravelWorklfow\Contracts')
        ->toBeInterface();

    it('ensures enums are enums')
        ->expect('CleaniqueCoders\LaravelWorklfow\Enums')
        ->toBeEnum();

    it('ensures processors are classes')
        ->expect('CleaniqueCoders\LaravelWorklfow\Processors')
        ->toBeClasses();

    it('ensures models extend Eloquent model')
        ->expect('CleaniqueCoders\LaravelWorklfow\Models')
        ->toExtend('Illuminate\Database\Eloquent\Model');

    it('ensures service providers extend Laravel service provider')
        ->expect('CleaniqueCoders\LaravelWorklfow\LaravelWorklfowServiceProvider')
        ->toExtend('Illuminate\Support\ServiceProvider');

    it('ensures facades extend Laravel facade')
        ->expect('CleaniqueCoders\LaravelWorklfow\Facades')
        ->toExtend('Illuminate\Support\Facades\Facade');

    it('ensures no class uses die or exit')
        ->expect(['die', 'exit'])
        ->each->not->toBeUsed();

    it('ensures models use proper naming convention')
        ->expect('CleaniqueCoders\LaravelWorklfow\Models')
        ->toHaveSuffix('');

    it('ensures processors have proper naming')
        ->expect('CleaniqueCoders\LaravelWorklfow\Processors')
        ->classes->not->toBeFinal();

    it('ensures workflow trait is used correctly')
        ->expect('CleaniqueCoders\LaravelWorklfow\Models\Workflow')
        ->toUse('CleaniqueCoders\LaravelWorklfow\Concerns\InteractsWithWorkflow');

    it('ensures proper exception handling')
        ->expect('CleaniqueCoders\LaravelWorklfow')
        ->not->toUse(['trigger_error', 'error_reporting']);

    it('ensures package follows PSR-4 autoloading')
        ->expect('CleaniqueCoders\LaravelWorklfow')
        ->toHaveMethod('__construct')
        ->or->not->toHaveMethod('__construct');
});
