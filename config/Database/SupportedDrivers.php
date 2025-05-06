<?php

namespace Config\Database;

use Config\Database\Exceptions\UnsupportedDriverException;

/**
 * SupportedDrivers
 *
 * Centralizes normalization, validation, and fallback resolution for
 * supported database driver strings.
 */
final class SupportedDrivers
{
    /**
     * List of supported database drivers.
     */
    private const SUPPORTED = ['mysql', 'pgsql', 'sqlite'];

    /**
     * Resolves the driver string to be used by the system.
     *
     * Applies normalization, fallback, and validation logic.
     *
     * @param string|null $driver Input driver string or null.
     * @return string Validated and resolved driver.
     *
     * @throws UnsupportedDriverException If the driver is not in the supported list.
     */
    public static function resolve(?string $driver): string
    {
        $resolved = self::resolveOrDefault($driver);
        self::assertIsSupported($resolved);
        return $resolved;
    }

    /**
     * Normalizes the given driver and returns it or falls back to the default if empty.
     * This method does not validate.
     *
     * @param string|null $driver
     * @return string
     */
    private static function resolveOrDefault(?string $driver): string
    {
        $normalized = self::normalize($driver);
        return $normalized !== '' ? $normalized : self::default();
    }

    /**
     * Normalizes a driver string by trimming and lowercasing it.
     *
     * @param string|null $driver
     * @return string
     */
    private static function normalize(?string $driver): string
    {
        return strtolower(trim((string) $driver));
    }

    /**
     * Ensures the provided driver is in the supported list.
     *
     * @param string $driver
     * @return void
     * @throws UnsupportedDriverException
     */
    private static function assertIsSupported(string $driver): void
    {
        if (!in_array($driver, self::SUPPORTED, true)) {
            $available = implode(', ', self::SUPPORTED);
            throw new UnsupportedDriverException("Unsupported DB_DRIVER '{$driver}'. Supported: {$available}.");
        }
    }

    /**
     * Returns the default driver.
     *
     * @return string
     */
    private static function default(): string
    {
        return self::SUPPORTED[0];
    }

    /**
     * Returns the list of supported drivers.
     *
     * @return string[]
     */
    public static function list(): array
    {
        return self::SUPPORTED;
    }
}
