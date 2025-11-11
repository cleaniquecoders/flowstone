<?php

use CleaniqueCoders\Flowstone\Models\Workflow;
use CleaniqueCoders\Flowstone\Tests\Models\Article;

beforeEach(function () {
    // Create a state machine workflow
    $this->stateMachineWorkflow = Workflow::factory()->create([
        'name' => 'article-state-machine',
        'type' => 'state_machine',
        'audit_trail_enabled' => true,
    ]);

    // Manually create places
    \CleaniqueCoders\Flowstone\Models\WorkflowPlace::factory()->create([
        'workflow_id' => $this->stateMachineWorkflow->id,
        'name' => 'draft',
        'sort_order' => 1,
    ]);
    \CleaniqueCoders\Flowstone\Models\WorkflowPlace::factory()->create([
        'workflow_id' => $this->stateMachineWorkflow->id,
        'name' => 'review',
        'sort_order' => 2,
    ]);
    \CleaniqueCoders\Flowstone\Models\WorkflowPlace::factory()->create([
        'workflow_id' => $this->stateMachineWorkflow->id,
        'name' => 'published',
        'sort_order' => 3,
    ]);

    // Manually create transitions
    \CleaniqueCoders\Flowstone\Models\WorkflowTransition::factory()->create([
        'workflow_id' => $this->stateMachineWorkflow->id,
        'name' => 'submit',
        'from_place' => 'draft',
        'to_place' => 'review',
        'sort_order' => 1,
    ]);
    \CleaniqueCoders\Flowstone\Models\WorkflowTransition::factory()->create([
        'workflow_id' => $this->stateMachineWorkflow->id,
        'name' => 'publish',
        'from_place' => 'review',
        'to_place' => 'published',
        'sort_order' => 2,
    ]);

    // Create a workflow (supports multiple states)
    $this->multiStateWorkflow = Workflow::factory()->create([
        'name' => 'article-workflow',
        'type' => 'workflow',
    ]);

    // Manually create places for multiStateWorkflow
    \CleaniqueCoders\Flowstone\Models\WorkflowPlace::factory()->create([
        'workflow_id' => $this->multiStateWorkflow->id,
        'name' => 'editing',
        'sort_order' => 1,
    ]);
    \CleaniqueCoders\Flowstone\Models\WorkflowPlace::factory()->create([
        'workflow_id' => $this->multiStateWorkflow->id,
        'name' => 'reviewing',
        'sort_order' => 2,
    ]);
    \CleaniqueCoders\Flowstone\Models\WorkflowPlace::factory()->create([
        'workflow_id' => $this->multiStateWorkflow->id,
        'name' => 'testing',
        'sort_order' => 3,
    ]);
    \CleaniqueCoders\Flowstone\Models\WorkflowPlace::factory()->create([
        'workflow_id' => $this->multiStateWorkflow->id,
        'name' => 'published',
        'sort_order' => 4,
    ]);

    // Manually create transitions for multiStateWorkflow
    \CleaniqueCoders\Flowstone\Models\WorkflowTransition::factory()->create([
        'workflow_id' => $this->multiStateWorkflow->id,
        'name' => 'start_review',
        'from_place' => 'editing',
        'to_place' => 'reviewing',
        'sort_order' => 1,
    ]);
    \CleaniqueCoders\Flowstone\Models\WorkflowTransition::factory()->create([
        'workflow_id' => $this->multiStateWorkflow->id,
        'name' => 'start_testing',
        'from_place' => 'reviewing',
        'to_place' => 'testing',
        'sort_order' => 2,
    ]);
    \CleaniqueCoders\Flowstone\Models\WorkflowTransition::factory()->create([
        'workflow_id' => $this->multiStateWorkflow->id,
        'name' => 'publish',
        'from_place' => 'testing',
        'to_place' => 'published',
        'sort_order' => 3,
    ]);

    $this->article = new Article([
        'title' => 'Test Article',
        'content' => 'Test content',
        'workflow_type' => 'article-state-machine',
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
            'audit_trail_enabled' => true,
        ],
    ]);
    $this->article->save();
});

describe('Multiple State Support', function () {
    it('detects state machine does not support multiple states', function () {
        expect($this->article->supportsMultipleStates())->toBeFalse();
    });

    it('detects workflow supports multiple states', function () {
        $article = new Article([
            'title' => 'Test',
            'type' => 'article-workflow',
            'marking' => 'editing',
            'config' => [
                'type' => 'workflow',
                'places' => [
                    'editing' => null,
                    'reviewing' => null,
                    'published' => null,
                ],
                'transitions' => [],
                'initial_marking' => 'editing',
            ],
        ]);

        expect($article->supportsMultipleStates())->toBeTrue();
    });

    it('gets all marked places', function () {
        $places = $this->article->getMarkedPlaces();

        expect($places)->toBeArray()
            ->and($places)->toContain('draft');
    });

    it('checks if model is in specific place', function () {
        expect($this->article->isInPlace('draft'))->toBeTrue()
            ->and($this->article->isInPlace('review'))->toBeFalse();
    });

    it('checks if model is in all specified places', function () {
        expect($this->article->isInAllPlaces(['draft']))->toBeTrue()
            ->and($this->article->isInAllPlaces(['draft', 'review']))->toBeFalse();
    });

    it('checks if model is in any of specified places', function () {
        expect($this->article->isInAnyPlace(['draft', 'review']))->toBeTrue()
            ->and($this->article->isInAnyPlace(['review', 'published']))->toBeFalse();
    });

    it('validates marking store type for state machine', function () {
        expect($this->article->validateMarkingStoreType())->toBeTrue();
    });

    it('throws exception for invalid marking store type', function () {
        $article = new Article([
            'title' => 'Test',
            'type' => 'article-state-machine',
            'marking' => 'draft',
        ]);

        // Manually set invalid config
        $article->config = [
            'type' => 'state_machine',
            'marking_store' => ['type' => 'multiple_state'],
        ];

        $article->validateMarkingStoreType();
    })->throws(\LogicException::class, 'State machine workflows must use');
});

describe('Context Support', function () {
    it('applies transition with context and returns both marking and context', function () {
        $context = ['reason' => 'Ready for review', 'priority' => 'high'];
        $result = $this->article->applyTransitionWithContext('submit', $context);

        expect($result)->toBeArray()
            ->and($result)->toHaveKeys(['marking', 'context'])
            ->and($result['context'])->toBe($context)
            ->and($result['marking'])->toBeInstanceOf(\Symfony\Component\Workflow\Marking::class);
    });

    it('retrieves last transition context from audit log', function () {
        $context = ['reason' => 'Ready for review', 'notes' => 'All checks passed'];
        $this->article->applyTransition('submit', $context);

        $retrievedContext = $this->article->getLastTransitionContext();

        expect($retrievedContext)->toBe($context);
    });

    it('retrieves specific transition context by name', function () {
        $context1 = ['reason' => 'First submission'];
        $this->article->applyTransition('submit', $context1);

        $retrievedContext = $this->article->getTransitionContext('submit');

        expect($retrievedContext)->toBe($context1);
    });

    it('returns null when no transition context exists', function () {
        $context = $this->article->getLastTransitionContext();

        expect($context)->toBeNull();
    });

    it('checks method guard with context parameter', function () {
        $mockArticle = new class extends Article
        {
            public function canBeApprovedWithContext(array $context = []): bool
            {
                return isset($context['priority']) && $context['priority'] === 'high';
            }
        };

        $mockArticle->title = 'Test';
        $mockArticle->type = 'article-state-machine';
        $mockArticle->marking = 'draft';

        $result = $mockArticle->checkMethodGuardWithContext('canBeApprovedWithContext', ['priority' => 'high']);

        expect($result)->toBeTrue();

        $result = $mockArticle->checkMethodGuardWithContext('canBeApprovedWithContext', ['priority' => 'low']);

        expect($result)->toBeFalse();
    });
});

describe('Metadata Support', function () {
    beforeEach(function () {
        // Create workflow with metadata
        $this->workflowWithMetadata = Workflow::factory()->create([
            'name' => 'article-with-metadata',
            'type' => 'state_machine',
            'meta' => ['department' => 'editorial', 'priority' => 'high'],
        ]);

        // Manually create places with metadata
        \CleaniqueCoders\Flowstone\Models\WorkflowPlace::factory()->create([
            'workflow_id' => $this->workflowWithMetadata->id,
            'name' => 'draft',
            'sort_order' => 1,
            'meta' => ['color' => 'gray', 'icon' => 'draft-icon'],
        ]);
        \CleaniqueCoders\Flowstone\Models\WorkflowPlace::factory()->create([
            'workflow_id' => $this->workflowWithMetadata->id,
            'name' => 'published',
            'sort_order' => 2,
            'meta' => ['color' => 'green', 'icon' => 'check-icon'],
        ]);

        // Manually create transition with metadata
        \CleaniqueCoders\Flowstone\Models\WorkflowTransition::factory()->create([
            'workflow_id' => $this->workflowWithMetadata->id,
            'name' => 'publish',
            'from_place' => 'draft',
            'to_place' => 'published',
            'sort_order' => 1,
            'meta' => ['requires_approval' => true, 'notification' => 'email'],
        ]);

        $this->articleWithMeta = new Article([
            'title' => 'Test Article',
            'workflow_type' => 'article-with-metadata',
            'marking' => 'draft',
            'config' => [
                'type' => 'state_machine',
                'supports' => [\CleaniqueCoders\Flowstone\Tests\Models\Article::class],
                'places' => ['draft', 'published'],
                'transitions' => [
                    'publish' => [
                        'from' => 'draft',
                        'to' => 'published',
                    ],
                ],
            ],
        ]);
    });

    it('gets workflow metadata', function () {
        $metadata = $this->articleWithMeta->getWorkflowMetadata();

        expect($metadata)->toBeArray();
    });

    it('gets specific workflow metadata by key', function () {
        // Note: Metadata would need to be set in the workflow definition
        $priority = $this->articleWithMeta->getWorkflowMetadata('priority');

        expect($priority)->toBeNull(); // Will be null unless set in Symfony config
    });

    it('gets place metadata', function () {
        $metadata = $this->articleWithMeta->getPlaceMetadata('draft');

        expect($metadata)->toBeArray();
    });

    it('gets specific place metadata by key', function () {
        $color = $this->articleWithMeta->getPlaceMetadata('draft', 'color');

        // Will be null unless set in workflow definition
        expect($color)->toBeNull();
    });

    it('gets transition metadata', function () {
        $metadata = $this->articleWithMeta->getTransitionMetadata('publish');

        expect($metadata)->toBeArray();
    });

    it('gets specific transition metadata by key', function () {
        $requiresApproval = $this->articleWithMeta->getTransitionMetadata('publish', 'requires_approval');

        // Will be null unless set in workflow definition
        expect($requiresApproval)->toBeNull();
    });

    it('gets all places with metadata', function () {
        $places = $this->articleWithMeta->getPlacesWithMetadata();

        expect($places)->toBeArray()
            ->and($places)->toHaveKey('draft')
            ->and($places)->toHaveKey('published');
    });

    it('gets all transitions with metadata', function () {
        $transitions = $this->articleWithMeta->getTransitionsWithMetadata();

        expect($transitions)->toBeArray()
            ->and($transitions)->toHaveKey('publish');
    });

    it('gets metadata with generic method', function () {
        // Get workflow metadata
        $workflowMeta = $this->articleWithMeta->getMetadata(null, 'workflow');
        expect($workflowMeta)->toBeArray();

        // Get place metadata
        $placeMeta = $this->articleWithMeta->getMetadata(null, 'place', 'draft');
        expect($placeMeta)->toBeArray();

        // Get transition metadata
        $transitionMeta = $this->articleWithMeta->getMetadata(null, 'transition', 'publish');
        expect($transitionMeta)->toBeArray();
    });
});

describe('Advanced Feature Integration', function () {
    it('uses context in transition with audit trail', function () {
        $context = [
            'approver' => 'John Doe',
            'comments' => 'Looks good',
            'priority' => 'high',
        ];

        $this->article->applyTransition('submit', $context, true);

        $lastLog = $this->article->auditLogs()->latest()->first();

        expect($lastLog)->not->toBeNull()
            ->and($lastLog->context)->toBe($context)
            ->and($lastLog->transition)->toBe('submit');
    });

    it('checks multiple states after multiple transitions', function () {
        // This would require a workflow that supports multiple simultaneous states
        $article = new Article([
            'title' => 'Test',
            'type' => 'article-workflow',
            'marking' => 'editing',
            'config' => [
                'type' => 'workflow',
                'places' => [
                    'editing' => null,
                    'reviewing' => null,
                    'published' => null,
                ],
                'transitions' => [],
                'initial_marking' => 'editing',
            ],
        ]);
        $article->save();

        expect($article->supportsMultipleStates())->toBeTrue()
            ->and($article->getMarkedPlaces())->toBeArray();
    });

    it('retrieves metadata for current place', function () {
        $currentPlace = $this->article->getMarking();
        $metadata = $this->article->getPlaceMetadata($currentPlace);

        expect($metadata)->toBeArray();
    });

    it('validates workflow type matches marking store for multiple workflows', function () {
        // State machine should pass
        expect($this->article->validateMarkingStoreType())->toBeTrue();

        // Workflow should also pass
        $workflowArticle = new Article([
            'title' => 'Test',
            'type' => 'article-workflow',
            'marking' => 'editing',
        ]);

        expect($workflowArticle->validateMarkingStoreType())->toBeTrue();
    });
});
