<?php

namespace CleaniqueCoders\Flowstone\Guards;

/**
 * A blocker represents a reason why a transition cannot be applied.
 *
 * This class is inspired by Symfony Workflow's TransitionBlocker.
 */
class TransitionBlocker
{
    /**
     * The transition is blocked because it's not enabled.
     */
    public const BLOCKED_BY_MARKING = 'BLOCKED_BY_MARKING';

    /**
     * The transition is blocked by a guard expression.
     */
    public const BLOCKED_BY_EXPRESSION_GUARD = 'BLOCKED_BY_EXPRESSION_GUARD';

    /**
     * The transition is blocked because the user lacks required permissions.
     */
    public const BLOCKED_BY_PERMISSION = 'BLOCKED_BY_PERMISSION';

    /**
     * The transition is blocked because the user lacks required roles.
     */
    public const BLOCKED_BY_ROLE = 'BLOCKED_BY_ROLE';

    /**
     * The transition is blocked by a custom guard condition.
     */
    public const BLOCKED_BY_CUSTOM_GUARD = 'BLOCKED_BY_CUSTOM_GUARD';

    /**
     * The transition is blocked due to unknown reasons.
     */
    public const UNKNOWN = 'UNKNOWN';

    public function __construct(
        protected string $message,
        protected string $code,
        protected array $parameters = []
    ) {}

    /**
     * Get the blocker message.
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Get the blocker code.
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * Get the blocker parameters.
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Create a blocker for marking issues.
     */
    public static function createBlockedByMarking(string $message = 'The transition is not enabled.'): self
    {
        return new self($message, self::BLOCKED_BY_MARKING);
    }

    /**
     * Create a blocker for expression guard failures.
     */
    public static function createBlockedByExpressionGuard(string $expression): self
    {
        return new self(
            "The expression \"{$expression}\" returned false.",
            self::BLOCKED_BY_EXPRESSION_GUARD,
            ['expression' => $expression]
        );
    }

    /**
     * Create a blocker for permission issues.
     */
    public static function createBlockedByPermission(string|array $permission): self
    {
        $permissionList = is_array($permission) ? implode(', ', $permission) : $permission;
        $message = is_array($permission)
            ? "You need one of these permissions: {$permissionList}."
            : "You do not have the required permission: {$permission}.";

        return new self(
            $message,
            self::BLOCKED_BY_PERMISSION,
            ['permission' => $permission]
        );
    }

    /**
     * Create a blocker for role issues.
     */
    public static function createBlockedByRole(array $roles): self
    {
        $rolesList = implode(', ', $roles);

        return new self(
            "You need one of these roles: {$rolesList}.",
            self::BLOCKED_BY_ROLE,
            ['roles' => $roles]
        );
    }

    /**
     * Create a blocker for custom guard failures.
     */
    public static function createBlockedByCustomGuard(string $message): self
    {
        return new self($message, self::BLOCKED_BY_CUSTOM_GUARD);
    }

    /**
     * Create an unknown blocker.
     */
    public static function createUnknown(string $message = 'Unknown reason.'): self
    {
        return new self($message, self::UNKNOWN);
    }

    /**
     * Convert blocker to array.
     */
    public function toArray(): array
    {
        return [
            'message' => $this->message,
            'code' => $this->code,
            'parameters' => $this->parameters,
        ];
    }

    /**
     * Convert blocker to string.
     */
    public function __toString(): string
    {
        return $this->message;
    }
}
