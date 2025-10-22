<?php

namespace CleaniqueCoders\LaravelWorklfow\Enums;

use CleaniqueCoders\Traitify\Concerns\InteractsWithEnum;
use CleaniqueCoders\Traitify\Contracts\Enum as Contract;

enum Status: string implements Contract
{
    use InteractsWithEnum;

    case DRAFT = 'draft';
    case PENDING = 'pending';
    case IN_PROGRESS = 'in-progress';
    case UNDER_REVIEW = 'under-review';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case ON_HOLD = 'on-hold';
    case CANCELLED = 'cancelled';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case PAUSED = 'paused';
    case ARCHIVED = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => __('Draft'),
            self::PENDING => __('Pending'),
            self::IN_PROGRESS => __('In Progress'),
            self::UNDER_REVIEW => __('Under Review'),
            self::APPROVED => __('Approved'),
            self::REJECTED => __('Rejected'),
            self::ON_HOLD => __('On Hold'),
            self::CANCELLED => __('Cancelled'),
            self::COMPLETED => __('Completed'),
            self::FAILED => __('Failed'),
            self::PAUSED => __('Paused'),
            self::ARCHIVED => __('Archived'),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::DRAFT => __('Initial state where the workflow is being prepared or configured.'),
            self::PENDING => __('Workflow is waiting to be started or assigned.'),
            self::IN_PROGRESS => __('Workflow is currently being executed or processed.'),
            self::UNDER_REVIEW => __('Workflow is being reviewed or evaluated by stakeholders.'),
            self::APPROVED => __('Workflow has been approved and can proceed to the next stage.'),
            self::REJECTED => __('Workflow has been rejected and requires changes or corrections.'),
            self::ON_HOLD => __('Workflow execution is temporarily suspended.'),
            self::CANCELLED => __('Workflow has been cancelled and will not be completed.'),
            self::COMPLETED => __('Workflow has been successfully finished.'),
            self::FAILED => __('Workflow execution failed due to errors or issues.'),
            self::PAUSED => __('Workflow is temporarily paused and can be resumed later.'),
            self::ARCHIVED => __('Workflow is completed and moved to archive for historical reference.'),
        };
    }
}
