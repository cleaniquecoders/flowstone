<?php

namespace CleaniqueCoders\Flowstone\Models;

use CleaniqueCoders\Flowstone\Concerns\InteractsWithWorkflow;
use CleaniqueCoders\Flowstone\Contracts\Workflow as WorkflowContract;
use CleaniqueCoders\Flowstone\Database\Factories\WorkflowFactory;
use CleaniqueCoders\Traitify\Concerns\InteractsWithMeta;
use CleaniqueCoders\Traitify\Concerns\InteractsWithUuid;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Workflow extends Model implements WorkflowContract
{
    use HasFactory, InteractsWithMeta, InteractsWithUuid, InteractsWithWorkflow, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'type',
        'initial_marking',
        'marking',
        'config',
        'designer',
        'is_enabled',
        'meta',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'meta' => 'array',
        'config' => 'array',
        'designer' => 'array',
    ];

    public function places(): HasMany
    {
        return $this->hasMany(WorkflowPlace::class)->orderBy('sort_order');
    }

    public function transitions(): HasMany
    {
        return $this->hasMany(WorkflowTransition::class)->orderBy('sort_order');
    }

    public function scopeIsEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Get the workflow configuration as an accessor
     */
    public function getConfigAttribute($value): array
    {
        // If there's a stored config, use it
        if ($value) {
            return is_array($value) ? $value : json_decode($value, true) ?? [];
        }

        // Otherwise, generate from database relationships
        return $this->getSymfonyConfig();
    }

    /**
     * Generate Symfony Workflow configuration from database structure
     */
    public function getSymfonyConfig(): array
    {
        $places = [];
        foreach ($this->places as $place) {
            $places[$place->name] = [
                'metadata' => $place->meta ?? [],
            ];
        }

        $transitions = [];
        foreach ($this->transitions as $transition) {
            $transitions[$transition->name] = [
                'from' => [$transition->from_place],
                'to' => $transition->to_place,
                'metadata' => $transition->meta ?? [],
            ];
        }

        return [
            'type' => $this->type,
            'places' => $places,
            'transitions' => $transitions,
            'initial_marking' => $this->initial_marking,
            'metadata' => $this->meta ?? [],
        ];
    }

    /**
     * Implementation of WorkflowContract methods
     */
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

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): WorkflowFactory
    {
        return WorkflowFactory::new();
    }
}
