<?php

declare(strict_types=1);

namespace ClassDiscovery\Domain\ValueObjects;

final class NamespaceName
{
    private string $value;

    /**
     * @param string $namespace
     * @throws \InvalidArgumentException If the namespace is empty or not valid.
     */
    public function __construct(string $namespace)
    {
        $this->value = $this->validateNamespace($namespace);
    }

    /**
     * Returns the fully qualified namespace as string.
     */
    public function value(): string
    {
        return $this->value;
    }

    private function validateNamespace(string $namespace): string
    {
        $trimmed = trim($namespace);
        if ($trimmed === '') {
            throw new \InvalidArgumentException('Namespace cannot be empty.');
        }
        
        return $trimmed;
    }
}
