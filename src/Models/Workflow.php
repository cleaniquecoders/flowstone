<?php

namespace CleaniqueCoders\LaravelWorklfow\Models;

use CleaniqueCoders\LaravelWorklfow\Concerns\InteractsWithWorkflow;
use CleaniqueCoders\Traitify\Concerns\InteractsWithUuid;
use Illuminate\Database\Eloquent\Model;

class Workflow extends Model
{
    use InteractsWithUuid, InteractsWithWorkflow;

    protected $casts = [
        'config' => 'array',
        'is_enabled' => 'bool',
        'created_by' => 'array',
        'updated_by' => 'array',
        'deleted_by' => 'array',
        'meta' => 'array',
    ];
}
