<?php

declare(strict_types=1);

namespace Persistence\Domain\Support;

/**
 * Provides strong safeguards against the accidental exposure
 * of sensitive credential data within Value Objects.
 *
 * Any class using this trait must define a method:
 *   array getSafeDebugInfo()
 *
 * This trait prevents:
 * - Exposure via string casting
 * - Exposure via var_dump(), print_r(), or reflection
 * - Exposure via serialize(), __sleep(), or __serialize()
 * - Accidental JSON serialization
 *
 * Intended for use in immutable Value Objects representing
 * sensitive credentials (e.g., DB passwords, API tokens).
 */
trait CredentialsSecurity
{
    /**
     * Prevents object from being cast to string.
     */
    public function __toString(): string
    {
        return '[' . static::class . ']';
    }

    /**
     * Prevents display of sensitive data in var_dump() / print_r().
     */
    public function __debugInfo(): array
    {
        if (method_exists($this, 'getSafeDebugInfo')) {
            return $this->getSafeDebugInfo();
        }

        return ['debug' => '[sensitive object]'];
    }

    /**
     * Prevents serialization using the modern __serialize() interface.
     */
    public function __serialize(): array
    {
        throw new \LogicException(
            'Serialization of sensitive credentials is strictly prohibited.'
        );
    }

    /**
     * Prevents unserialization via the modern interface.
     */
    public function __unserialize(array $data): void
    {
        throw new \LogicException(
            'Unserialization of sensitive credentials is strictly prohibited.'
        );
    }

    /**
     * Prevents legacy serialization via __sleep().
     */
    public function __sleep(): array
    {
        throw new \LogicException(
            'Legacy serialization of sensitive credentials is not allowed.'
        );
    }

    /**
     * Prevents legacy unserialization via __wakeup().
     */
    public function __wakeup(): void
    {
        throw new \LogicException(
            'Legacy unserialization of sensitive credentials is not allowed.'
        );
    }

    /**
     * Prevents accidental conversion to JSON (e.g., json_encode()).
     */
    public function jsonSerialize(): mixed
    {
        throw new \LogicException(
            'JSON serialization of sensitive credentials is not allowed.'
        );
    }
}
