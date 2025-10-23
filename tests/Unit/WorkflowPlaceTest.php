<?php

use CleaniqueCoders\Flowstone\Models\Workflow;
use CleaniqueCoders\Flowstone\Models\WorkflowPlace;

test('workflow place belongs to workflow', function () {
    $workflow = Workflow::factory()->create();
    $place = WorkflowPlace::factory()->create(['workflow_id' => $workflow->id]);

    expect($place->workflow)->toBeInstanceOf(Workflow::class);
    expect($place->workflow->id)->toBe($workflow->id);
});

test('workflow place generates uuid on creation', function () {
    $place = WorkflowPlace::factory()->create();

    expect((string) $place->uuid)->toBeString();
    expect((string) $place->uuid)->toHaveLength(36);
});

test('workflow place has correct fillable fields', function () {
    $place = WorkflowPlace::factory()->create([
        'name' => 'draft',
        'sort_order' => 1,
        'meta' => ['title' => 'Draft State'],
    ]);

    expect($place->name)->toBe('draft');
    expect($place->sort_order)->toBe(1);
    expect($place->meta)->toBe(['title' => 'Draft State']);
});

test('workflow place meta is cast to array', function () {
    $meta = ['title' => 'Test', 'description' => 'Test description'];
    $place = WorkflowPlace::factory()->create(['meta' => $meta]);

    expect($place->meta)->toBeArray();
    expect($place->meta)->toBe($meta);
});
