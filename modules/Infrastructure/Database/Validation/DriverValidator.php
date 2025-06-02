<?php

declare(strict_types=1);

namespace Database\Validation;

use Database\Exceptions\MissingDriverConfigurationException;
use Database\Exceptions\UnsupportedDriverException;
use Config\Database\SupportedDrivers;

/**
 * Validates and resolves supported database driver identifiers.
 *
 * This validator normalizes input, applies fallback logic, and verifies support
 * based on the list defined in configuration. It does not perform any connection logic.
 */
final class DriverValidator
{
    /**
     * Returns the list of supported drivers defined in config.
     *
     * @return string[]
     */
    public static function list(): array
    {
        return SupportedDrivers::LIST;
    }

    /**
     * Normalizes and resolves the database driver.
     *
     * @param string|null $driver Input driver string (possibly from env or config)
     * @return string Validated and normalized driver
     * @throws MissingDriverConfigurationException
     */
    public static function resolve(?string $driver): string
    {
        $normalized = strtolower(trim((string) $driver));

        if ($normalized === '') {
            $default = self::list()[0] ?? null;
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
    public static function assertIsSupported(string $driver): void
    {
        if (!in_array($driver, self::list(), true)) {
            throw new UnsupportedDriverException($driver);
        }
    }
}
