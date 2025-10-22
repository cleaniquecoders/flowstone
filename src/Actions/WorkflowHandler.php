<?php

namespace CleaniqueCoders\LaravelWorklfow\Actions;

use CleaniqueCoders\LaravelAction\ResourceAction as Action;
use CleaniqueCoders\LaravelWorklfow\Models\Workflow;

class WorkflowHandler extends Action
{
    protected string $model = Workflow::class;

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:4', 'max:255'],
        ];
    }
}
