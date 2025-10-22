<?php

use CleaniqueCoders\LaravelWorklfow\Database\Factories\WorkflowFactory;
use CleaniqueCoders\LaravelWorklfow\Enums\Status;

describe('Laravel Workflow Package', function () {
    it('can create workflows using factory', function () {
        $workflow = WorkflowFactory::new()->create();

        expect($workflow)->not->toBeNull();
        expect($workflow->exists)->toBeTrue();
    });

    it('has all expected status enums', function () {
        $statuses = Status::cases();

        expect($statuses)->toHaveCount(12);
        expect($statuses)->toContain(Status::DRAFT);
        expect($statuses)->toContain(Status::COMPLETED);
    });

    it('can work with workflow configuration', function () {
        $config = config('worklfow.default');

        expect($config)->toBeArray();
        expect($config['type'])->toBe('state_machine');
    });

    it('provides helper functions', function () {
        expect(function_exists('create_workflow'))->toBeTrue();
        expect(function_exists('get_workflow_config'))->toBeTrue();
        expect(function_exists('get_roles_from_transition'))->toBeTrue();
    });
});
