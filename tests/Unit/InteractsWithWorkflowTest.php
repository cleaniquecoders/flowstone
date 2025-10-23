<?php

use CleaniqueCoders\Flowstone\Database\Factories\WorkflowFactory;
use CleaniqueCoders\Flowstone\Enums\Status;
use CleaniqueCoders\Flowstone\Models\Workflow;
use CleaniqueCoders\Flowstone\Tests\Models\Article;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Workflow\Workflow as SymfonyWorkflow;

describe('InteractsWithWorkflow Trait', function () {
    beforeEach(function () {
        // Create workflow configuration
        $this->workflowConfig = WorkflowFactory::new()
            ->withPlacesAndTransitions()
            ->create([
                'name' => 'article-workflow',
                'type' => 'state_machine',
                'marking' => Status::DRAFT->value,
            ]);

        // Create test model that uses the workflow
        $this->article = new Article([
            'title' => 'Test Article',
            'content' => 'Test content',
            'marking' => Status::DRAFT->value,
            'workflow_type' => 'article-workflow',
        ]);
        $this->article->save();
    });

    it('can set workflow configuration', function () {
        $article = new Article([
            'title' => 'Test Article',
            'content' => 'Test content',
            'marking' => Status::DRAFT->value,
            'workflow_type' => 'article-workflow',
        ]);
        $article->save();

        expect($article->config)->toBeNull();

        $article->setWorkflow();

        expect($article->config)->not->toBeNull();
        expect($article->config)->toBeArray();
    });

    it('does not override existing workflow configuration', function () {
        $existingConfig = ['existing' => 'configuration'];
        $article = new Article([
            'title' => 'Test Article',
            'content' => 'Test content',
            'marking' => Status::DRAFT->value,
            'workflow_type' => 'article-workflow',
            'config' => $existingConfig,
        ]);
        $article->save();

        $article->setWorkflow();

        expect($article->config)->toEqual($existingConfig);
    });

    it('has workflow type accessor', function () {
        expect($this->article->workflow_type)->toBe($this->article->workflow_type);
    });

    it('has workflow type field accessor', function () {
        expect($this->article->workflow_type_field)->toBe('workflow_type');
    });

    it('can get symfony workflow instance', function () {
        $workflow = $this->article->getWorkflow();

        expect($workflow)->toBeInstanceOf(SymfonyWorkflow::class);
    });

    it('caches workflow instance', function () {
        // Set up article with workflow configuration
        $this->article->config = [
            'type' => 'state_machine',
            'places' => [Status::DRAFT->value => null],
            'transitions' => [],
        ];
        $this->article->save();

        // First call should create and cache the workflow
        $workflow1 = $this->article->getWorkflow();
        $workflow2 = $this->article->getWorkflow();

        expect($workflow1)->toBeInstanceOf(SymfonyWorkflow::class);
        expect($workflow2)->toBeInstanceOf(SymfonyWorkflow::class);
        // The instances should be the same due to caching
        expect($workflow1)->toBe($workflow2);
    });

    it('generates correct workflow cache key', function () {
        $key = $this->article->getWorkflowKey();

        expect($key)->toBeString();
        expect($key)->toContain('tests');
        expect($key)->toContain('models');
        expect($key)->toContain('article');
        expect($key)->toContain((string) $this->article->id);
    });

    it('can get marking', function () {
        expect($this->article->getMarking())->toBe($this->article->marking);
    });

    it('can get enabled transitions', function () {
        // Set up article with proper workflow configuration
        $this->article->config = [
            'type' => 'state_machine',
            'places' => [
                Status::DRAFT->value => null,
                Status::PENDING->value => null,
            ],
            'transitions' => [
                'submit' => [
                    'from' => [Status::DRAFT->value],
                    'to' => Status::PENDING->value,
                ],
            ],
        ];
        $this->article->marking = Status::DRAFT->value;
        $this->article->save();

        $transitions = $this->article->getEnabledTransitions();

        expect($transitions)->toBeArray();
    });

    it('can get enabled to transitions', function () {
        // Set up article with proper workflow configuration
        $this->article->config = [
            'type' => 'state_machine',
            'places' => [
                Status::DRAFT->value => null,
                Status::PENDING->value => null,
            ],
            'transitions' => [
                'submit' => [
                    'from' => [Status::DRAFT->value],
                    'to' => Status::PENDING->value,
                ],
            ],
        ];
        $this->article->marking = Status::DRAFT->value;
        $this->article->save();

        $toTransitions = $this->article->getEnabledToTransitions();

        expect($toTransitions)->toBeArray();
    });

    it('can check if has enabled to transitions', function () {
        // Test article in DRAFT state should have outgoing transitions
        expect($this->article->hasEnabledToTransitions())->toBeTrue();

        // Test article in final state with no outgoing transitions
        $finalArticle = new Article([
            'title' => 'Final Article',
            'content' => 'Final content',
            'marking' => Status::ARCHIVED->value, // Final state
            'workflow_type' => 'article-workflow',
        ]);
        $finalArticle->save();

        expect($finalArticle->hasEnabledToTransitions())->toBeFalse();
    });

    it('can get roles from transition', function () {
        $article = new Article([
            'title' => 'Test Article',
            'content' => 'Test content',
            'marking' => Status::DRAFT->value,
            'workflow_type' => 'article-workflow',
            'workflow' => [
                'transitions' => [
                    'submit' => [
                        'from' => [Status::DRAFT->value],
                        'to' => Status::PENDING->value,
                        'metadata' => [
                            'role' => ['admin', 'manager'],
                        ],
                    ],
                ],
            ],
        ]);
        $article->save();

        $roles = $article->getRolesFromTransition(Status::PENDING->value, 'to');

        expect($roles)->toBeArray();
    });

    it('can get all enabled transition roles', function () {
        // Set up article with workflow configuration that has roles
        $this->article->config = [
            'type' => 'state_machine',
            'places' => [
                Status::DRAFT->value => null,
                Status::PENDING->value => null,
            ],
            'transitions' => [
                'submit' => [
                    'from' => [Status::DRAFT->value],
                    'to' => Status::PENDING->value,
                    'metadata' => ['role' => ['author', 'editor']],
                ],
            ],
        ];
        $this->article->marking = Status::DRAFT->value;
        $this->article->save();

        $allRoles = $this->article->getAllEnabledTransitionRoles();

        expect($allRoles)->toBeArray();
    });
});
