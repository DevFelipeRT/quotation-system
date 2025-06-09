<?php

declare(strict_types=1);

namespace ClassDiscovery\Domain\ValueObjects;

final class FullyQualifiedClassName
{
    private string $value;

    /**
     * @param string $fqcn
     * @throws \InvalidArgumentException If the FQCN is empty or not resolvable as class or interface.
     */
    public function __construct(string $fqcn)
    {
        $this->value = $this->validateFqnc($fqcn);
    }

    /**
     * Returns the FQCN as string.
     */
    public function value(): string
    {
        return $this->value;
    }

    private function validateFqnc(string $fqcn): string
    {
        $trimmed = trim($fqcn);
        if ($trimmed === '') {
            throw new \InvalidArgumentException('Fully qualified class name cannot be empty.');
        }
        if (!class_exists($trimmed) && !interface_exists($trimmed)) {
            throw new \InvalidArgumentException("FQCN '{$fqcn}' does not reference a valid class or interface.");
        }
        return $trimmed;
    }
}
