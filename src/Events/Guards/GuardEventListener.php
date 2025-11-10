<?php

namespace CleaniqueCoders\Flowstone\Events\Guards;

use CleaniqueCoders\Flowstone\Events\WorkflowEventSubscriber;
use Symfony\Component\Workflow\Event\GuardEvent;

/**
 * Guard Event Listener
 *
 * Guard events are fired every time `Workflow::can()`, `Workflow::apply()`, or
 * `Workflow::getEnabledTransitions()` is executed. Guard events allow you to add
 * custom logic to decide which transitions should be blocked or allowed.
 *
 * This listener provides a convenient way to handle guard events without having to
 * manually subscribe to all guard event variations.
 *
 * @example
 * ```php
 * class BlogPostGuardListener extends GuardEventListener
 * {
 *     protected ?string $workflowName = 'blog_publishing';
 *     protected ?string $transitionName = 'publish';
 *
 *     public function guardTransition(GuardEvent $event): void
 *     {
 *         $post = $event->getSubject();
 *
 *         if (empty($post->title)) {
 *             $event->setBlocked(true, 'Cannot publish post without a title');
 *         }
 *
 *         if (!$post->isReviewed()) {
 *             $event->addTransitionBlocker(
 *                 new TransitionBlocker('Post must be reviewed before publishing', 'not_reviewed')
 *             );
 *         }
 *     }
 * }
 * ```
 */
abstract class GuardEventListener extends WorkflowEventSubscriber
{
    /**
     * Subscribe to guard events only.
     */
    public static function getSubscribedEvents(): array
    {
        $instance = new static;

        return [
            $instance->buildEventName('workflow.guard') => 'handleGuard',
        ];
    }

    /**
     * Handle the guard event.
     *
     * This method is called automatically by Symfony's event dispatcher.
     * It checks if the event should be handled and delegates to guardTransition().
     */
    final public function handleGuard(GuardEvent $event): void
    {
        if (! $this->shouldHandle($event)) {
            return;
        }

        $this->guardTransition($event);
    }

    /**
     * Guard a transition.
     *
     * Override this method to implement custom guard logic. You can:
     * - Block transitions with `$event->setBlocked(true, 'reason')`
     * - Add transition blockers with `$event->addTransitionBlocker(...)`
     * - Check if transition is already blocked with `$event->isBlocked()`
     *
     * @param  GuardEvent  $event  The guard event containing subject, transition, and marking
     */
    abstract protected function guardTransition(GuardEvent $event): void;

    /**
     * Check if the transition is blocked.
     */
    protected function isBlocked(GuardEvent $event): bool
    {
        return $event->isBlocked();
    }

    /**
     * Block the transition with a message.
     */
    protected function block(GuardEvent $event, string $message): void
    {
        $event->setBlocked(true, $message);
    }

    /**
     * Add a transition blocker to the event.
     */
    protected function addBlocker(GuardEvent $event, \Symfony\Component\Workflow\TransitionBlocker $blocker): void
    {
        $event->addTransitionBlocker($blocker);
    }

    /**
     * Get all transition blockers from the event.
     */
    protected function getBlockers(GuardEvent $event): \Symfony\Component\Workflow\TransitionBlockerList
    {
        return $event->getTransitionBlockerList();
    }

    /**
     * Check if user has a specific role (requires authentication).
     */
    protected function hasRole(string $role): bool
    {
        if (! function_exists('auth') || ! auth()->check()) {
            return false;
        }

        $user = auth()->user();

        // Check if user has hasRole method (Spatie Permission)
        if (method_exists($user, 'hasRole')) {
            return $user->hasRole($role);
        }

        return false;
    }

    /**
     * Check if user has a specific permission (requires authentication).
     */
    protected function hasPermission(string $permission): bool
    {
        if (! function_exists('auth') || ! auth()->check()) {
            return false;
        }

        $user = auth()->user();

        // Check if user has can method (Laravel Authorization or Spatie Permission)
        if (method_exists($user, 'can')) {
            return $user->can($permission);
        }

        return false;
    }

    /**
     * Check if user is authenticated.
     */
    protected function isAuthenticated(): bool
    {
        return function_exists('auth') && auth()->check();
    }

    /**
     * Get the authenticated user (or null).
     */
    protected function getUser(): ?object
    {
        return function_exists('auth') ? auth()->user() : null;
    }
}
