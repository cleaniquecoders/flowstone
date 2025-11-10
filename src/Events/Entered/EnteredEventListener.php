<?php

namespace CleaniqueCoders\Flowstone\Events\Entered;

use CleaniqueCoders\Flowstone\Events\WorkflowEventSubscriber;
use Symfony\Component\Workflow\Event\EnteredEvent;

/**
 * Entered Event Listener
 *
 * Entered events are fired when the subject has entered a new place and the marking
 * has been updated. This is the ideal place to perform actions that depend on the
 * subject being in the new state.
 *
 * Use this listener for actions that should happen after the state change is complete,
 * such as sending notifications, updating related records, or triggering side effects.
 *
 * Event Order: guard → leave → transition → enter → **entered** → completed → announce
 *
 * @example
 * ```php
 * class OrderEnteredListener extends EnteredEventListener
 * {
 *     protected ?string $workflowName = 'order_fulfillment';
 *     protected ?string $placeName = 'shipped';
 *
 *     protected function onEnteredPlace(EnteredEvent $event): void
 *     {
 *         $order = $event->getSubject();
 *
 *         // Marking is updated - order is now in "shipped" state
 *         // Send customer notification
 *         Mail::to($order->customer)
 *             ->send(new OrderShippedMail($order));
 *
 *         // Update inventory
 *         InventoryService::markAsShipped($order);
 *
 *         // Create tracking entry
 *         TrackingLog::create([
 *             'order_id' => $order->id,
 *             'status' => 'shipped',
 *             'timestamp' => now(),
 *         ]);
 *     }
 * }
 * ```
 */
abstract class EnteredEventListener extends WorkflowEventSubscriber
{
    /**
     * Subscribe to entered events only.
     */
    public static function getSubscribedEvents(): array
    {
        $instance = new static;

        return [
            $instance->buildEventName('workflow.entered') => 'handleEntered',
        ];
    }

    /**
     * Handle the entered event.
     *
     * This method is called automatically by Symfony's event dispatcher.
     * It checks if the event should be handled and delegates to onEnteredPlace().
     */
    final public function handleEntered(EnteredEvent $event): void
    {
        if (! $this->shouldHandle($event)) {
            return;
        }

        $this->onEnteredPlace($event);
    }

    /**
     * Called when subject has entered a place.
     *
     * Override this method to implement custom logic after entering a new place.
     * IMPORTANT: The marking HAS been updated - the subject is now in the new place.
     *
     * @param  EnteredEvent  $event  The entered event containing subject, transition, and updated marking
     */
    abstract protected function onEnteredPlace(EnteredEvent $event): void;

    /**
     * Get the places that were entered.
     */
    protected function getEnteredPlaces(EnteredEvent $event): array
    {
        return $event->getTransition()->getTos();
    }

    /**
     * Get the first place that was entered (for single-state workflows).
     */
    protected function getEnteredPlace(EnteredEvent $event): string
    {
        $places = $this->getEnteredPlaces($event);

        return $places[0] ?? '';
    }

    /**
     * Get the current places (after entering new place).
     */
    protected function getCurrentPlaces(EnteredEvent $event): array
    {
        return array_keys($event->getMarking()->getPlaces());
    }

    /**
     * Get the transition name.
     */
    protected function getTransitionName(EnteredEvent $event): string
    {
        return $event->getTransition()->getName();
    }

    /**
     * Get the source places (where the subject came from).
     */
    protected function getFromPlaces(EnteredEvent $event): array
    {
        return $event->getTransition()->getFroms();
    }
}
