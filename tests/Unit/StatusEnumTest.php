<?php

use CleaniqueCoders\Flowstone\Enums\Status;

describe('Status Enum', function () {
    it('has all expected status cases', function () {
        $expectedStatuses = [
            'DRAFT',
            'PENDING',
            'IN_PROGRESS',
            'UNDER_REVIEW',
            'APPROVED',
            'REJECTED',
            'ON_HOLD',
            'CANCELLED',
            'COMPLETED',
            'FAILED',
            'PAUSED',
            'ARCHIVED',
        ];

        $actualStatuses = array_map(fn ($case) => $case->name, Status::cases());

        expect($actualStatuses)->toEqual($expectedStatuses);
    });

    it('has correct string values for each status', function () {
        expect(Status::DRAFT->value)->toBe('draft');
        expect(Status::PENDING->value)->toBe('pending');
        expect(Status::IN_PROGRESS->value)->toBe('in-progress');
        expect(Status::UNDER_REVIEW->value)->toBe('under-review');
        expect(Status::APPROVED->value)->toBe('approved');
        expect(Status::REJECTED->value)->toBe('rejected');
        expect(Status::ON_HOLD->value)->toBe('on-hold');
        expect(Status::CANCELLED->value)->toBe('cancelled');
        expect(Status::COMPLETED->value)->toBe('completed');
        expect(Status::FAILED->value)->toBe('failed');
        expect(Status::PAUSED->value)->toBe('paused');
        expect(Status::ARCHIVED->value)->toBe('archived');
    });

    it('provides correct labels for each status', function () {
        expect(Status::DRAFT->label())->toBe(__('Draft'));
        expect(Status::PENDING->label())->toBe(__('Pending'));
        expect(Status::IN_PROGRESS->label())->toBe(__('In Progress'));
        expect(Status::UNDER_REVIEW->label())->toBe(__('Under Review'));
        expect(Status::APPROVED->label())->toBe(__('Approved'));
        expect(Status::REJECTED->label())->toBe(__('Rejected'));
        expect(Status::ON_HOLD->label())->toBe(__('On Hold'));
        expect(Status::CANCELLED->label())->toBe(__('Cancelled'));
        expect(Status::COMPLETED->label())->toBe(__('Completed'));
        expect(Status::FAILED->label())->toBe(__('Failed'));
        expect(Status::PAUSED->label())->toBe(__('Paused'));
        expect(Status::ARCHIVED->label())->toBe(__('Archived'));
    });

    it('provides descriptions for each status', function () {
        expect(Status::DRAFT->description())
            ->toBe(__('Initial state where the workflow is being prepared or configured.'));
        expect(Status::PENDING->description())
            ->toBe(__('Workflow is waiting to be started or assigned.'));
        expect(Status::IN_PROGRESS->description())
            ->toBe(__('Workflow is currently being executed or processed.'));
        expect(Status::UNDER_REVIEW->description())
            ->toBe(__('Workflow is being reviewed or evaluated by stakeholders.'));
        expect(Status::APPROVED->description())
            ->toBe(__('Workflow has been approved and can proceed to the next stage.'));
        expect(Status::REJECTED->description())
            ->toBe(__('Workflow has been rejected and requires changes or corrections.'));
        expect(Status::ON_HOLD->description())
            ->toBe(__('Workflow execution is temporarily suspended.'));
        expect(Status::CANCELLED->description())
            ->toBe(__('Workflow has been cancelled and will not be completed.'));
        expect(Status::COMPLETED->description())
            ->toBe(__('Workflow has been successfully finished.'));
        expect(Status::FAILED->description())
            ->toBe(__('Workflow execution failed due to errors or issues.'));
        expect(Status::PAUSED->description())
            ->toBe(__('Workflow is temporarily paused and can be resumed later.'));
        expect(Status::ARCHIVED->description())
            ->toBe(__('Workflow is completed and moved to archive for historical reference.'));
    });

    it('can be used in match expressions', function () {
        $status = Status::DRAFT;

        $result = match ($status) {
            Status::DRAFT => 'draft_action',
            Status::PENDING => 'pending_action',
            default => 'unknown_action'
        };

        expect($result)->toBe('draft_action');
    });

    it('can be compared for equality', function () {
        expect(Status::DRAFT)->toBe(Status::DRAFT);
        expect(Status::DRAFT)->not->toBe(Status::PENDING);
    });

    it('can be created from string values', function () {
        expect(Status::from('draft'))->toBe(Status::DRAFT);
        expect(Status::from('pending'))->toBe(Status::PENDING);
        expect(Status::from('completed'))->toBe(Status::COMPLETED);
    });

    it('can try to create from string values', function () {
        expect(Status::tryFrom('draft'))->toBe(Status::DRAFT);
        expect(Status::tryFrom('invalid'))->toBeNull();
    });
});
