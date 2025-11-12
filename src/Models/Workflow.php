<?php

namespace CleaniqueCoders\Flowstone\Models;

use CleaniqueCoders\Flowstone\Concerns\InteractsWithWorkflow;
use CleaniqueCoders\Flowstone\Contracts\Workflow as WorkflowContract;
use CleaniqueCoders\Flowstone\Database\Factories\WorkflowFactory;
use CleaniqueCoders\Traitify\Concerns\InteractsWithMeta;
use CleaniqueCoders\Traitify\Concerns\InteractsWithTags;
use CleaniqueCoders\Traitify\Concerns\InteractsWithUuid;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string|null $description
 * @property string|null $group
 * @property string|null $category
 * @property array $tags
 * @property string $type
 * @property string|null $marking_store_type
 * @property string|null $marking_store_property
 * @property string|null $initial_marking
 * @property string|null $marking
 * @property array|null $config
 * @property array|null $designer
 * @property bool $is_enabled
 * @property bool $audit_trail_enabled
 * @property array|null $event_listeners
 * @property array|null $events_to_dispatch
 * @property bool $dispatch_guard_events
 * @property bool $dispatch_leave_events
 * @property bool $dispatch_transition_events
 * @property bool $dispatch_enter_events
 * @property bool $dispatch_entered_events
 * @property bool $dispatch_completed_events
 * @property bool $dispatch_announce_events
 * @property array|null $meta
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \CleaniqueCoders\Flowstone\Models\WorkflowPlace> $places
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \CleaniqueCoders\Flowstone\Models\WorkflowTransition> $transitions
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \CleaniqueCoders\Flowstone\Models\WorkflowAuditLog> $auditLogs
 */
class Workflow extends Model implements WorkflowContract
{
    use HasFactory, InteractsWithMeta, InteractsWithTags, InteractsWithUuid, InteractsWithWorkflow, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'group',
        'category',
        'tags',
        'type',
        'marking_store_type',
        'marking_store_property',
        'initial_marking',
        'marking',
        'config',
        'designer',
        'is_enabled',
        'audit_trail_enabled',
        'event_listeners',
        'events_to_dispatch',
        'dispatch_guard_events',
        'dispatch_leave_events',
        'dispatch_transition_events',
        'dispatch_enter_events',
        'dispatch_entered_events',
        'dispatch_completed_events',
        'dispatch_announce_events',
        'meta',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'audit_trail_enabled' => 'boolean',
        'dispatch_guard_events' => 'boolean',
        'dispatch_leave_events' => 'boolean',
        'dispatch_transition_events' => 'boolean',
        'dispatch_enter_events' => 'boolean',
        'dispatch_entered_events' => 'boolean',
        'dispatch_completed_events' => 'boolean',
        'dispatch_announce_events' => 'boolean',
        'meta' => 'array',
        'config' => 'array',
        'designer' => 'array',
        'tags' => 'array',
        'event_listeners' => 'array',
        'events_to_dispatch' => 'array',
    ];

    public function places(): HasMany
    {
        return $this->hasMany(WorkflowPlace::class)->orderBy('sort_order');
    }

    public function transitions(): HasMany
    {
        return $this->hasMany(WorkflowTransition::class)->orderBy('sort_order');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(WorkflowAuditLog::class)->latest();
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

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")
                ->orWhere('group', 'like', "%{$term}%")
                ->orWhere('category', 'like', "%{$term}%")
                ->orWhere('tags', 'like', "%{$term}%");
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
     * Add a tag to the workflow
     */
    public function addTag(string $tag): self
    {
        $tags = $this->tags ?? [];

        if (! in_array($tag, $tags)) {
            $tags[] = $tag;
            $this->tags = $tags;
            $this->save();
        }

        return $this;
    }

    /**
     * Sync tags with the workflow
     */
    public function syncTags(array $tags): self
    {
        $this->tags = array_unique($tags);
        $this->save();

        return $this;
    }

    /**
     * Remove a tag from the workflow
     */
    public function removeTag(string $tag): self
    {
        $tags = $this->tags ?? [];

        $this->tags = array_values(array_filter($tags, fn ($t) => $t !== $tag));
        $this->save();

        return $this;
    }

    /**
     * Scope to filter workflows by a specific tag
     */
    public function scopeByTag($query, string $tag)
    {
        return $query->whereJsonContains('tags', $tag);
    }

    /**
     * Scope to filter workflows by multiple tags (must have all)
     */
    public function scopeByTags($query, array $tags)
    {
        foreach ($tags as $tag) {
            $query->whereJsonContains('tags', $tag);
        }

        return $query;
    }

    /**
     * Scope to filter workflows by any of the given tags
     */
    public function scopeByAnyTag($query, array $tags)
    {
        return $query->where(function ($q) use ($tags) {
            foreach ($tags as $tag) {
                $q->orWhereJsonContains('tags', $tag);
            }
        });
    }

    /**
     * Get all unique tags across all workflows
     */
    public static function getAllTags(): array
    {
        return static::query()
            ->whereNotNull('tags')
            ->get()
            ->pluck('tags')
            ->flatten()
            ->unique()
            ->sort()
            ->values()
            ->toArray();
    }

    /**
     * Check if workflow has a specific tag
     */
    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->tags ?? []);
    }

    /**
     * Check if workflow has any of the given tags
     */
    public function hasAnyTag(array $tags): bool
    {
        $workflowTags = $this->tags ?? [];

        return ! empty(array_intersect($tags, $workflowTags));
    }

    /**
     * Check if workflow has all of the given tags
     */
    public function hasAllTags(array $tags): bool
    {
        $workflowTags = $this->tags ?? [];

        return empty(array_diff($tags, $workflowTags));
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
     * Check if a specific event listener is registered for this workflow
     *
     * @param  string  $listener  Fully qualified class name of the event listener
     */
    public function hasEventListener(string $listener): bool
    {
        $listeners = $this->event_listeners ?? [];

        return in_array($listener, $listeners, true);
    }

    /**
     * Add an event listener to this workflow
     *
     * @param  string  $listener  Fully qualified class name of the event listener
     */
    public function addEventListener(string $listener): self
    {
        $listeners = $this->event_listeners ?? [];

        if (! in_array($listener, $listeners, true)) {
            $listeners[] = $listener;
            $this->event_listeners = $listeners;
        }

        return $this;
    }

    /**
     * Remove an event listener from this workflow
     *
     * @param  string  $listener  Fully qualified class name of the event listener
     */
    public function removeEventListener(string $listener): self
    {
        $listeners = $this->event_listeners ?? [];
        $listeners = array_values(array_filter($listeners, fn ($l) => $l !== $listener));
        $this->event_listeners = $listeners;

        return $this;
    }

    /**
     * Check if a specific event type should be dispatched for this workflow
     *
     * This method checks both the boolean flags and the events_to_dispatch array.
     *
     * @param  string  $eventType  The event type (e.g., 'guard', 'leave', 'transition', 'enter', 'entered', 'completed', 'announce')
     */
    public function shouldDispatchEvent(string $eventType): bool
    {
        // Map event types to their boolean flag columns
        $eventFlagMap = [
            'guard' => 'dispatch_guard_events',
            'leave' => 'dispatch_leave_events',
            'transition' => 'dispatch_transition_events',
            'enter' => 'dispatch_enter_events',
            'entered' => 'dispatch_entered_events',
            'completed' => 'dispatch_completed_events',
            'announce' => 'dispatch_announce_events',
        ];

        // Check boolean flag first (more specific control)
        if (isset($eventFlagMap[$eventType])) {
            $flagColumn = $eventFlagMap[$eventType];
            if ($this->{$flagColumn} === false) {
                return false;
            }
        }

        // If events_to_dispatch is empty or null, all events are enabled
        $eventsToDispatch = $this->events_to_dispatch ?? [];
        if (empty($eventsToDispatch)) {
            return true;
        }

        // Check if this specific event or its variants are in the list
        // e.g., 'workflow.guard' or 'workflow.my_workflow.guard'
        $eventPatterns = [
            "workflow.{$eventType}",
            "workflow.*.{$eventType}",
        ];

        foreach ($eventPatterns as $pattern) {
            if (in_array($pattern, $eventsToDispatch, true)) {
                return true;
            }
        }

        // Check if any wildcard patterns match
        foreach ($eventsToDispatch as $event) {
            if (str_contains($event, $eventType)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the complete event configuration for this workflow
     *
     * Returns an array with event listeners and event dispatch settings.
     *
     * @return array{
     *     event_listeners: array<string>,
     *     events_to_dispatch: array<string>,
     *     dispatch_flags: array<string, bool>
     * }
     */
    public function getEventConfiguration(): array
    {
        return [
            'event_listeners' => $this->event_listeners ?? [],
            'events_to_dispatch' => $this->events_to_dispatch ?? [],
            'dispatch_flags' => [
                'guard' => $this->dispatch_guard_events ?? true,
                'leave' => $this->dispatch_leave_events ?? true,
                'transition' => $this->dispatch_transition_events ?? true,
                'enter' => $this->dispatch_enter_events ?? true,
                'entered' => $this->dispatch_entered_events ?? true,
                'completed' => $this->dispatch_completed_events ?? true,
                'announce' => $this->dispatch_announce_events ?? true,
            ],
        ];
    }

    /**
     * Get simplified guards configuration for a specific transition
     * Returns a simplified array format for displaying guard information
     */
    public function getTransitionGuardConfig(string $transitionName): array
    {
        $transition = $this->transitions()->where('name', $transitionName)->first();

        if (! $transition) {
            return [];
        }

        $meta = $transition->meta ?? [];
        $guards = [];

        // Check for role guards
        if (isset($meta['roles']) && is_array($meta['roles'])) {
            $guards['roles'] = $meta['roles'];
        }

        // Check for permission guards
        if (isset($meta['permission'])) {
            $guards['permission'] = $meta['permission'];
        }

        // Check for custom guards
        if (isset($meta['guards']) && is_array($meta['guards'])) {
            $guards['custom'] = $meta['guards'];
        }

        return $guards;
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

        $config = [
            'type' => $this->type,
            'places' => $places,
            'transitions' => $transitions,
            'initial_marking' => $this->initial_marking,
            'metadata' => $this->meta ?? [],
        ];

        // Add marking store configuration if set to non-default values
        $defaultType = 'method';
        $defaultProperty = 'marking';
        $hasCustomMarkingStore = ($this->marking_store_type && $this->marking_store_type !== $defaultType)
            || ($this->marking_store_property && $this->marking_store_property !== $defaultProperty);

        if ($hasCustomMarkingStore) {
            $config['marking_store'] = [
                'type' => $this->marking_store_type ?? $defaultType,
                'property' => $this->marking_store_property ?? $defaultProperty,
            ];
        }

        // Add event configuration
        $eventConfig = $this->getEventConfiguration();
        if (! empty($eventConfig['event_listeners'])) {
            $config['event_listeners'] = $eventConfig['event_listeners'];
        }
        if (! empty($eventConfig['events_to_dispatch'])) {
            $config['events_to_dispatch'] = $eventConfig['events_to_dispatch'];
        }

        return $config;
    }

    /**
     * Get the marking store type for this workflow
     */
    public function getMarkingStoreType(): string
    {
        return $this->marking_store_type ?? config('flowstone.default.marking_store.type', 'method');
    }

    /**
     * Get the marking store property for this workflow
     */
    public function getMarkingStoreProperty(): string
    {
        return $this->marking_store_property ?? config('flowstone.default.marking_store.property', 'marking');
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
     * Example guard method used for testing.
     * Custom guard methods should return boolean.
     */
    public function canBeApproved(): bool
    {
        // This is a test method - always returns false for guard testing
        return false;
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): WorkflowFactory
    {
        return WorkflowFactory::new();
    }
}
