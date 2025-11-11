<?php

namespace CleaniqueCoders\Flowstone\Models;

use CleaniqueCoders\Flowstone\Database\Factories\WorkflowAuditLogFactory;
use CleaniqueCoders\Traitify\Concerns\InteractsWithUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WorkflowAuditLog extends Model
{
    use HasFactory, InteractsWithUuid;

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array<string>
     */
    protected $dates = ['created_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'workflow_id',
        'subject_type',
        'subject_id',
        'from_place',
        'to_place',
        'transition',
        'user_id',
        'context',
        'metadata',
        'created_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'context' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): WorkflowAuditLogFactory
    {
        return WorkflowAuditLogFactory::new();
    }

    /**
     * Get the workflow that this audit log belongs to.
     */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * Get the subject (model) that underwent the transition.
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who performed the transition.
     */
    public function user(): BelongsTo
    {
        $userModel = config('auth.providers.users.model', \App\Models\User::class);

        return $this->belongsTo($userModel, 'user_id');
    }

    /**
     * Scope to filter by workflow.
     */
    public function scopeForWorkflow($query, int $workflowId)
    {
        return $query->where('workflow_id', $workflowId);
    }

    /**
     * Scope to filter by subject.
     */
    public function scopeForSubject($query, string $subjectType, int $subjectId)
    {
        return $query->where('subject_type', $subjectType)
            ->where('subject_id', $subjectId);
    }

    /**
     * Scope to filter by user.
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by transition.
     */
    public function scopeByTransition($query, string $transition)
    {
        return $query->where('transition', $transition);
    }

    /**
     * Scope to filter by place (from or to).
     */
    public function scopeByPlace($query, string $place)
    {
        return $query->where(function ($q) use ($place) {
            $q->where('from_place', $place)
                ->orWhere('to_place', $place);
        });
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope to order by most recent first.
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope to order by oldest first.
     */
    public function scopeOldest($query)
    {
        return $query->orderBy('created_at', 'asc');
    }

    /**
     * Get a human-readable description of the transition.
     */
    public function getDescriptionAttribute(): string
    {
        $from = $this->from_place ? "'{$this->from_place}'" : 'initial state';
        $to = "'{$this->to_place}'";
        $transition = "'{$this->transition}'";

        return "Transitioned from {$from} to {$to} via {$transition}";
    }

    /**
     * Check if this log represents a successful transition.
     */
    public function isSuccessful(): bool
    {
        return ! empty($this->to_place);
    }
}
