<?php

namespace CleaniqueCoders\Flowstone\Events\Completed;

use CleaniqueCoders\Flowstone\Events\WorkflowEventSubscriber;
use Symfony\Component\Workflow\Event\CompletedEvent;

/**
 * Completed Event Listener
 *
 * Completed events are fired after a transition has been fully completed.
 * This is the final event in the transition sequence (before announce events).
 *
 * Use this listener for post-transition actions like logging, analytics, notifications,
 * or triggering follow-up processes.
 *
 * Event Order: guard → leave → transition → enter → entered → **completed** → announce
 *
 * @example
 * ```php
 * class WorkflowCompletedLogger extends CompletedEventListener
 * {
 *     protected ?string $workflowName = 'document_approval';
 *
 *     protected function onTransitionCompleted(CompletedEvent $event): void
 *     {
 *         $document = $event->getSubject();
 *         $transition = $event->getTransition()->getName();
 *         $toPlaces = implode(', ', $event->getTransition()->getTos());
 *
 *         // Log the completed transition
 *         Log::info('Workflow transition completed', [
 *             'workflow' => $event->getWorkflowName(),
 *             'subject_type' => get_class($document),
 *             'subject_id' => $document->id,
 *             'transition' => $transition,
 *             'to_places' => $toPlaces,
 *             'user_id' => auth()->id(),
 *         ]);
 *
 *         // Send analytics event
 *         Analytics::track('workflow_transition_completed', [
 *             'workflow' => $event->getWorkflowName(),
 *             'transition' => $transition,
 *         ]);
 *
 *         // Trigger follow-up processes
 *         if ($transition === 'approve') {
 *             PublishDocumentJob::dispatch($document);
 *         }
 *     }
 * }
 * ```
 */
abstract class CompletedEventListener extends WorkflowEventSubscriber
{
    /**
     * Subscribe to completed events only.
     */
    public static function getSubscribedEvents(): array
    {
        $instance = new static;

        return [
            $instance->buildEventName('workflow.completed') => 'handleCompleted',
        ];
    }

    /**
     * Handle the completed event.
     *
     * This method is called automatically by Symfony's event dispatcher.
     * It checks if the event should be handled and delegates to onTransitionCompleted().
     */
    final public function handleCompleted(CompletedEvent $event): void
    {
        if (! $this->shouldHandle($event)) {
            return;
        }

        $this->onTransitionCompleted($event);
    }

    /**
     * Called when a transition has been completed.
     *
     * Override this method to implement custom logic after a transition completes.
     * At this point, the subject has fully transitioned to the new place.
     *
     * @param  CompletedEvent  $event  The completed event containing subject, transition, and marking
     */
    abstract protected function onTransitionCompleted(CompletedEvent $event): void;

    /**
     * Get the transition name that was completed.
     */
    protected function getCompletedTransition(CompletedEvent $event): string
    {
        return $event->getTransition()->getName();
    }

    /**
     * Get the source places (where the subject came from).
     */
    protected function getFromPlaces(CompletedEvent $event): array
    {
        return $event->getTransition()->getFroms();
    }

    /**
     * Get the destination places (where the subject is now).
     */
    protected function getToPlaces(CompletedEvent $event): array
    {
        return $event->getTransition()->getTos();
    }

    /**
     * Get the current places (after transition).
     */
    protected function getCurrentPlaces(CompletedEvent $event): array
    {
        return array_keys($event->getMarking()->getPlaces());
    }

    /**
     * Get the context from the marking (if available).
     */
    protected function getTransitionContext(CompletedEvent $event): array
    {
        return $this->getContext($event);
    }
}
