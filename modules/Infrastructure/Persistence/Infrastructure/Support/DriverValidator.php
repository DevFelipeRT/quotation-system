<?php

declare(strict_types=1);

namespace Persistence\Infrastructure\Support;

use Persistence\Infrastructure\Exceptions\MissingDriverConfigurationException;
use Persistence\Infrastructure\Exceptions\UnsupportedDriverException;

/**
 * Validates and resolves supported database driver identifiers.
 *
 * This validator normalizes input, applies fallback logic, and verifies support
 * based on the list defined in configuration. It does not perform any connection logic.
 */
final class DriverValidator
{
    private readonly array $allowedDrivers;

    public function __construct(array $allowedDrivers) {
        $this->allowedDrivers = $allowedDrivers ?? [];
    }

    /**
     * Returns the list of supported drivers defined in config.
     *
     * @return string[]
     */
    public function list(): array
    {
        return array_values($this->allowedDrivers);
    }

    /**
     * Normalizes and resolves the database driver.
     *
     * @param string|null $driver Input driver string (possibly from env or config)
     * @return string Validated and normalized driver
     * @throws MissingDriverConfigurationException
     */
    public function resolve(?string $driver): string
    {
        $normalized = strtolower(trim((string) $driver));

        if ($normalized === '') {
            $default = $this->list()[0] ?? null;
            if ($default === null) {
                throw new MissingDriverConfigurationException();
            }
            return $default;
        }

        return $normalized;
    }

    /**
     * Validates that the given driver is supported.
     *
     * @param string $driver Normalized driver identifier
     * @return void
     * @throws UnsupportedDriverException
     */
    public function assertIsSupported(string $driver): void
    {
        if (!in_array($driver, $this->list(), true)) {
            throw new UnsupportedDriverException($driver);
        }
    }
}
