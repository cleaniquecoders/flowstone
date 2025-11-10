<?php

namespace CleaniqueCoders\Flowstone\Events\Transition;

use CleaniqueCoders\Flowstone\Events\WorkflowEventSubscriber;
use Symfony\Component\Workflow\Event\TransitionEvent;

/**
 * Transition Event Listener
 *
 * Transition events are fired when the subject is actively going through a transition.
 * This event occurs after leave events but before enter events.
 *
 * Use this listener to perform actions during the transition process, such as updating
 * related data, sending notifications, or recording activity.
 *
 * Event Order: guard → leave → **transition** → enter → entered → completed → announce
 *
 * @example
 * ```php
 * class OrderTransitionListener extends TransitionEventListener
 * {
 *     protected ?string $workflowName = 'order_fulfillment';
 *     protected ?string $transitionName = 'ship';
 *
 *     protected function onTransitioning(TransitionEvent $event): void
 *     {
 *         $order = $event->getSubject();
 *
 *         // Generate shipping label during the transition
 *         $order->shipping_label = ShippingService::generateLabel($order);
 *         $order->shipped_at = now();
 *         $order->save();
 *
 *         // Send notification
 *         Notification::send(
 *             $order->customer,
 *             new OrderShippedNotification($order)
 *         );
 *     }
 * }
 * ```
 */
abstract class TransitionEventListener extends WorkflowEventSubscriber
{
    /**
     * Subscribe to transition events only.
     */
    public static function getSubscribedEvents(): array
    {
        $instance = new static;

        return [
            $instance->buildEventName('workflow.transition') => 'handleTransition',
        ];
    }

    /**
     * Handle the transition event.
     *
     * This method is called automatically by Symfony's event dispatcher.
     * It checks if the event should be handled and delegates to onTransitioning().
     */
    final public function handleTransition(TransitionEvent $event): void
    {
        if (! $this->shouldHandle($event)) {
            return;
        }

        $this->onTransitioning($event);
    }

    /**
     * Called when subject is going through a transition.
     *
     * Override this method to implement custom logic during the transition.
     * At this point, the subject has left the old place but hasn't entered the new place yet.
     *
     * @param  TransitionEvent  $event  The transition event containing subject, transition, and marking
     */
    abstract protected function onTransitioning(TransitionEvent $event): void;

    /**
     * Get the transition name.
     */
    protected function getTransitionName(TransitionEvent $event): string
    {
        return $event->getTransition()->getName();
    }

    /**
     * Get the source places (where the subject came from).
     */
    protected function getFromPlaces(TransitionEvent $event): array
    {
        return $event->getTransition()->getFroms();
    }

    /**
     * Get the destination places (where the subject is going).
     */
    protected function getToPlaces(TransitionEvent $event): array
    {
        return $event->getTransition()->getTos();
    }

    /**
     * Get the current marking places.
     */
    protected function getCurrentPlaces(TransitionEvent $event): array
    {
        return array_keys($event->getMarking()->getPlaces());
    }
}
