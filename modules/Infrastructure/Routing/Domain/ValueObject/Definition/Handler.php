<?php

declare(strict_types=1);

namespace Routing\Domain\ValueObject\Definition;

/**
 * Represents the handler for a route definition as a typesafe value object.
 *
 * This VO encapsulates the pointer to the code that should be executed for a
 * route, such as a Closure, a controller-action array, or an invokable
 * class name. It ensures the handler's format is valid upon creation.
 */
final class Handler
{
    private readonly mixed $value;

    /**
     * @param mixed $value The handler to be encapsulated.
     * @throws \InvalidArgumentException if the handler type is invalid.
     */
    public function __construct(mixed $value)
    {
        $this->ensureIsValid($value);
        $this->value = $value;
    }

    /**
     * Retrieves the raw handler value.
     *
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * Checks if two Handler objects are equal.
     *
     * Note: Strict equality for Closures is not possible. This implementation
     * compares them by object identity if both values are objects.
     *
     * @param self $other
     * @return bool
     */
    public function equals(self $other): bool
    {
        if (is_object($this->value) && is_object($other->value)) {
            return $this->value === $other->value;
        }

        return $this->value === $other->value;
    }

    /**
     * Validates the supported handler formats.
     *
     * @param mixed $value
     * @return void
     * @throws \InvalidArgumentException
     */
    private function ensureIsValid(mixed $value): void
    {
        // Case 1: A Closure
        if ($value instanceof \Closure) {
            return;
        }

        // Case 2: An invokable class name string
        if (is_string($value) && class_exists($value) && method_exists($value, '__invoke')) {
            return;
        }

        // Case 3: A [controller, method] array
        if (is_array($value) && count($value) === 2 && isset($value[0], $value[1]) && is_string($value[0]) && is_string($value[1])) {
            return;
        }

        throw new \InvalidArgumentException(
            'Invalid handler type. Must be a Closure, an invokable class name, or a [class, method] array.'
        );
    }
}