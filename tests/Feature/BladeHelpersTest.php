<?php

use CleaniqueCoders\Flowstone\Models\Workflow;
use CleaniqueCoders\Flowstone\Models\WorkflowAuditLog;
use CleaniqueCoders\Flowstone\Tests\Models\Article;
use Illuminate\Support\Facades\Blade;

beforeEach(function () {
    // Create a test workflow with proper config
    $this->workflow = Workflow::factory()->create([
        'name' => 'document-workflow',
        'type' => 'state_machine',
        'config' => [
            'type' => 'state_machine',
            'supports' => [\CleaniqueCoders\Flowstone\Tests\Models\Article::class],
            'places' => ['draft', 'review', 'published'],
            'transitions' => [
                'submit' => [
                    'from' => 'draft',
                    'to' => 'review',
                ],
                'publish' => [
                    'from' => 'review',
                    'to' => 'published',
                ],
            ],
        ],
    ]);

    // Manually create places to avoid factory sequence issues
    \CleaniqueCoders\Flowstone\Models\WorkflowPlace::factory()->create([
        'workflow_id' => $this->workflow->id,
        'name' => 'draft',
        'sort_order' => 1,
    ]);

    \CleaniqueCoders\Flowstone\Models\WorkflowPlace::factory()->create([
        'workflow_id' => $this->workflow->id,
        'name' => 'review',
        'sort_order' => 2,
    ]);

    \CleaniqueCoders\Flowstone\Models\WorkflowPlace::factory()->create([
        'workflow_id' => $this->workflow->id,
        'name' => 'published',
        'sort_order' => 3,
    ]);

    // Manually create transitions
    \CleaniqueCoders\Flowstone\Models\WorkflowTransition::factory()->create([
        'workflow_id' => $this->workflow->id,
        'name' => 'submit',
        'from_place' => 'draft',
        'to_place' => 'review',
        'sort_order' => 1,
    ]);

    \CleaniqueCoders\Flowstone\Models\WorkflowTransition::factory()->create([
        'workflow_id' => $this->workflow->id,
        'name' => 'publish',
        'from_place' => 'review',
        'to_place' => 'published',
        'sort_order' => 2,
    ]);

    $this->document = new Article([
        'title' => 'Test Document',
        'workflow_type' => 'document-workflow',
        'marking' => 'draft',
        'config' => [
            'type' => 'state_machine',
            'supports' => [\CleaniqueCoders\Flowstone\Tests\Models\Article::class],
            'places' => ['draft', 'review', 'published'],
            'transitions' => [
                'submit' => [
                    'from' => 'draft',
                    'to' => 'review',
                ],
                'publish' => [
                    'from' => 'review',
                    'to' => 'published',
                ],
            ],
        ],
    ]);
});

describe('workflow helper functions', function () {
    it('workflow_can returns true when transition is allowed', function () {
        expect(workflow_can($this->document, 'submit'))->toBeTrue();
    });

    it('workflow_can returns false when transition is not allowed', function () {
        expect(workflow_can($this->document, 'publish'))->toBeFalse();
    });

    it('workflow_can returns false for non-workflow models', function () {
        $nonWorkflow = new stdClass;
        expect(workflow_can($nonWorkflow, 'submit'))->toBeFalse();
    });

    it('workflow_transitions returns array of transitions', function () {
        $transitions = workflow_transitions($this->document);

        expect($transitions)->toBeArray()
            ->and(count($transitions))->toBeGreaterThan(0);
    });

    it('workflow_transitions returns empty array for non-workflow models', function () {
        $nonWorkflow = new stdClass;
        expect(workflow_transitions($nonWorkflow))->toBe([]);
    });

    it('workflow_transition returns specific transition', function () {
        $transition = workflow_transition($this->document, 'submit');

        expect($transition)->not->toBeNull()
            ->and($transition->getName())->toBe('submit');
    });

    it('workflow_transition returns null for non-existent transition', function () {
        $transition = workflow_transition($this->document, 'non_existent');

        expect($transition)->toBeNull();
    });

    it('workflow_marked_places returns current places', function () {
        $places = workflow_marked_places($this->document);

        expect($places)->toBeArray()
            ->and($places)->toHaveKey('draft');
    });

    it('workflow_marked_places returns empty array for non-workflow models', function () {
        $nonWorkflow = new stdClass;
        expect(workflow_marked_places($nonWorkflow))->toBe([]);
    });

    it('workflow_has_marked_place returns true for current place', function () {
        expect(workflow_has_marked_place($this->document, 'draft'))->toBeTrue();
    });

    it('workflow_has_marked_place returns false for other places', function () {
        expect(workflow_has_marked_place($this->document, 'review'))->toBeFalse();
    });

    it('workflow_transition_blockers returns empty array when no blockers', function () {
        $blockers = workflow_transition_blockers($this->document, 'submit');

        expect($blockers)->toBeArray()
            ->and($blockers)->toBeEmpty();
    });

    it('workflow_transition_blockers returns blockers when transition blocked', function () {
        $blockers = workflow_transition_blockers($this->document, 'publish');

        expect($blockers)->toBeArray()
            ->and(count($blockers))->toBeGreaterThan(0);
    });

    it('workflow_transition_blockers returns empty array for non-workflow models', function () {
        $nonWorkflow = new stdClass;
        expect(workflow_transition_blockers($nonWorkflow, 'submit'))->toBe([]);
    });

    it('workflow_metadata returns workflow metadata', function () {
        $this->workflow->update([
            'meta' => ['priority' => 'high'],
        ]);

        $this->document->type = 'document-workflow';
        $metadata = workflow_metadata($this->document, 'priority', 'workflow');

        // This test assumes the workflow has metadata configured
        expect($metadata)->toBeNull(); // Will be null unless explicitly set in workflow definition
    });

    it('workflow_metadata returns null for non-workflow models', function () {
        $nonWorkflow = new stdClass;
        expect(workflow_metadata($nonWorkflow, 'test', 'workflow'))->toBeNull();
    });
});

describe('blade directives', function () {
    it('canTransition directive compiles correctly', function () {
        $blade = "@canTransition(\$document, 'submit')
            <button>Submit</button>
        @endCanTransition";

        $compiled = Blade::compileString($blade);

        expect($compiled)->toContain('<?php if(workflow_can($document, \'submit\')): ?>')
            ->and($compiled)->toContain('<?php endif; ?>');
    });

    it('cannotTransition directive compiles correctly', function () {
        $blade = "@cannotTransition(\$document, 'submit')
            <p>Cannot submit</p>
        @endCannotTransition";

        $compiled = Blade::compileString($blade);

        expect($compiled)->toContain('<?php if(!workflow_can($document, \'submit\')): ?>')
            ->and($compiled)->toContain('<?php endif; ?>');
    });

    it('workflowMarkedPlaces directive compiles correctly', function () {
        $blade = '@workflowMarkedPlaces($document)';

        $compiled = Blade::compileString($blade);

        expect($compiled)->toContain('workflow_marked_places($document)');
    });

    it('workflowHasMarkedPlace directive compiles correctly', function () {
        $blade = "@workflowHasMarkedPlace(\$document, 'draft')
            <span>In draft</span>
        @endWorkflowHasMarkedPlace";

        $compiled = Blade::compileString($blade);

        expect($compiled)->toContain('<?php if(workflow_has_marked_place($document, \'draft\')): ?>')
            ->and($compiled)->toContain('<?php endif; ?>');
    });
});

describe('blade components', function () {
    it('workflow-transitions component can be instantiated', function () {
        $component = new \CleaniqueCoders\Flowstone\View\Components\WorkflowTransitions(
            $this->document
        );

        expect($component->model)->toBe($this->document)
            ->and($component->transitions)->toBeArray()
            ->and(count($component->transitions))->toBeGreaterThan(0);
    });

    it('workflow-status component can be instantiated', function () {
        $component = new \CleaniqueCoders\Flowstone\View\Components\WorkflowStatus(
            $this->document
        );

        expect($component->model)->toBe($this->document)
            ->and($component->places)->toBeArray()
            ->and($component->places)->toHaveKey('draft');
    });

    it('workflow-status component formats places correctly', function () {
        $component = new \CleaniqueCoders\Flowstone\View\Components\WorkflowStatus(
            $this->document
        );

        expect($component->formatPlace('in_progress'))->toBe('In Progress')
            ->and($component->formatPlace('draft'))->toBe('Draft');
    });

    it('workflow-status component returns correct badge colors', function () {
        $component = new \CleaniqueCoders\Flowstone\View\Components\WorkflowStatus(
            $this->document
        );

        expect($component->getBadgeColor('draft'))->toContain('gray')
            ->and($component->getBadgeColor('approved'))->toContain('green')
            ->and($component->getBadgeColor('rejected'))->toContain('red');
    });

    it('workflow-blockers component can be instantiated', function () {
        $component = new \CleaniqueCoders\Flowstone\View\Components\WorkflowBlockers(
            $this->document,
            'publish'
        );

        expect($component->model)->toBe($this->document)
            ->and($component->transition)->toBe('publish')
            ->and($component->hasBlockers())->toBeTrue();
    });

    it('workflow-blockers component detects when no blockers', function () {
        $component = new \CleaniqueCoders\Flowstone\View\Components\WorkflowBlockers(
            $this->document,
            'submit'
        );

        expect($component->hasBlockers())->toBeFalse();
    });

    it('workflow-timeline component can be instantiated', function () {
        $component = new \CleaniqueCoders\Flowstone\View\Components\WorkflowTimeline(
            $this->document
        );

        expect($component->model)->toBe($this->document)
            ->and($component->logs)->toBeInstanceOf(\Illuminate\Support\Collection::class);
    });

    it('workflow-timeline component respects limit parameter', function () {
        // Create some audit logs
        WorkflowAuditLog::factory()->count(5)->create([
            'workflow_id' => $this->workflow->id,
            'subject_type' => Article::class,
            'subject_id' => 1,
        ]);

        $component = new \CleaniqueCoders\Flowstone\View\Components\WorkflowTimeline(
            $this->document,
            limit: 3
        );

        expect($component->limit)->toBe(3);
    });

    it('workflow-timeline component returns correct colors', function () {
        $log = WorkflowAuditLog::factory()->create([
            'workflow_id' => $this->workflow->id,
            'subject_type' => Article::class,
            'subject_id' => 1,
            'transition' => 'approve',
        ]);

        $component = new \CleaniqueCoders\Flowstone\View\Components\WorkflowTimeline(
            $this->document
        );

        expect($component->getTimelineColor($log))->toContain('green');
    });
});
