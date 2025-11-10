<?php

namespace CleaniqueCoders\Flowstone\Events\Announce;

use CleaniqueCoders\Flowstone\Events\WorkflowEventSubscriber;
use Symfony\Component\Workflow\Event\AnnounceEvent;

/**
 * Announce Event Listener
 *
 * Announce events are fired for each transition that becomes accessible after a
 * transition completes. This event is triggered AFTER the completed event.
 *
 * The announce event tests for all available transitions, which triggers guard
 * events again for each transition. This can impact performance if guard events
 * include intensive operations.
 *
 * Use this listener to notify users about newly available actions or to update UI
 * elements showing available transitions.
 *
 * Event Order: guard → leave → transition → enter → entered → completed → **announce**
 *
 * Performance Note: You can disable announce events for specific transitions using:
 * `$workflow->apply($subject, $transition, [Workflow::DISABLE_ANNOUNCE_EVENT => true])`
 *
 * @example
 * ```php
 * class AvailableActionsNotifier extends AnnounceEventListener
 * {
 *     protected ?string $workflowName = 'task_management';
 *
 *     protected function onTransitionAnnounced(AnnounceEvent $event): void
 *     {
 *         $task = $event->getSubject();
 *         $availableTransition = $event->getTransition()->getName();
 *
 *         // Notify assigned user about available actions
 *         if ($task->assigned_to) {
 *             Notification::send(
 *                 $task->assignedUser,
 *                 new AvailableActionNotification($task, $availableTransition)
 *             );
 *         }
 *
 *         // Update dashboard cache
 *         Cache::tags(['tasks', "task:{$task->id}"])
 *             ->put("available_transitions:{$task->id}", $this->getAllAvailableTransitions($task));
 *     }
 *
 *     private function getAllAvailableTransitions($task): array
 *     {
 *         return $task->getEnabledTransitions();
 *     }
 * }
 * ```
 */
abstract class AnnounceEventListener extends WorkflowEventSubscriber
{
    /**
     * Subscribe to announce events only.
     */
    public static function getSubscribedEvents(): array
    {
        $instance = new static;

        return [
            $instance->buildEventName('workflow.announce') => 'handleAnnounce',
        ];
    }

    /**
     * Handle the announce event.
     *
     * This method is called automatically by Symfony's event dispatcher.
     * It checks if the event should be handled and delegates to onTransitionAnnounced().
     */
    final public function handleAnnounce(AnnounceEvent $event): void
    {
        if (! $this->shouldHandle($event)) {
            return;
        }

        $this->onTransitionAnnounced($event);
    }

    /**
     * Called when a transition is announced as available.
     *
     * Override this method to implement custom logic when transitions become available.
     * This event is fired for EACH available transition after a transition completes.
     *
     * WARNING: This can be called multiple times per workflow transition if multiple
     * transitions become available. Guard events will be fired for each announced transition.
     *
     * @param  AnnounceEvent  $event  The announce event containing subject, transition, and marking
     */
    abstract protected function onTransitionAnnounced(AnnounceEvent $event): void;

    /**
     * Get the announced transition name.
     */
    protected function getAnnouncedTransition(AnnounceEvent $event): string
    {
        return $event->getTransition()->getName();
    }

    /**
     * Get the destination places for this announced transition.
     */
    protected function getDestinationPlaces(AnnounceEvent $event): array
    {
        return $event->getTransition()->getTos();
    }

    /**
     * Get the current places.
     */
    protected function getCurrentPlaces(AnnounceEvent $event): array
    {
        return array_keys($event->getMarking()->getPlaces());
    }

    /**
     * Check if this is the initial marking announcement.
     *
     * When a workflow is first initialized, announce events are fired for all
     * available transitions from the initial place.
     */
    protected function isInitialAnnouncement(AnnounceEvent $event): bool
    {
        $context = $this->getContext($event);

        return isset($context['initial_marking']) && $context['initial_marking'] === true;
    }
}
