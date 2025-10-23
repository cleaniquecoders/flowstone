<?php

namespace CleaniqueCoders\Flowstone\Models;

use CleaniqueCoders\Flowstone\Database\Factories\WorkflowPlaceFactory;
use CleaniqueCoders\Traitify\Concerns\InteractsWithUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowPlace extends Model
{
    use HasFactory, InteractsWithUuid;

    protected $fillable = [
        'workflow_id',
        'name',
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
    protected static function newFactory(): WorkflowPlaceFactory
    {
        return WorkflowPlaceFactory::new();
    }
}
