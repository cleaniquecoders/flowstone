<?php

namespace CleaniqueCoders\Flowstone\Models;

use CleaniqueCoders\Flowstone\Concerns\InteractsWithWorkflow;
use CleaniqueCoders\Flowstone\Contracts\Workflow as WorkflowContract;
use CleaniqueCoders\Flowstone\Database\Factories\WorkflowFactory;
use CleaniqueCoders\Traitify\Concerns\InteractsWithUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Workflow extends Model implements WorkflowContract
{
    use HasFactory, InteractsWithUuid, InteractsWithWorkflow, SoftDeletes;

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
        'deleted_by',
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

    public function scopeIsEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): WorkflowFactory
    {
        return WorkflowFactory::new();
    }
}
