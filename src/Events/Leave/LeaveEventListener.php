<?php

namespace CleaniqueCoders\Flowstone\Events\Leave;

use CleaniqueCoders\Flowstone\Events\WorkflowEventSubscriber;
use Symfony\Component\Workflow\Event\LeaveEvent;

/**
 * Leave Event Listener
 *
 * Leave events are fired when the subject is about to leave a place during a transition.
 * This event occurs after guard events but before the transition event.
 *
 * Use this listener to perform cleanup or logging before the subject leaves its current state.
 *
 * Event Order: guard → **leave** → transition → enter → entered → completed → announce
 *
 * @example
 * ```php
 * class DocumentLeaveListener extends LeaveEventListener
 * {
 *     protected ?string $workflowName = 'document_approval';
 *     protected ?string $placeName = 'draft';
 *
 *     protected function onLeavingPlace(LeaveEvent $event): void
 *     {
 *         $document = $event->getSubject();
 *
 *         // Log that document is leaving draft state
 *         Log::info('Document leaving draft', [
 *             'document_id' => $document->id,
 *             'transition' => $event->getTransition()->getName(),
 *         ]);
 *
 *         // Cleanup draft-specific data
 *         $document->clearDraftMetadata();
 *     }
 * }
 * ```
 */
abstract class LeaveEventListener extends WorkflowEventSubscriber
{
    /**
     * Subscribe to leave events only.
     */
    public static function getSubscribedEvents(): array
    {
        $instance = new static;

        return [
            $instance->buildEventName('workflow.leave') => 'handleLeave',
        ];
    }

    /**
     * Handle the leave event.
     *
     * This method is called automatically by Symfony's event dispatcher.
     * It checks if the event should be handled and delegates to onLeavingPlace().
     */
    final public function handleLeave(LeaveEvent $event): void
    {
        if (! $this->shouldHandle($event)) {
            return;
        }

        $this->onLeavingPlace($event);
    }

    /**
     * Called when subject is leaving a place.
     *
     * Override this method to implement custom logic when the subject leaves a place.
     * The marking has not been updated yet at this point.
     *
     * @param  LeaveEvent  $event  The leave event containing subject, transition, and current marking
     */
    abstract protected function onLeavingPlace(LeaveEvent $event): void;

    /**
     * Get the places being left.
     */
    protected function getLeavingPlaces(LeaveEvent $event): array
    {
        return array_keys($event->getMarking()->getPlaces());
    }

    /**
     * Get the first place being left (for single-state workflows).
     */
    protected function getLeavingPlace(LeaveEvent $event): string
    {
        $places = $this->getLeavingPlaces($event);

        return $places[0] ?? '';
    }

    /**
     * Get the destination places (where the subject is going).
     */
    protected function getDestinationPlaces(LeaveEvent $event): array
    {
        return $event->getTransition()->getTos();
    }

    /**
     * Get the transition name.
     */
    protected function getTransitionName(LeaveEvent $event): string
    {
        return $event->getTransition()->getName();
    }
}
