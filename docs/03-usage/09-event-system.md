# Event System

The Flowstone package provides a comprehensive event system that allows you to hook into the workflow lifecycle and execute custom logic at various stages of a transition. This is built on top of Symfony Workflow's event system with Laravel-friendly abstractions.

## Table of Contents

- [Overview](#overview)
- [Event Firing Order](#event-firing-order)
- [Available Event Listeners](#available-event-listeners)
- [Creating Custom Event Listeners](#creating-custom-event-listeners)
- [Event Configuration](#event-configuration)
- [Guard Events](#guard-events)
- [Leave Events](#leave-events)
- [Transition Events](#transition-events)
- [Enter Events](#enter-events)
- [Entered Events](#entered-events)
- [Completed Events](#completed-events)
- [Announce Events](#announce-events)
- [Event Filtering](#event-filtering)
- [Helper Methods](#helper-methods)
- [Real-World Examples](#real-world-examples)
- [Best Practices](#best-practices)
- [Performance Considerations](#performance-considerations)

## Overview

Workflow events are fired automatically by Symfony Workflow at different stages during a transition. Flowstone provides Laravel-friendly abstract classes that you can extend to create custom event listeners.

**Key Benefits:**

- Hook into workflow lifecycle without modifying core code
- Send notifications when transitions occur
- Validate transitions with custom business logic
- Log workflow activity for audit trails
- Integrate with external systems (webhooks, APIs)
- Update related data automatically

## Event Firing Order

When a transition is applied, events are fired in this specific order:

```plaintext
1. Guard     → Validate if transition is allowed (can be blocked)
2. Leave     → Subject is leaving the current place
3. Transition → Subject is going through the transition
4. Enter     → Subject is about to enter new place (before marking update)
5. Entered   → Subject has entered new place (after marking update)
6. Completed → Transition is complete
7. Announce  → Announce newly available transitions (for each transition)
```

**Important Notes:**

- **Guard events** can block transitions by calling `$event->setBlocked(true)`
- **Leave/Enter events** fire even if the subject stays in the same place
- **Enter event** fires BEFORE marking is updated
- **Entered event** fires AFTER marking is updated
- **Announce events** fire once per newly available transition (can be multiple)

## Available Event Listeners

Flowstone provides 7 abstract event listener classes that you can extend:

| Listener Class | Event Type | Use Case |
|----------------|------------|----------|
| `GuardEventListener` | Guard | Validate and block transitions |
| `LeaveEventListener` | Leave | Cleanup before leaving a place |
| `TransitionEventListener` | Transition | Update data during transition |
| `EnterEventListener` | Enter | Prepare for entering new place |
| `EnteredEventListener` | Entered | Post-entry actions (notifications) |
| `CompletedEventListener` | Completed | Logging, analytics, follow-up |
| `AnnounceEventListener` | Announce | Notify about available actions |

All listeners extend the base `WorkflowEventSubscriber` class.

## Creating Custom Event Listeners

### Basic Structure

To create a custom event listener, extend one of the abstract listener classes and implement the required method:

```php
<?php

namespace App\Workflow\Listeners;

use CleaniqueCoders\Flowstone\Events\Entered\EnteredEventListener;
use Symfony\Component\Workflow\Event\EnteredEvent;

class DocumentPublishedListener extends EnteredEventListener
{
    // Optional: Filter by workflow name
    protected ?string $workflowName = 'document_approval';

    // Optional: Filter by place name
    protected ?string $placeName = 'published';

    protected function onEnteredPlace(EnteredEvent $event): void
    {
        $document = $event->getSubject();

        // Your custom logic here
        \Log::info("Document {$document->id} was published");
    }
}
```

### Registering Event Listeners

#### Method 1: Database Configuration (Recommended)

You can register event listeners directly in the workflow database configuration:

```php
use App\Workflow\Listeners\DocumentPublishedListener;
use App\Workflow\Listeners\SendEmailOnApprovalListener;

$workflow = Workflow::find($id);
$workflow->addEventListener(DocumentPublishedListener::class);
$workflow->addEventListener(SendEmailOnApprovalListener::class);
$workflow->save();
```

Or configure multiple listeners at once:

```php
$workflow->update([
    'event_listeners' => [
        DocumentPublishedListener::class,
        SendEmailOnApprovalListener::class,
        LogWorkflowTransitionListener::class,
    ],
]);
```

#### Method 2: Config File Configuration

Configure default event listeners in your config file:

```php
// config/flowstone.php
'default' => [
    'event_listeners' => [
        \App\Workflow\Listeners\DocumentPublishedListener::class,
        \App\Workflow\Listeners\SendEmailOnApprovalListener::class,
    ],
],
```

#### Method 3: Service Provider Registration

Register listeners globally in your service provider:

```php
// app/Providers/AppServiceProvider.php
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

public function boot()
{
    // Get the event dispatcher from the container
    $dispatcher = app(EventDispatcherInterface::class);

    // Register your custom listeners
    $dispatcher->addSubscriber(new \App\Workflow\Listeners\DocumentPublishedListener());
}
```

## Event Configuration

Flowstone provides powerful event configuration options that allow you to control which events are dispatched and which listeners are registered per workflow.

### Configuring Event Listeners

#### Add Event Listeners to a Workflow

```php
$workflow = Workflow::find($id);

// Add a listener
$workflow->addEventListener(\App\Listeners\SendNotificationOnApproval::class);
$workflow->save();

// Check if a listener is registered
if ($workflow->hasEventListener(\App\Listeners\SendNotificationOnApproval::class)) {
    // Listener is registered
}

// Remove a listener
$workflow->removeEventListener(\App\Listeners\SendNotificationOnApproval::class);
$workflow->save();
```

### Controlling Which Events are Dispatched

You can control which event types are dispatched for a specific workflow using two approaches:

#### Approach 1: Boolean Flags (Simple)

Use boolean flags for simple on/off control of event types:

```php
$workflow->update([
    'dispatch_guard_events' => true,
    'dispatch_leave_events' => true,
    'dispatch_transition_events' => true,
    'dispatch_enter_events' => true,
    'dispatch_entered_events' => true,
    'dispatch_completed_events' => true,
    'dispatch_announce_events' => false, // Disable announce events for performance
]);
```

#### Approach 2: Event Name Array (Advanced)

Specify exactly which events to dispatch:

```php
$workflow->update([
    'events_to_dispatch' => [
        'workflow.guard',
        'workflow.completed',
        'workflow.entered',
    ],
]);
```

You can also use workflow-specific or transition-specific patterns:

```php
$workflow->update([
    'events_to_dispatch' => [
        'workflow.document_approval.guard',
        'workflow.document_approval.publish.completed',
    ],
]);
```

### Checking Event Dispatch Configuration

```php
// Check if a specific event type should be dispatched
if ($workflow->shouldDispatchEvent('guard')) {
    // Guard events are enabled
}

if ($workflow->shouldDispatchEvent('announce')) {
    // Announce events are enabled
}

// Get complete event configuration
$config = $workflow->getEventConfiguration();
/*
Returns:
[
    'event_listeners' => ['App\\Listeners\\...'],
    'events_to_dispatch' => ['workflow.guard', 'workflow.completed'],
    'dispatch_flags' => [
        'guard' => true,
        'leave' => true,
        'transition' => true,
        'enter' => true,
        'entered' => true,
        'completed' => true,
        'announce' => false,
    ],
]
*/
```

### Configuration in Symfony Config

Event configuration is automatically included in the Symfony workflow configuration:

```php
$symfonyConfig = $workflow->getSymfonyConfig();
/*
Returns:
[
    'type' => 'state_machine',
    'places' => [...],
    'transitions' => [...],
    'event_listeners' => [
        'App\\Listeners\\SendNotificationOnApproval',
    ],
    'events_to_dispatch' => [
        'workflow.guard',
        'workflow.completed',
    ],
]
*/
```

### Database Configuration

The event configuration is stored in these database fields:

- `event_listeners` (JSON) - Array of listener class names
- `events_to_dispatch` (JSON) - Array of event names to dispatch
- `dispatch_guard_events` (boolean) - Enable/disable guard events
- `dispatch_leave_events` (boolean) - Enable/disable leave events
- `dispatch_transition_events` (boolean) - Enable/disable transition events
- `dispatch_enter_events` (boolean) - Enable/disable enter events
- `dispatch_entered_events` (boolean) - Enable/disable entered events
- `dispatch_completed_events` (boolean) - Enable/disable completed events
- `dispatch_announce_events` (boolean) - Enable/disable announce events

### Performance Optimization

Disable unnecessary events for better performance:

```php
// Disable announce events if you don't need them
// Announce events fire frequently (after every transition for every available transition)
$workflow->update([
    'dispatch_announce_events' => false,
]);

// Only enable the events you actually need
$workflow->update([
    'dispatch_guard_events' => true,  // For validation
    'dispatch_completed_events' => true,  // For notifications
    'dispatch_entered_events' => false,  // Don't need these
    'dispatch_leave_events' => false,  // Don't need these
    'dispatch_transition_events' => false,  // Don't need these
    'dispatch_enter_events' => false,  // Don't need these
    'dispatch_announce_events' => false,  // Don't need these
]);
```

## Guard Events

Guard events allow you to add custom validation logic and block transitions.

### Example: Validate Document Before Publishing

```php
<?php

namespace App\Workflow\Listeners;

use CleaniqueCoders\Flowstone\Events\Guards\GuardEventListener;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\TransitionBlocker;

class DocumentPublishGuard extends GuardEventListener
{
    protected ?string $workflowName = 'document_approval';
    protected ?string $transitionName = 'publish';

    protected function guardTransition(GuardEvent $event): void
    {
        $document = $event->getSubject();

        // Block if title is empty
        if (empty($document->title)) {
            $this->block($event, 'Document must have a title before publishing');
            return;
        }

        // Block if not reviewed
        if (!$document->reviewed_at) {
            $event->addTransitionBlocker(
                new TransitionBlocker('Document must be reviewed before publishing', 'not_reviewed')
            );
        }

        // Block if user doesn't have permission
        if (!$this->hasPermission('publish-documents')) {
            $this->block($event, 'You do not have permission to publish documents');
        }
    }
}
```

### Guard Helper Methods

```php
// Check if transition is already blocked
if ($this->isBlocked($event)) {
    return;
}

// Block with a message
$this->block($event, 'Transition not allowed');

// Add a transition blocker
$this->addBlocker($event, new TransitionBlocker('reason', 'code'));

// Get all blockers
$blockers = $this->getBlockers($event);

// Check authentication
if (!$this->isAuthenticated()) {
    $this->block($event, 'Must be logged in');
}

// Check role (requires Spatie Permission or similar)
if (!$this->hasRole('admin')) {
    $this->block($event, 'Admin role required');
}

// Check permission
if (!$this->hasPermission('approve-documents')) {
    $this->block($event, 'Permission required');
}

// Get current user
$user = $this->getUser();
```

## Leave Events

Leave events fire when the subject is about to leave a place. Use these for cleanup operations.

### Example: Cleanup Draft Data

```php
<?php

namespace App\Workflow\Listeners;

use CleaniqueCoders\Flowstone\Events\Leave\LeaveEventListener;
use Symfony\Component\Workflow\Event\LeaveEvent;

class CleanupDraftListener extends LeaveEventListener
{
    protected ?string $workflowName = 'document_approval';
    protected ?string $placeName = 'draft';

    protected function onLeavingPlace(LeaveEvent $event): void
    {
        $document = $event->getSubject();

        // Get places being left
        $leavingPlaces = $this->getLeavingPlaces($event);

        // Get destination
        $destination = $this->getDestinationPlaces($event);

        // Cleanup draft-specific data
        $document->draft_metadata = null;
        $document->save();

        \Log::info("Document {$document->id} leaving draft", [
            'from' => $leavingPlaces,
            'to' => $destination,
            'transition' => $this->getTransitionName($event),
        ]);
    }
}
```

### Leave Helper Methods

```php
// Get places being left
$places = $this->getLeavingPlaces($event); // ['draft']

// Get first place (for single-state workflows)
$place = $this->getLeavingPlace($event); // 'draft'

// Get destination places
$destinations = $this->getDestinationPlaces($event); // ['review']

// Get transition name
$transition = $this->getTransitionName($event); // 'submit_for_review'
```

## Transition Events

Transition events fire during the transition process. Use these to update related data or perform actions.

### Example: Update Order During Shipment

```php
<?php

namespace App\Workflow\Listeners;

use CleaniqueCoders\Flowstone\Events\Transition\TransitionEventListener;
use Symfony\Component\Workflow\Event\TransitionEvent;
use App\Services\ShippingService;

class ShipOrderListener extends TransitionEventListener
{
    protected ?string $workflowName = 'order_fulfillment';
    protected ?string $transitionName = 'ship';

    protected function onTransitioning(TransitionEvent $event): void
    {
        $order = $event->getSubject();

        // Generate shipping label during transition
        $trackingNumber = ShippingService::generateLabel($order);

        $order->update([
            'tracking_number' => $trackingNumber,
            'shipped_at' => now(),
            'shipping_carrier' => 'USPS',
        ]);

        // Log the transition
        \Log::info("Order {$order->id} is being shipped", [
            'tracking' => $trackingNumber,
            'from' => $this->getFromPlaces($event),
            'to' => $this->getToPlaces($event),
        ]);
    }
}
```

### Transition Helper Methods

```php
// Get transition name
$name = $this->getTransitionName($event); // 'ship'

// Get source places
$from = $this->getFromPlaces($event); // ['packed']

// Get destination places
$to = $this->getToPlaces($event); // ['shipped']

// Get current marking
$current = $this->getCurrentPlaces($event);
```

## Enter Events

Enter events fire BEFORE the marking is updated. Use these to prepare the subject for the new state.

### Example: Prepare Task for In Progress State

```php
<?php

namespace App\Workflow\Listeners;

use CleaniqueCoders\Flowstone\Events\Enter\EnterEventListener;
use Symfony\Component\Workflow\Event\EnterEvent;

class PrepareTaskListener extends EnterEventListener
{
    protected ?string $workflowName = 'task_management';
    protected ?string $placeName = 'in_progress';

    protected function onEnteringPlace(EnterEvent $event): void
    {
        $task = $event->getSubject();

        // Prepare task before it enters "in_progress"
        // Note: Marking is NOT updated yet
        $task->started_at = now();
        $task->assigned_to = auth()->id();

        // Don't save yet if you want entered event to handle that

        \Log::info("Task {$task->id} is about to enter in_progress", [
            'entering' => $this->getEnteringPlaces($event),
            'current' => $this->getCurrentPlaces($event), // Still shows old place
        ]);
    }
}
```

### Enter Helper Methods

```php
// Get places being entered
$places = $this->getEnteringPlaces($event); // ['in_progress']

// Get first place (for single-state workflows)
$place = $this->getEnteringPlace($event); // 'in_progress'

// Get current places (still old places)
$current = $this->getCurrentPlaces($event); // ['pending']

// Get transition name
$transition = $this->getTransitionName($event);
```

## Entered Events

Entered events fire AFTER the marking is updated. Use these for notifications, integrations, and side effects.

### Example: Send Notifications After State Change

```php
<?php

namespace App\Workflow\Listeners;

use CleaniqueCoders\Flowstone\Events\Entered\EnteredEventListener;
use Symfony\Component\Workflow\Event\EnteredEvent;
use App\Notifications\OrderShippedNotification;
use Illuminate\Support\Facades\Mail;

class OrderShippedNotificationListener extends EnteredEventListener
{
    protected ?string $workflowName = 'order_fulfillment';
    protected ?string $placeName = 'shipped';

    protected function onEnteredPlace(EnteredEvent $event): void
    {
        $order = $event->getSubject();

        // Marking is now updated - order is in "shipped" state

        // Send email notification
        Mail::to($order->customer->email)
            ->send(new OrderShippedNotification($order));

        // Send SMS notification
        if ($order->customer->phone) {
            SmsService::send(
                $order->customer->phone,
                "Your order #{$order->number} has been shipped! Tracking: {$order->tracking_number}"
            );
        }

        // Update external system
        ExternalAPI::notifyOrderShipped($order);

        \Log::info("Notifications sent for shipped order {$order->id}");
    }
}
```

### Entered Helper Methods

```php
// Get places that were entered
$places = $this->getEnteredPlaces($event); // ['shipped']

// Get first place (for single-state workflows)
$place = $this->getEnteredPlace($event); // 'shipped'

// Get current places (now shows new places)
$current = $this->getCurrentPlaces($event); // ['shipped']

// Get transition name
$transition = $this->getTransitionName($event);

// Get source places
$from = $this->getFromPlaces($event); // ['packed']
```

## Completed Events

Completed events fire after the transition is fully complete. Use these for logging, analytics, and follow-up processes.

### Example: Log Workflow Activity

```php
<?php

namespace App\Workflow\Listeners;

use CleaniqueCoders\Flowstone\Events\Completed\CompletedEventListener;
use Symfony\Component\Workflow\Event\CompletedEvent;

class WorkflowActivityLogger extends CompletedEventListener
{
    protected ?string $workflowName = 'document_approval';

    protected function onTransitionCompleted(CompletedEvent $event): void
    {
        $document = $event->getSubject();
        $transition = $this->getCompletedTransition($event);
        $from = implode(', ', $this->getFromPlaces($event));
        $to = implode(', ', $this->getToPlaces($event));

        // Log to database
        \DB::table('workflow_activity_log')->insert([
            'workflow_name' => $event->getWorkflowName(),
            'subject_type' => get_class($document),
            'subject_id' => $document->id,
            'transition' => $transition,
            'from_places' => $from,
            'to_places' => $to,
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'context' => json_encode($this->getTransitionContext($event)),
            'created_at' => now(),
        ]);

        // Send to analytics
        Analytics::track('workflow_transition_completed', [
            'workflow' => $event->getWorkflowName(),
            'transition' => $transition,
            'from' => $from,
            'to' => $to,
        ]);

        // Trigger follow-up processes
        if ($transition === 'approve') {
            \App\Jobs\PublishDocumentJob::dispatch($document);
        }
    }
}
```

### Completed Helper Methods

```php
// Get completed transition name
$transition = $this->getCompletedTransition($event); // 'approve'

// Get source places
$from = $this->getFromPlaces($event); // ['review']

// Get destination places
$to = $this->getToPlaces($event); // ['approved']

// Get current places
$current = $this->getCurrentPlaces($event); // ['approved']

// Get transition context
$context = $this->getTransitionContext($event);
```

## Announce Events

Announce events fire for each newly available transition after a transition completes. Use these to notify users about available actions.

**Performance Warning:** Announce events trigger guard events for each available transition, which can impact performance if guard logic is intensive.

### Example: Notify About Available Actions

```php
<?php

namespace App\Workflow\Listeners;

use CleaniqueCoders\Flowstone\Events\Announce\AnnounceEventListener;
use Symfony\Component\Workflow\Event\AnnounceEvent;
use App\Notifications\AvailableActionNotification;

class NotifyAvailableActionsListener extends AnnounceEventListener
{
    protected ?string $workflowName = 'task_management';

    protected function onTransitionAnnounced(AnnounceEvent $event): void
    {
        $task = $event->getSubject();
        $availableTransition = $this->getAnnouncedTransition($event);

        // Skip if this is initial marking announcement
        if ($this->isInitialAnnouncement($event)) {
            return;
        }

        // Notify assigned user about available actions
        if ($task->assigned_to) {
            $task->assignedUser->notify(
                new AvailableActionNotification($task, $availableTransition)
            );
        }

        // Update cached available actions
        \Cache::tags(['tasks', "task:{$task->id}"])
            ->put(
                "available_transitions:{$task->id}",
                $task->getEnabledTransitions(),
                now()->addHours(1)
            );
    }
}
```

### Announce Helper Methods

```php
// Get announced transition name
$transition = $this->getAnnouncedTransition($event); // 'complete'

// Get destination places for this transition
$destinations = $this->getDestinationPlaces($event); // ['completed']

// Get current places
$current = $this->getCurrentPlaces($event); // ['in_progress']

// Check if this is initial announcement
if ($this->isInitialAnnouncement($event)) {
    // Workflow just initialized
}
```

## Event Filtering

You can filter which events your listener handles by setting class properties:

### Filter by Workflow Name

```php
class MyListener extends CompletedEventListener
{
    // Only handle events from 'document_approval' workflow
    protected ?string $workflowName = 'document_approval';

    // ...
}
```

### Filter by Transition Name

```php
class MyGuard extends GuardEventListener
{
    // Only handle 'publish' transition
    protected ?string $transitionName = 'publish';

    // ...
}
```

### Filter by Place Name

```php
class MyListener extends EnteredEventListener
{
    // Only handle entering 'published' place
    protected ?string $placeName = 'published';

    // ...
}
```

### Combine Filters

```php
class SpecificListener extends EnteredEventListener
{
    protected ?string $workflowName = 'document_approval';
    protected ?string $placeName = 'published';

    // Only handles when documents enter 'published' in 'document_approval' workflow

    // ...
}
```

### Manual Filtering

You can also filter manually in your handler method:

```php
protected function onEnteredPlace(EnteredEvent $event): void
{
    $document = $event->getSubject();

    // Custom filtering logic
    if (!$document->is_important) {
        return; // Skip this event
    }

    // Only process on weekdays
    if (now()->isWeekend()) {
        return;
    }

    // Your logic here
}
```

## Helper Methods

All event listeners inherit helper methods from `WorkflowEventSubscriber`:

```php
// Get the subject (your model)
$subject = $this->getSubject($event);

// Get workflow name
$workflowName = $this->getWorkflowName($event); // 'document_approval'

// Get transition object
$transition = $this->getTransition($event);

// Get marking object
$marking = $this->getMarking($event);

// Get metadata
$metadata = $this->getMetadata($event, 'color'); // Workflow metadata
$metadata = $this->getMetadata($event, 'priority', $transition); // Transition metadata

// Get context
$context = $this->getContext($event); // ['user_id' => 123, 'reason' => 'approved']
```

## Real-World Examples

### Example 1: Email Notifications on Approval

```php
<?php

namespace App\Workflow\Listeners;

use CleaniqueCoders\Flowstone\Events\Entered\EnteredEventListener;
use Symfony\Component\Workflow\Event\EnteredEvent;
use App\Mail\DocumentApprovedMail;
use Illuminate\Support\Facades\Mail;

class SendApprovalEmailListener extends EnteredEventListener
{
    protected ?string $workflowName = 'document_approval';
    protected ?string $placeName = 'approved';

    protected function onEnteredPlace(EnteredEvent $event): void
    {
        $document = $event->getSubject();

        // Send email to document owner
        Mail::to($document->owner->email)
            ->send(new DocumentApprovedMail($document));

        // Send email to all reviewers
        foreach ($document->reviewers as $reviewer) {
            Mail::to($reviewer->email)
                ->send(new DocumentApprovedMail($document));
        }
    }
}
```

### Example 2: Slack Notification on Deployment

```php
<?php

namespace App\Workflow\Listeners;

use CleaniqueCoders\Flowstone\Events\Completed\CompletedEventListener;
use Symfony\Component\Workflow\Event\CompletedEvent;
use Illuminate\Support\Facades\Http;

class SlackDeploymentNotifier extends CompletedEventListener
{
    protected ?string $workflowName = 'deployment_pipeline';
    protected ?string $transitionName = 'deploy_production';

    protected function onTransitionCompleted(CompletedEvent $event): void
    {
        $deployment = $event->getSubject();

        $message = [
            'text' => '🚀 Production Deployment Complete',
            'blocks' => [
                [
                    'type' => 'section',
                    'text' => [
                        'type' => 'mrkdwn',
                        'text' => "*Deployment #{$deployment->id} has been deployed to production*",
                    ],
                ],
                [
                    'type' => 'section',
                    'fields' => [
                        ['type' => 'mrkdwn', 'text' => "*Version:*\n{$deployment->version}"],
                        ['type' => 'mrkdwn', 'text' => "*Environment:*\nProduction"],
                        ['type' => 'mrkdwn', 'text' => "*Deployed By:*\n{$deployment->deployer->name}"],
                        ['type' => 'mrkdwn', 'text' => "*Time:*\n" . now()->format('Y-m-d H:i:s')],
                    ],
                ],
            ],
        ];

        Http::post(config('services.slack.webhook_url'), $message);
    }
}
```

### Example 3: Webhook Integration

```php
<?php

namespace App\Workflow\Listeners;

use CleaniqueCoders\Flowstone\Events\Completed\CompletedEventListener;
use Symfony\Component\Workflow\Event\CompletedEvent;
use Illuminate\Support\Facades\Http;

class WebhookIntegrationListener extends CompletedEventListener
{
    protected function onTransitionCompleted(CompletedEvent $event): void
    {
        $subject = $event->getSubject();

        // Get webhook URL from subject or config
        $webhookUrl = $subject->webhook_url ?? config('workflow.webhook_url');

        if (!$webhookUrl) {
            return;
        }

        $payload = [
            'event' => 'workflow.transition.completed',
            'workflow' => $event->getWorkflowName(),
            'transition' => $this->getCompletedTransition($event),
            'from' => $this->getFromPlaces($event),
            'to' => $this->getToPlaces($event),
            'subject' => [
                'type' => get_class($subject),
                'id' => $subject->id,
                'data' => $subject->toArray(),
            ],
            'timestamp' => now()->toIso8601String(),
        ];

        try {
            Http::timeout(5)
                ->retry(3, 100)
                ->post($webhookUrl, $payload);
        } catch (\Exception $e) {
            \Log::error("Webhook failed: {$e->getMessage()}", [
                'url' => $webhookUrl,
                'payload' => $payload,
            ]);
        }
    }
}
```

### Example 4: Multi-Guard Validation

```php
<?php

namespace App\Workflow\Listeners;

use CleaniqueCoders\Flowstone\Events\Guards\GuardEventListener;
use Symfony\Component\Workflow\Event\GuardEvent;

class ComprehensiveDocumentGuard extends GuardEventListener
{
    protected ?string $workflowName = 'document_approval';

    protected function guardTransition(GuardEvent $event): void
    {
        $document = $event->getSubject();
        $transitionName = $event->getTransition()->getName();

        // Common validations
        if (!$this->isAuthenticated()) {
            $this->block($event, 'You must be logged in');
            return;
        }

        // Transition-specific validations
        match ($transitionName) {
            'submit_for_review' => $this->guardSubmit($event, $document),
            'approve' => $this->guardApprove($event, $document),
            'publish' => $this->guardPublish($event, $document),
            'reject' => $this->guardReject($event, $document),
            default => null,
        };
    }

    private function guardSubmit(GuardEvent $event, $document): void
    {
        if (empty($document->title)) {
            $this->block($event, 'Document must have a title');
        }

        if (empty($document->content)) {
            $this->block($event, 'Document must have content');
        }

        if ($document->word_count < 100) {
            $this->block($event, 'Document must be at least 100 words');
        }
    }

    private function guardApprove(GuardEvent $event, $document): void
    {
        if (!$this->hasRole('reviewer')) {
            $this->block($event, 'Only reviewers can approve documents');
        }

        if ($document->author_id === $this->getUser()?->id) {
            $this->block($event, 'You cannot approve your own document');
        }
    }

    private function guardPublish(GuardEvent $event, $document): void
    {
        if (!$this->hasPermission('publish-documents')) {
            $this->block($event, 'You do not have permission to publish');
        }

        if (!$document->reviewed_at) {
            $this->block($event, 'Document must be reviewed before publishing');
        }

        if ($document->reviewers->count() < 2) {
            $this->block($event, 'Document needs at least 2 reviewers');
        }
    }

    private function guardReject(GuardEvent $event, $document): void
    {
        $context = $this->getContext($event);

        if (empty($context['rejection_reason'])) {
            $this->block($event, 'Rejection reason is required');
        }

        if (!$this->hasRole(['reviewer', 'admin'])) {
            $this->block($event, 'Only reviewers and admins can reject');
        }
    }
}
```

## Best Practices

### 1. Keep Listeners Focused

Each listener should handle one specific concern:

```php
// ✅ Good - Single responsibility
class SendApprovalEmailListener extends EnteredEventListener { }
class LogApprovalListener extends CompletedEventListener { }
class UpdateAnalyticsListener extends CompletedEventListener { }

// ❌ Bad - Doing too much
class DoEverythingListener extends EnteredEventListener {
    // Sending emails, logging, analytics, webhooks all in one
}
```

### 2. Use Appropriate Event Types

Choose the right event for your use case:

- **Guard** - Validation and authorization
- **Leave** - Cleanup before leaving state
- **Transition** - Update related data during transition
- **Enter** - Prepare for new state (before marking update)
- **Entered** - Post-transition actions (after marking update) - **Most common**
- **Completed** - Logging, analytics, follow-up processes
- **Announce** - Notify about available actions (use sparingly)

### 3. Handle Errors Gracefully

Don't let event listeners crash your workflow:

```php
protected function onEnteredPlace(EnteredEvent $event): void
{
    try {
        // Potentially failing operation
        ExternalAPI::notifyOrderShipped($order);
    } catch (\Exception $e) {
        \Log::error("Failed to notify external API: {$e->getMessage()}", [
            'order_id' => $order->id,
            'exception' => $e,
        ]);
        // Workflow continues even if notification fails
    }
}
```

### 4. Use Queued Jobs for Heavy Operations

Don't block workflow execution with slow operations:

```php
protected function onEnteredPlace(EnteredEvent $event): void
{
    $document = $event->getSubject();

    // ❌ Bad - Blocks workflow execution
    PdfGenerator::generate($document); // Slow operation

    // ✅ Good - Queue it
    \App\Jobs\GeneratePdfJob::dispatch($document);
}
```

### 5. Leverage Filtering

Use class properties to filter events instead of manual checks:

```php
// ✅ Good - Declarative filtering
class MyListener extends EnteredEventListener
{
    protected ?string $workflowName = 'document_approval';
    protected ?string $placeName = 'published';
}

// ❌ Bad - Manual filtering
class MyListener extends EnteredEventListener
{
    protected function onEnteredPlace(EnteredEvent $event): void
    {
        if ($event->getWorkflowName() !== 'document_approval') return;
        if (!in_array('published', $this->getEnteredPlaces($event))) return;
        // ...
    }
}
```

### 6. Test Your Event Listeners

Write tests for your custom event listeners:

```php
public function test_sends_email_when_document_approved()
{
    Mail::fake();

    $document = Document::factory()->create(['marking' => 'review']);
    $document->applyTransition('approve');

    Mail::assertSent(DocumentApprovedMail::class, function ($mail) use ($document) {
        return $mail->hasTo($document->owner->email);
    });
}
```

## Performance Considerations

### 1. Announce Events Can Be Expensive

Announce events fire once per newly available transition and trigger guard events for each. This can impact performance:

```php
// Disable announce events for performance-critical transitions
$workflow->apply($subject, 'transition_name', [
    \Symfony\Component\Workflow\Workflow::DISABLE_ANNOUNCE_EVENT => true,
]);
```

### 2. Guard Events Fire Frequently

Guard events fire every time you check if a transition is available:

```php
// This fires guard events
$workflow->can($subject, 'transition_name');

// This also fires guard events
$workflow->getEnabledTransitions($subject);

// And this too
$workflow->apply($subject, 'transition_name');
```

Keep guard logic fast and avoid database queries if possible.

### 3. Cache Available Transitions

Cache the result of expensive guard checks:

```php
$cacheKey = "workflow_transitions:{$model->id}";
$transitions = Cache::remember($cacheKey, 300, function () use ($model) {
    return $model->getEnabledTransitions();
});

// Clear cache when marking changes
$model->applyTransition('approve');
Cache::forget($cacheKey);
```

### 4. Use Queued Notifications

For non-critical notifications, queue them:

```php
protected function onEnteredPlace(EnteredEvent $event): void
{
    $document = $event->getSubject();

    // Queue notification instead of sending immediately
    \App\Jobs\SendDocumentApprovedNotification::dispatch($document)
        ->onQueue('notifications')
        ->delay(now()->addSeconds(5));
}
```

### 5. Batch Operations

If you're processing multiple subjects, disable events during bulk operations:

```php
// Coming in Phase 3: Per-transition event control
$context = [
    \Symfony\Component\Workflow\Workflow::DISABLE_LEAVE_EVENT => true,
    \Symfony\Component\Workflow\Workflow::DISABLE_TRANSITION_EVENT => true,
    \Symfony\Component\Workflow\Workflow::DISABLE_ANNOUNCE_EVENT => true,
];

foreach ($documents as $document) {
    $document->applyTransition('bulk_approve', $context);
}
```

## Next Steps

- **Phase 2**: Event configuration in database (coming soon)
- **Phase 3**: Per-transition event control (coming soon)
- **Phase 4**: Laravel event bridge for broadcasting (optional)
- **Phase 5**: UI for managing event listeners (optional)

---

For more information, see:

- [Guards and Blockers](05-guards-and-blockers.md)
- [Audit Trail](04-audit-trail.md)
- [Advanced Features](07-advanced-features.md)
