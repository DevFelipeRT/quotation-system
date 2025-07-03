<?php

declare(strict_types=1);

namespace Routing\Domain\ValueObject\Definition;

/**
 * Represents a generic command verb for a route definition (e.g., 'GET', 'POST', 'CLI').
 *
 * This simple, immutable value object encapsulates the verb as a string,
 * keeping the routing domain protocol-agnostic.
 */
final class Verb
{
    private readonly string $value;

    /**
     * @param string $value The verb string.
     * @throws \InvalidArgumentException If the verb is empty.
     */
    public function __construct(string $value)
    {
        $trimmedValue = trim(strtoupper($value));
        if ($trimmedValue === '') {
            throw new \InvalidArgumentException('Verb cannot be empty.');
        }
        $this->value = $trimmedValue;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}