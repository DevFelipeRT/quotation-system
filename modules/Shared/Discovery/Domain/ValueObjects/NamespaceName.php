<?php

declare(strict_types=1);

namespace App\Shared\Discovery\Domain\ValueObjects;

final class NamespaceName
{
    private string $value;

    /**
     * @param string $namespace
     * @throws \InvalidArgumentException If the namespace is empty or not valid.
     */
    public function __construct(string $namespace)
    {
        $trimmed = trim($namespace);
        if ($trimmed === '') {
            throw new \InvalidArgumentException('Namespace cannot be empty.');
        }
        // Opcional: Adicione aqui validação de formato PSR-4, se desejar ser mais estrito.
        $this->value = $trimmed;
    }

    /**
     * Returns the fully qualified namespace as string.
     */
    public function value(): string
    {
        return $this->value;
    }

    /**
     * Semantic equality for value objects.
     */
    public function equals(NamespaceName $other): bool
    {
        return $this->value === $other->value();
    }
}
