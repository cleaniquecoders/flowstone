<?php

use CleaniqueCoders\Flowstone\Models\Workflow;
use CleaniqueCoders\Flowstone\Services\WorkflowOrganizationService;

test('can create workflow with group, category, and tags', function () {
    $workflow = Workflow::factory()->create([
        'name' => 'Invoice Approval',
        'group' => 'finance',
        'category' => 'accounting',
        'tags' => ['Critical', 'Approval Required', 'SLA'],
    ]);

    expect($workflow->group)->toBe('finance')
        ->and($workflow->category)->toBe('accounting')
        ->and($workflow->tags)->toHaveCount(3)
        ->and($workflow->hasTag('Critical'))->toBeTrue()
        ->and($workflow->hasTag('NonExistent'))->toBeFalse();
});

test('can add and remove tags', function () {
    $workflow = Workflow::factory()->create([
        'tags' => ['Critical', 'Automated'],
    ]);

    $workflow->addTag('SLA');
    expect($workflow->fresh()->tags)->toContain('SLA');

    $workflow->removeTag('Automated');
    expect($workflow->fresh()->tags)->not->toContain('Automated')
        ->and($workflow->fresh()->tags)->toContain('Critical');
});

test('can sync tags', function () {
    $workflow = Workflow::factory()->create([
        'tags' => ['Critical', 'Automated', 'Old Tag'],
    ]);

    $workflow->syncTags(['New Tag', 'Another Tag']);

    expect($workflow->fresh()->tags)->toHaveCount(2)
        ->and($workflow->fresh()->tags)->toContain('New Tag')
        ->and($workflow->fresh()->tags)->not->toContain('Critical');
});

test('can filter workflows by group', function () {
    Workflow::factory()->create(['group' => 'finance']);
    Workflow::factory()->create(['group' => 'finance']);
    Workflow::factory()->create(['group' => 'hr']);

    $financeWorkflows = Workflow::byGroup('finance')->get();

    expect($financeWorkflows)->toHaveCount(2);
});

test('can filter workflows by category', function () {
    Workflow::factory()->create(['category' => 'accounting']);
    Workflow::factory()->create(['category' => 'accounting']);
    Workflow::factory()->create(['category' => 'payroll']);

    $accountingWorkflows = Workflow::byCategory('accounting')->get();

    expect($accountingWorkflows)->toHaveCount(2);
});

test('can filter workflows by single tag', function () {
    Workflow::factory()->create(['tags' => ['Critical', 'SLA']]);
    Workflow::factory()->create(['tags' => ['Critical', 'Automated']]);
    Workflow::factory()->create(['tags' => ['Automated']]);

    $criticalWorkflows = Workflow::byTag('Critical')->get();

    expect($criticalWorkflows)->toHaveCount(2);
});

test('can filter workflows by all tags', function () {
    Workflow::factory()->create(['tags' => ['Critical', 'SLA', 'Automated']]);
    Workflow::factory()->create(['tags' => ['Critical', 'SLA']]);
    Workflow::factory()->create(['tags' => ['Critical']]);

    $workflows = Workflow::byTags(['Critical', 'SLA'])->get();

    expect($workflows)->toHaveCount(2);
});

test('can filter workflows by any tag', function () {
    Workflow::factory()->create(['tags' => ['Critical']]);
    Workflow::factory()->create(['tags' => ['SLA']]);
    Workflow::factory()->create(['tags' => ['Automated']]);
    Workflow::factory()->create(['tags' => ['Other']]);

    $workflows = Workflow::byAnyTag(['Critical', 'SLA'])->get();

    expect($workflows)->toHaveCount(2);
});

test('can search workflows', function () {
    Workflow::factory()->create(['name' => 'Invoice Approval', 'group' => 'finance']);
    Workflow::factory()->create(['description' => 'Process invoice payments', 'category' => 'accounting']);
    Workflow::factory()->create(['tags' => ['invoice-related']]);
    Workflow::factory()->create(['name' => 'Payroll Processing']);

    $results = Workflow::search('invoice')->get();

    expect($results)->toHaveCount(3);
});

test('can get all unique groups', function () {
    Workflow::factory()->create(['group' => 'finance']);
    Workflow::factory()->create(['group' => 'hr']);
    Workflow::factory()->create(['group' => 'finance']);

    $groups = Workflow::getAllGroups();

    expect($groups)->toHaveCount(2)
        ->and($groups)->toContain('finance')
        ->and($groups)->toContain('hr');
});

test('can get all unique categories', function () {
    Workflow::factory()->create(['category' => 'accounting']);
    Workflow::factory()->create(['category' => 'payroll']);
    Workflow::factory()->create(['category' => 'accounting']);

    $categories = Workflow::getAllCategories();

    expect($categories)->toHaveCount(2)
        ->and($categories)->toContain('accounting')
        ->and($categories)->toContain('payroll');
});

test('can get all unique tags', function () {
    Workflow::factory()->create(['tags' => ['Critical', 'SLA']]);
    Workflow::factory()->create(['tags' => ['Automated', 'Critical']]);
    Workflow::factory()->create(['tags' => ['SLA', 'Time-sensitive']]);

    $tags = Workflow::getAllTags();

    expect($tags)->toHaveCount(4)
        ->and($tags)->toContain('Critical')
        ->and($tags)->toContain('SLA')
        ->and($tags)->toContain('Automated')
        ->and($tags)->toContain('Time-sensitive');
});

test('organization service can get groups with counts', function () {
    Workflow::factory()->create(['group' => 'finance']);
    Workflow::factory()->create(['group' => 'finance']);
    Workflow::factory()->create(['group' => 'hr']);

    $service = new WorkflowOrganizationService;
    $groups = $service->getGroupsWithCounts();

    expect($groups)->toHaveKey('finance')
        ->and($groups['finance'])->toBe(2)
        ->and($groups['hr'])->toBe(1);
});

test('organization service can get categories with counts', function () {
    Workflow::factory()->create(['category' => 'accounting']);
    Workflow::factory()->create(['category' => 'accounting']);
    Workflow::factory()->create(['category' => 'payroll']);

    $service = new WorkflowOrganizationService;
    $categories = $service->getCategoriesWithCounts();

    expect($categories)->toHaveKey('accounting')
        ->and($categories['accounting'])->toBe(2)
        ->and($categories['payroll'])->toBe(1);
});

test('organization service can get tags with counts', function () {
    Workflow::factory()->create(['tags' => ['Critical', 'SLA']]);
    Workflow::factory()->create(['tags' => ['Critical', 'Automated']]);
    Workflow::factory()->create(['tags' => ['SLA']]);

    $service = new WorkflowOrganizationService;
    $tags = $service->getTagsWithCounts();

    expect($tags['Critical'])->toBe(2)
        ->and($tags['SLA'])->toBe(2)
        ->and($tags['Automated'])->toBe(1);
});

test('organization service can get summary', function () {
    Workflow::factory()->count(5)->create(['is_enabled' => true]);
    Workflow::factory()->count(2)->create(['is_enabled' => false]);

    $service = new WorkflowOrganizationService;
    $summary = $service->getSummary();

    expect($summary)->toHaveKey('total_workflows')
        ->and($summary['total_workflows'])->toBe(7)
        ->and($summary['enabled_workflows'])->toBe(5)
        ->and($summary)->toHaveKey('groups')
        ->and($summary)->toHaveKey('categories')
        ->and($summary)->toHaveKey('tags');
});

test('organization service can rename tag', function () {
    Workflow::factory()->create(['tags' => ['OldTag', 'Other']]);
    Workflow::factory()->create(['tags' => ['OldTag']]);
    Workflow::factory()->create(['tags' => ['Different']]);

    $service = new WorkflowOrganizationService;
    $count = $service->renameTag('OldTag', 'NewTag');

    expect($count)->toBe(2);

    $workflows = Workflow::byTag('NewTag')->get();
    expect($workflows)->toHaveCount(2);

    $oldTagWorkflows = Workflow::byTag('OldTag')->get();
    expect($oldTagWorkflows)->toHaveCount(0);
});

test('organization service can delete tag', function () {
    Workflow::factory()->create(['tags' => ['DeleteMe', 'Keep']]);
    Workflow::factory()->create(['tags' => ['DeleteMe']]);
    Workflow::factory()->create(['tags' => ['Keep']]);

    $service = new WorkflowOrganizationService;
    $count = $service->deleteTag('DeleteMe');

    expect($count)->toBe(2);

    $workflows = Workflow::byTag('DeleteMe')->get();
    expect($workflows)->toHaveCount(0);
});
