<?php

namespace CleaniqueCoders\Flowstone\Events\Enter;

use CleaniqueCoders\Flowstone\Events\WorkflowEventSubscriber;
use Symfony\Component\Workflow\Event\EnterEvent;

/**
 * Enter Event Listener
 *
 * Enter events are fired when the subject is about to enter a new place.
 * This event occurs BEFORE the marking is updated, so the subject is still marked
 * as being in the old place.
 *
 * Use this listener to prepare the subject for the new state before it officially enters.
 *
 * Event Order: guard → leave → transition → **enter** → entered → completed → announce
 *
 * @example
 * ```php
 * class TaskEnterListener extends EnterEventListener
 * {
 *     protected ?string $workflowName = 'task_management';
 *     protected ?string $placeName = 'in_progress';
 *
 *     protected function onEnteringPlace(EnterEvent $event): void
 *     {
 *         $task = $event->getSubject();
 *
 *         // Prepare the task before it enters "in_progress"
 *         $task->started_at = now();
 *         $task->assigned_to = auth()->id();
 *
 *         // Note: Don't save yet if you want entered event to handle that
 *     }
 * }
 * ```
 */
abstract class EnterEventListener extends WorkflowEventSubscriber
{
    /**
     * Subscribe to enter events only.
     */
    public static function getSubscribedEvents(): array
    {
        $instance = new static;

        return [
            $instance->buildEventName('workflow.enter') => 'handleEnter',
        ];
    }

    /**
     * Handle the enter event.
     *
     * This method is called automatically by Symfony's event dispatcher.
     * It checks if the event should be handled and delegates to onEnteringPlace().
     */
    final public function handleEnter(EnterEvent $event): void
    {
        if (! $this->shouldHandle($event)) {
            return;
        }

        $this->onEnteringPlace($event);
    }

    /**
     * Called when subject is about to enter a place.
     *
     * Override this method to implement custom logic before entering a new place.
     * IMPORTANT: The marking has NOT been updated yet - the subject is still in the old place.
     *
     * @param  EnterEvent  $event  The enter event containing subject, transition, and marking
     */
    abstract protected function onEnteringPlace(EnterEvent $event): void;

    /**
     * Get the places being entered.
     */
    protected function getEnteringPlaces(EnterEvent $event): array
    {
        return $event->getTransition()->getTos();
    }

    /**
     * Get the first place being entered (for single-state workflows).
     */
    protected function getEnteringPlace(EnterEvent $event): string
    {
        $places = $this->getEnteringPlaces($event);

        return $places[0] ?? '';
    }

    /**
     * Get the current places (before entering new place).
     */
    protected function getCurrentPlaces(EnterEvent $event): array
    {
        return array_keys($event->getMarking()->getPlaces());
    }

    /**
     * Get the transition name.
     */
    protected function getTransitionName(EnterEvent $event): string
    {
        return $event->getTransition()->getName();
    }
}
