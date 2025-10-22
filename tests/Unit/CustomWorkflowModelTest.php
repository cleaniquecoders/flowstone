<?php

use CleaniqueCoders\LaravelWorklfow\Concerns\InteractsWithWorkflow;
use CleaniqueCoders\LaravelWorklfow\Contracts\Workflow as WorkflowContract;
use CleaniqueCoders\Traitify\Concerns\InteractsWithUuid;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * Test model for demonstrating workflow functionality
 */
class TestWorkflowModel extends Model implements WorkflowContract
{
    use InteractsWithUuid, InteractsWithWorkflow;

    protected $table = 'workflows';

    protected $fillable = [
        'type',
        'name',
        'description',
        'config',
        'marking',
        'workflow',
        'is_enabled',
        'created_by',
        'updated_by',
        'meta',
    ];

    protected $casts = [
        'config' => 'array',
        'workflow' => 'array',
        'is_enabled' => 'bool',
        'created_by' => 'array',
        'updated_by' => 'array',
        'deleted_by' => 'array',
        'meta' => 'array',
    ];

    public function workflowType(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->type,
        );
    }

    public function workflowTypeField(): Attribute
    {
        return Attribute::make(
            get: fn () => 'type',
        );
    }
}

describe('Custom Workflow Model Implementation', function () {
    it('can create a custom workflow model', function () {
        $model = new TestWorkflowModel([
            'type' => 'custom-workflow',
            'name' => 'Test Custom Workflow',
            'marking' => 'draft',
            'config' => [
                'type' => 'state_machine',
                'places' => ['draft', 'published'],
            ],
            'is_enabled' => true,
        ]);

        expect($model)->toBeInstanceOf(WorkflowContract::class);
        expect($model)->toBeInstanceOf(TestWorkflowModel::class);
    });

    it('implements workflow contract methods correctly', function () {
        $model = new TestWorkflowModel([
            'type' => 'test-type',
            'marking' => 'draft',
        ]);

        expect($model->workflow_type)->toBe('test-type');
        expect($model->workflow_type_field)->toBe('type');
        expect($model->getMarking())->toBe('draft');
    });

    it('can use workflow trait functionality', function () {
        $model = new TestWorkflowModel([
            'type' => 'test-workflow',
            'marking' => 'draft',
            'workflow' => null,
        ]);

        // Should be able to call trait methods
        expect(method_exists($model, 'setWorkflow'))->toBeTrue();
        expect(method_exists($model, 'getWorkflowKey'))->toBeTrue();
        expect(method_exists($model, 'hasEnabledToTransitions'))->toBeTrue();

        $key = $model->getWorkflowKey();
        expect($key)->toBeString();
        expect($key)->toContain('testworkflowmodel');
    });

    it('can be extended with custom workflow logic', function () {
        $customModel = new class extends TestWorkflowModel
        {
            public function canTransitionTo(string $state): bool
            {
                // Custom business logic
                if ($this->marking === 'draft' && $state === 'published') {
                    return ! empty($this->name);
                }

                return true;
            }

            public function getRequiredApprovals(): int
            {
                return match ($this->type) {
                    'article' => 2,
                    'blog-post' => 1,
                    default => 0,
                };
            }
        };

        $customModel->fill([
            'type' => 'article',
            'name' => 'Test Article',
            'marking' => 'draft',
        ]);

        expect($customModel->canTransitionTo('published'))->toBeTrue();
        expect($customModel->getRequiredApprovals())->toBe(2);

        $customModel->name = '';
        expect($customModel->canTransitionTo('published'))->toBeFalse();
    });
});
