<?php

declare(strict_types=1);

namespace App\Shared\Discovery\Domain\ValueObjects;

final class InterfaceName
{
    private string $value;

    /**
     * @param string $interfaceName
     * @throws \InvalidArgumentException If the name does not refer to a declared interface.
     */
    public function __construct(string $interfaceName)
    {
        $this->value = $this->validate($interfaceName);
    }

    /**
     * Returns the fully qualified name of the interface.
     */
    public function value(): string
    {
        return $this->value;
    }

    /**
     * Comparison for value objects (semantic equality).
     */
    public function equals(InterfaceName $other): bool
    {
        return $this->value === $other->value();
    }

    /**
     * Returns the validated name of the interface.
     */
    private function validate(string $interfaceName): string
    {
        if (trim($interfaceName) === '') {
            throw new \InvalidArgumentException('Interface name cannot be empty.');
        }
        if (!interface_exists($interfaceName)) {
            throw new \InvalidArgumentException("Given name '{$interfaceName}' is not a valid interface.");
        }
        return $interfaceName;
    }
}
