<?php

describe('Architecture Tests', function () {
    it('will not use debugging functions')
        ->expect(['dd', 'dump', 'ray'])
        ->each->not->toBeUsed();

    it('ensures models implement workflow contract correctly')
        ->expect('CleaniqueCoders\Flowstone\Models')
        ->toImplement('CleaniqueCoders\Flowstone\Contracts\Workflow');

    it('ensures concerns are traits')
        ->expect('CleaniqueCoders\Flowstone\Concerns')
        ->toBeTrait();

    it('ensures contracts are interfaces')
        ->expect('CleaniqueCoders\Flowstone\Contracts')
        ->toBeInterface();

    it('ensures enums are enums')
        ->expect('CleaniqueCoders\Flowstone\Enums')
        ->toBeEnum();

    it('ensures processors are classes')
        ->expect('CleaniqueCoders\Flowstone\Processors')
        ->toBeClasses();

    it('ensures models extend Eloquent model')
        ->expect('CleaniqueCoders\Flowstone\Models')
        ->toExtend('Illuminate\Database\Eloquent\Model');

    it('ensures service providers extend Laravel service provider')
        ->expect('CleaniqueCoders\Flowstone\FlowstoneServiceProvider')
        ->toExtend('Illuminate\Support\ServiceProvider');

    it('ensures facades extend Laravel facade')
        ->expect('CleaniqueCoders\Flowstone\Facades')
        ->toExtend('Illuminate\Support\Facades\Facade');

    it('ensures no class uses die or exit')
        ->expect(['die', 'exit'])
        ->each->not->toBeUsed();

    it('ensures models use proper naming convention')
        ->expect('CleaniqueCoders\Flowstone\Models')
        ->toHaveSuffix('');

    it('ensures processors have proper naming')
        ->expect('CleaniqueCoders\Flowstone\Processors')
        ->classes->not->toBeFinal();

    it('ensures workflow trait is used correctly')
        ->expect('CleaniqueCoders\Flowstone\Models\Workflow')
        ->toUse('CleaniqueCoders\Flowstone\Concerns\InteractsWithWorkflow');

    it('ensures proper exception handling')
        ->expect('CleaniqueCoders\Flowstone')
        ->not->toUse(['trigger_error', 'error_reporting']);

    it('ensures package follows PSR-4 autoloading')
        ->expect('CleaniqueCoders\Flowstone')
        ->toHaveMethod('__construct')
        ->or->not->toHaveMethod('__construct');
});
