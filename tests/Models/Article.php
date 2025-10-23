<?php

namespace CleaniqueCoders\Flowstone\Tests\Models;

use CleaniqueCoders\Flowstone\Concerns\InteractsWithWorkflow;
use CleaniqueCoders\Flowstone\Contracts\Workflow as WorkflowContract;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Article extends Model implements WorkflowContract
{
    use InteractsWithWorkflow;

    protected $fillable = [
        'title',
        'content',
        'marking',
        'workflow_type',
        'config',
    ];

    protected $casts = [
        'marking' => 'string',
        'config' => 'array',
    ];

    public function workflowType(): Attribute
    {
        return Attribute::make(get: fn () => $this->workflow_type ?? 'article-workflow');
    }

    public function workflowTypeField(): Attribute
    {
        return Attribute::make(get: fn () => 'workflow_type');
    }
}
