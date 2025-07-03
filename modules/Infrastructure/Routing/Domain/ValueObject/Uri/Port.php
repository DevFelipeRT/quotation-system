<?php

declare(strict_types=1);

namespace Routing\Domain\ValueObject\Uri;

/**
 * Represents the port component of a URI as an immutable value object.
 *
 * This object ensures that a given port number is within the valid TCP/IP
 * range (1-65535) or represents the absence of a specific port.
 */
final class Port
{
    private const MIN_PORT = 1;
    private const MAX_PORT = 65535;

    private readonly ?int $value;

    /**
     * @param int|null $value The port number, or null if no port is specified.
     * @throws \InvalidArgumentException If the port number is outside the valid range.
     */
    public function __construct(?int $value)
    {
        if ($value !== null) {
            $this->ensureIsInRange($value);
        }

        $this->value = $value;
    }

    /**
     * Retrieves the port number.
     *
     * @return int|null The port number or null if not set.
     */
    public function getValue(): ?int
    {
        return $this->value;
    }

    /**
     * Checks if a port value has been set.
     *
     * @return bool
     */
    public function isSet(): bool
    {
        return $this->value !== null;
    }

    /**
     * Checks if this port is the standard port for a given scheme.
     *
     * @param Scheme $scheme The scheme to check against.
     * @return bool
     */
    public function isStandardForScheme(Scheme $scheme): bool
    {
        if (!$this->isSet()) {
            return true; // An unset port is considered "standard".
        }

        $standardPorts = [
            'http' => 80,
            'https' => 443,
            'ftp' => 21,
        ];

        return isset($standardPorts[$scheme->getValue()])
            && $this->value === $standardPorts[$scheme->getValue()];
    }

    /**
     * Checks if two Port objects are equal.
     *
     * @param self $other
     * @return bool
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * @param int $value
     * @return void
     * @throws \InvalidArgumentException
     */
    private function ensureIsInRange(int $value): void
    {
        if ($value < self::MIN_PORT || $value > self::MAX_PORT) {
            throw new \InvalidArgumentException(
                sprintf('Invalid port number. Must be between %d and %d.', self::MIN_PORT, self::MAX_PORT)
            );
        }
    }
}