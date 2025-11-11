<?php

use CleaniqueCoders\Flowstone\Models\Workflow;
use CleaniqueCoders\Flowstone\Models\WorkflowAuditLog;
use CleaniqueCoders\Flowstone\Models\WorkflowPlace;
use CleaniqueCoders\Flowstone\Models\WorkflowTransition;

beforeEach(function () {
    // Create a test workflow with audit trail enabled
    $this->workflow = Workflow::factory()->create([
        'name' => 'test-workflow',
        'type' => 'state_machine',
        'initial_marking' => 'draft',
        'audit_trail_enabled' => true,
    ]);

    // Create places
    WorkflowPlace::factory()->create([
        'workflow_id' => $this->workflow->id,
        'name' => 'draft',
    ]);

    WorkflowPlace::factory()->create([
        'workflow_id' => $this->workflow->id,
        'name' => 'published',
    ]);

    // Create transition
    WorkflowTransition::factory()->create([
        'workflow_id' => $this->workflow->id,
        'name' => 'publish',
        'from_place' => 'draft',
        'to_place' => 'published',
    ]);

    // Regenerate config
    $this->workflow->refresh();
    $this->workflow->setWorkflow();
});

test('audit log is created when transition is applied', function () {
    expect(WorkflowAuditLog::count())->toBe(0);

    $this->workflow->applyTransition('publish');

    expect(WorkflowAuditLog::count())->toBe(1);

    $log = WorkflowAuditLog::first();
    expect($log->workflow_id)->toBe($this->workflow->id);
    expect($log->subject_type)->toBe(Workflow::class);
    expect($log->subject_id)->toBe($this->workflow->id);
    expect($log->from_place)->toBe('draft');
    expect($log->to_place)->toBe('published');
    expect($log->transition)->toBe('publish');
});

test('audit log is not created when audit trail is disabled', function () {
    $this->workflow->update(['audit_trail_enabled' => false]);

    expect(WorkflowAuditLog::count())->toBe(0);

    $this->workflow->applyTransition('publish');

    expect(WorkflowAuditLog::count())->toBe(0);
});

test('audit log can be forced even when disabled', function () {
    $this->workflow->update(['audit_trail_enabled' => false]);

    expect(WorkflowAuditLog::count())->toBe(0);

    $this->workflow->applyTransition('publish', [], true);

    expect(WorkflowAuditLog::count())->toBe(1);
});

test('audit log can be disabled even when enabled', function () {
    expect(WorkflowAuditLog::count())->toBe(0);

    $this->workflow->applyTransition('publish', [], false);

    expect(WorkflowAuditLog::count())->toBe(0);
});

test('audit log stores context data', function () {
    $context = [
        'reason' => 'Ready for publication',
        'priority' => 'high',
    ];

    $this->workflow->applyTransition('publish', $context);

    $log = WorkflowAuditLog::first();
    expect($log->context)->toBe($context);
});

test('audit log stores metadata', function () {
    $this->workflow->applyTransition('publish');

    $log = WorkflowAuditLog::first();
    expect($log->metadata)->toBeArray();
    expect($log->metadata)->toHaveKey('ip_address');
    expect($log->metadata)->toHaveKey('user_agent');
    expect($log->metadata)->toHaveKey('timestamp');
});

test('audit log stores user id when authenticated', function () {
    $user = new class implements \Illuminate\Contracts\Auth\Authenticatable
    {
        public function getAuthIdentifierName()
        {
            return 'id';
        }

        public function getAuthIdentifier()
        {
            return 123;
        }

        public function getAuthPassword()
        {
            return 'password';
        }

        public function getRememberToken()
        {
            return null;
        }

        public function setRememberToken($value) {}

        public function getRememberTokenName()
        {
            return null;
        }

        public function getAuthPasswordName()
        {
            return 'password';
        }
    };

    auth()->setUser($user);

    $this->workflow->applyTransition('publish');

    $log = WorkflowAuditLog::first();
    expect($log->user_id)->toBe(123);
});

test('get audit trail returns logs for model', function () {
    $this->workflow->applyTransition('publish');

    $logs = $this->workflow->getAuditTrail();

    expect($logs)->toHaveCount(1);
    expect($logs->first()->transition)->toBe('publish');
});

test('get audit trail with limit', function () {
    // Apply multiple transitions
    $this->workflow->applyTransition('publish');
    $this->workflow->update(['marking' => 'draft']);
    $this->workflow->applyTransition('publish');
    $this->workflow->update(['marking' => 'draft']);
    $this->workflow->applyTransition('publish');

    $logs = $this->workflow->getAuditTrail(2);

    expect($logs)->toHaveCount(2);
});

test('recent audit logs helper method', function () {
    $this->workflow->applyTransition('publish');

    $logs = $this->workflow->recentAuditLogs(10);

    expect($logs)->toHaveCount(1);
});

test('has audit logs check', function () {
    expect($this->workflow->hasAuditLogs())->toBeFalse();

    $this->workflow->applyTransition('publish');

    expect($this->workflow->hasAuditLogs())->toBeTrue();
});

test('audit logs relationship', function () {
    $this->workflow->applyTransition('publish');

    $logs = $this->workflow->auditLogs;

    expect($logs)->toHaveCount(1);
    expect($logs->first())->toBeInstanceOf(WorkflowAuditLog::class);
});

test('workflow audit logs relationship', function () {
    $this->workflow->applyTransition('publish');

    $logs = $this->workflow->auditLogs;

    expect($logs)->toHaveCount(1);
});

test('audit log scope for workflow', function () {
    $this->workflow->applyTransition('publish');

    $logs = WorkflowAuditLog::forWorkflow($this->workflow->id)->get();

    expect($logs)->toHaveCount(1);
});

test('audit log scope for subject', function () {
    $this->workflow->applyTransition('publish');

    $logs = WorkflowAuditLog::forSubject(Workflow::class, $this->workflow->id)->get();

    expect($logs)->toHaveCount(1);
});

test('audit log scope by user', function () {
    $user = new class implements \Illuminate\Contracts\Auth\Authenticatable
    {
        public function getAuthIdentifierName()
        {
            return 'id';
        }

        public function getAuthIdentifier()
        {
            return 456;
        }

        public function getAuthPassword()
        {
            return 'password';
        }

        public function getRememberToken()
        {
            return null;
        }

        public function setRememberToken($value) {}

        public function getRememberTokenName()
        {
            return null;
        }

        public function getAuthPasswordName()
        {
            return 'password';
        }
    };

    auth()->setUser($user);
    $this->workflow->applyTransition('publish');

    $logs = WorkflowAuditLog::byUser(456)->get();

    expect($logs)->toHaveCount(1);
});

test('audit log scope by transition', function () {
    $this->workflow->applyTransition('publish');

    $logs = WorkflowAuditLog::byTransition('publish')->get();

    expect($logs)->toHaveCount(1);
});

test('audit log scope by place', function () {
    $this->workflow->applyTransition('publish');

    $fromLogs = WorkflowAuditLog::byPlace('draft')->get();
    $toLogs = WorkflowAuditLog::byPlace('published')->get();

    expect($fromLogs)->toHaveCount(1);
    expect($toLogs)->toHaveCount(1);
});

test('audit log description attribute', function () {
    $this->workflow->applyTransition('publish');

    $log = WorkflowAuditLog::first();

    expect($log->description)->toContain('draft');
    expect($log->description)->toContain('published');
    expect($log->description)->toContain('publish');
});

test('audit log is successful check', function () {
    $this->workflow->applyTransition('publish');

    $log = WorkflowAuditLog::first();

    expect($log->isSuccessful())->toBeTrue();
});
