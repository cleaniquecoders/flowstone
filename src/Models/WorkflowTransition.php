<?php

namespace CleaniqueCoders\Flowstone\Models;

use CleaniqueCoders\Flowstone\Database\Factories\WorkflowTransitionFactory;
use CleaniqueCoders\Traitify\Concerns\InteractsWithMeta;
use CleaniqueCoders\Traitify\Concerns\InteractsWithUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowTransition extends Model
{
    use HasFactory, InteractsWithMeta, InteractsWithUuid;

    protected $fillable = [
        'workflow_id',
        'name',
        'from_place',
        'to_place',
        'sort_order',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): WorkflowTransitionFactory
    {
        return WorkflowTransitionFactory::new();
    }
}
