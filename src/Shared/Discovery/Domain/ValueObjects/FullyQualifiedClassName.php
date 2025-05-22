<?php

declare(strict_types=1);

namespace App\Shared\Discovery\Domain\ValueObjects;

final class FullyQualifiedClassName
{
    private string $value;

    /**
     * @param string $fqcn
     * @throws \InvalidArgumentException If the FQCN is empty or not resolvable as class or interface.
     */
    public function __construct(string $fqcn)
    {
        $trimmed = trim($fqcn);
        if ($trimmed === '') {
            throw new \InvalidArgumentException('Fully qualified class name cannot be empty.');
        }
        if (!class_exists($trimmed) && !interface_exists($trimmed)) {
            throw new \InvalidArgumentException("FQCN '{$fqcn}' does not reference a valid class or interface.");
        }
        $this->value = $trimmed;
    }

    /**
     * Returns the FQCN as string.
     */
    public function value(): string
    {
        return $this->value;
    }

    /**
     * Semantic equality for value objects.
     */
    public function equals(FullyQualifiedClassName $other): bool
    {
        return $this->value === $other->value();
    }
}
