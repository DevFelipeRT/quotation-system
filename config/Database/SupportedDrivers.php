<?php

declare(strict_types=1);

namespace Config\Database;

use Config\Database\Exceptions\UnsupportedDriverException;

/**
 * SupportedDrivers
 *
 * Central authority for valid database driver identifiers.
 * Responsible for normalization, validation, and fallback logic.
 *
 * @package Config\Database
 */
final class SupportedDrivers
{
    /**
     * List of supported driver identifiers.
     *
     * @var string[]
     */
    private const SUPPORTED = ['mysql', 'pgsql', 'sqlite'];

    /**
     * Resolves and validates the driver to be used.
     *
     * Applies normalization and fallback logic, then ensures validity.
     *
     * @param string|null $driver Raw input from config or env.
     * @return string Normalized and validated driver identifier.
     *
     * @throws UnsupportedDriverException
     */
    public static function resolve(?string $driver): string
    {
        $normalized = self::normalize($driver);
        return $normalized !== '' ? $normalized : self::default();
    }

    /**
     * Ensures the provided driver is in the supported list.
     *
     * @param string $driver Normalized driver string.
     * @return void
     * @throws UnsupportedDriverException
     */
    public static function assertIsSupported(string $driver): void
    {
        if (!in_array($driver, self::SUPPORTED, true)) {
            $available = implode(', ', self::SUPPORTED);
            throw new UnsupportedDriverException("Unsupported DB_DRIVER '{$driver}'. Supported: {$available}.");
        }
    }

    /**
     * Returns the list of valid driver identifiers.
     *
     * @return string[]
     */
    public static function list(): array
    {
        return self::SUPPORTED;
    }

    /**
     * Returns the default driver if none is provided.
     *
     * @return string
     */
    public static function default(): string
    {
        return self::SUPPORTED[0];
    }

    /**
     * Normalizes input for consistent comparison.
     *
     * @param string|null $driver
     * @return string
     */
    private static function normalize(?string $driver): string
    {
        return strtolower(trim((string) $driver));
    }
}
