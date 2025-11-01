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
        'group',
        'category',
        'tags',
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
        'tags' => 'array',
    ];

    public function places(): HasMany
    {
        return $this->hasMany(WorkflowPlace::class)->orderBy('sort_order');
    }

    public function transitions(): HasMany
    {
        return $this->hasMany(WorkflowTransition::class)->orderBy('sort_order');
    }

    // Tag management methods
    public function addTag(string $tag): void
    {
        $tags = $this->tags ?? [];

        if (! in_array($tag, $tags)) {
            $tags[] = $tag;
            $this->update(['tags' => $tags]);
        }
    }

    public function removeTag(string $tag): void
    {
        $tags = $this->tags ?? [];

        $tags = array_values(array_filter($tags, fn ($t) => $t !== $tag));
        $this->update(['tags' => $tags]);
    }

    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->tags ?? []);
    }

    public function syncTags(array $tags): void
    {
        $this->update(['tags' => array_values(array_unique($tags))]);
    }

    // Scopes for filtering
    public function scopeIsEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeByGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByTag($query, string $tag)
    {
        return $query->whereJsonContains('tags', $tag);
    }

    public function scopeByTags($query, array $tags)
    {
        foreach ($tags as $tag) {
            $query->whereJsonContains('tags', $tag);
        }

        return $query;
    }

    public function scopeByAnyTag($query, array $tags)
    {
        return $query->where(function ($q) use ($tags) {
            foreach ($tags as $tag) {
                $q->orWhereJsonContains('tags', $tag);
            }
        });
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")
                ->orWhere('group', 'like', "%{$term}%")
                ->orWhere('category', 'like', "%{$term}%")
                ->orWhereJsonContains('tags', $term);
        });
    }

    /**
     * Get all unique groups
     */
    public static function getAllGroups(): array
    {
        return static::whereNotNull('group')
            ->distinct()
            ->pluck('group')
            ->sort()
            ->values()
            ->toArray();
    }

    /**
     * Get all unique categories
     */
    public static function getAllCategories(): array
    {
        return static::whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->sort()
            ->values()
            ->toArray();
    }

    /**
     * Get all unique tags
     */
    public static function getAllTags(): array
    {
        return static::pluck('tags')
            ->flatten()
            ->unique()
            ->filter()
            ->sort()
            ->values()
            ->toArray();
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
