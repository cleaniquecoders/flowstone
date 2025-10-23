<?php

namespace CleaniqueCoders\Flowstone\Actions;

use CleaniqueCoders\LaravelAction\ResourceAction;
use CleaniqueCoders\Flowstone\Models\Workflow;

class WorkflowHandler extends ResourceAction
{
    protected string $model = Workflow::class;

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:4', 'max:255'],
        ];
    }
}
