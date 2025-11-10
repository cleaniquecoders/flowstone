<?php

namespace CleaniqueCoders\Flowstone\Events;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\AnnounceEvent;
use Symfony\Component\Workflow\Event\CompletedEvent;
use Symfony\Component\Workflow\Event\EnteredEvent;
use Symfony\Component\Workflow\Event\EnterEvent;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\Event\LeaveEvent;
use Symfony\Component\Workflow\Event\TransitionEvent;

/**
 * Base class for workflow event subscribers.
 *
 * This abstract class provides a foundation for creating custom workflow event listeners.
 * Developers can extend this class and override specific event handler methods to add
 * custom logic to their workflow transitions.
 *
 * Event Firing Order:
 * 1. guard     - Validate whether the transition is allowed
 * 2. leave     - Subject is about to leave a place
 * 3. transition - Subject is going through the transition
 * 4. enter     - Subject is about to enter a new place (before marking update)
 * 5. entered   - Subject has entered the place (after marking update)
 * 6. completed - Transition is complete
 * 7. announce  - Announce all newly accessible transitions
 *
 * @example
 * ```php
 * class BlogPostWorkflowSubscriber extends WorkflowEventSubscriber
 * {
 *     protected ?string $workflowName = 'blog_publishing';
 *
 *     protected function onGuard(GuardEvent $event): void
 *     {
 *         $post = $event->getSubject();
 *         if (empty($post->title)) {
 *             $event->setBlocked(true, 'Post must have a title');
 *         }
 *     }
 *
 *     protected function onCompleted(CompletedEvent $event): void
 *     {
 *         // Send notification, log activity, etc.
 *     }
 * }
 * ```
 */
abstract class WorkflowEventSubscriber implements EventSubscriberInterface
{
    /**
     * The workflow name this subscriber listens to.
     * Set to null to listen to all workflows.
     */
    protected ?string $workflowName = null;

    /**
     * The transition name this subscriber listens to.
     * Set to null to listen to all transitions.
     */
    protected ?string $transitionName = null;

    /**
     * The place name this subscriber listens to.
     * Set to null to listen to all places.
     */
    protected ?string $placeName = null;

    /**
     * Subscribe to workflow events.
     *
     * This method automatically registers event handlers based on the configured
     * workflow name, transition name, and place name. Override this method if you
     * need custom event subscription logic.
     */
    public static function getSubscribedEvents(): array
    {
        $instance = new static;
        $events = [];

        // Guard events
        if (method_exists($instance, 'onGuard')) {
            $events[$instance->buildEventName('workflow.guard')] = 'onGuard';
        }

        // Leave events
        if (method_exists($instance, 'onLeave')) {
            $events[$instance->buildEventName('workflow.leave')] = 'onLeave';
        }

        // Transition events
        if (method_exists($instance, 'onTransition')) {
            $events[$instance->buildEventName('workflow.transition')] = 'onTransition';
        }

        // Enter events
        if (method_exists($instance, 'onEnter')) {
            $events[$instance->buildEventName('workflow.enter')] = 'onEnter';
        }

        // Entered events
        if (method_exists($instance, 'onEntered')) {
            $events[$instance->buildEventName('workflow.entered')] = 'onEntered';
        }

        // Completed events
        if (method_exists($instance, 'onCompleted')) {
            $events[$instance->buildEventName('workflow.completed')] = 'onCompleted';
        }

        // Announce events
        if (method_exists($instance, 'onAnnounce')) {
            $events[$instance->buildEventName('workflow.announce')] = 'onAnnounce';
        }

        return $events;
    }

    /**
     * Build the event name based on workflow, transition, and place configuration.
     */
    protected function buildEventName(string $baseEvent): string
    {
        $parts = [$baseEvent];

        if ($this->workflowName !== null) {
            $parts[] = $this->workflowName;
        }

        // For place-specific events (leave, enter, entered)
        if ($this->placeName !== null && in_array($baseEvent, ['workflow.leave', 'workflow.enter', 'workflow.entered'])) {
            $parts[] = $this->placeName;
        }

        // For transition-specific events (guard, transition, completed, announce)
        if ($this->transitionName !== null && in_array($baseEvent, ['workflow.guard', 'workflow.transition', 'workflow.completed', 'workflow.announce'])) {
            $parts[] = $this->transitionName;
        }

        return implode('.', array_filter($parts));
    }

    /**
     * Check if this event should be handled based on workflow/transition/place filters.
     */
    protected function shouldHandle(Event $event): bool
    {
        if ($this->workflowName !== null && $event->getWorkflowName() !== $this->workflowName) {
            return false;
        }

        if ($this->transitionName !== null && $event->getTransition()->getName() !== $this->transitionName) {
            return false;
        }

        return true;
    }

    /**
     * Handle guard events.
     * Override this method to add custom guard logic.
     */
    protected function onGuard(GuardEvent $event): void
    {
        // Override in child classes
    }

    /**
     * Handle leave events.
     * Override this method to add custom leave logic.
     */
    protected function onLeave(LeaveEvent $event): void
    {
        // Override in child classes
    }

    /**
     * Handle transition events.
     * Override this method to add custom transition logic.
     */
    protected function onTransition(TransitionEvent $event): void
    {
        // Override in child classes
    }

    /**
     * Handle enter events.
     * Override this method to add custom enter logic.
     */
    protected function onEnter(EnterEvent $event): void
    {
        // Override in child classes
    }

    /**
     * Handle entered events.
     * Override this method to add custom entered logic.
     */
    protected function onEntered(EnteredEvent $event): void
    {
        // Override in child classes
    }

    /**
     * Handle completed events.
     * Override this method to add custom completed logic.
     */
    protected function onCompleted(CompletedEvent $event): void
    {
        // Override in child classes
    }

    /**
     * Handle announce events.
     * Override this method to add custom announce logic.
     */
    protected function onAnnounce(AnnounceEvent $event): void
    {
        // Override in child classes
    }

    /**
     * Get the subject from the event.
     */
    protected function getSubject(Event $event): object
    {
        return $event->getSubject();
    }

    /**
     * Get the workflow name from the event.
     */
    protected function getWorkflowName(Event $event): string
    {
        return $event->getWorkflowName();
    }

    /**
     * Get the transition from the event.
     */
    protected function getTransition(Event $event): \Symfony\Component\Workflow\Transition
    {
        return $event->getTransition();
    }

    /**
     * Get the marking from the event.
     */
    protected function getMarking(Event $event): \Symfony\Component\Workflow\Marking
    {
        return $event->getMarking();
    }

    /**
     * Get metadata from the event.
     */
    protected function getMetadata(Event $event, string $key, $subject = null): mixed
    {
        return $event->getMetadata($key, $subject);
    }

    /**
     * Get the context from the event's marking (if available).
     */
    protected function getContext(Event $event): array
    {
        return $event->getMarking()->getContext() ?? [];
    }
}
