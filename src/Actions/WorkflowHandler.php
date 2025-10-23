<?php

namespace CleaniqueCoders\Flowstone\Actions;

use CleaniqueCoders\Flowstone\Models\Workflow;
use CleaniqueCoders\LaravelAction\ResourceAction;

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
