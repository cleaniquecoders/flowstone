<?php

use CleaniqueCoders\Flowstone\Models\Workflow;
use CleaniqueCoders\Flowstone\Models\WorkflowTransition;

test('workflow transition belongs to workflow', function () {
    $workflow = Workflow::factory()->create();
    $transition = WorkflowTransition::factory()->create(['workflow_id' => $workflow->id]);

    expect($transition->workflow)->toBeInstanceOf(Workflow::class);
    expect($transition->workflow->id)->toBe($workflow->id);
});

test('workflow transition generates uuid on creation', function () {
    $transition = WorkflowTransition::factory()->create();

    expect((string) $transition->uuid)->toBeString();
    expect((string) $transition->uuid)->toHaveLength(36);
});

test('workflow transition has correct fillable fields', function () {
    $transition = WorkflowTransition::factory()->create([
        'name' => 'submit',
        'from_place' => 'draft',
        'to_place' => 'pending',
        'sort_order' => 1,
        'meta' => ['title' => 'Submit for Review'],
    ]);

    expect($transition->name)->toBe('submit');
    expect($transition->from_place)->toBe('draft');
    expect($transition->to_place)->toBe('pending');
    expect($transition->sort_order)->toBe(1);
    expect($transition->meta)->toBe(['title' => 'Submit for Review']);
});

test('workflow transition meta is cast to array', function () {
    $meta = ['title' => 'Test', 'roles' => ['admin', 'manager']];
    $transition = WorkflowTransition::factory()->create(['meta' => $meta]);

    expect($transition->meta)->toBeArray();
    expect($transition->meta)->toBe($meta);
});
