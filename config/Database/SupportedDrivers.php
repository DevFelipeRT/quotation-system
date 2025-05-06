<?php

namespace Config\Database;

use Config\Database\Exceptions\UnsupportedDriverException;
use Config\Database\Exceptions\MissingDriverConfigurationException;

/**
 * SupportedDrivers
 *
 * Provides validation and resolution logic for supported database drivers.
 *
 * @package Config\Database
 */
final class SupportedDrivers
{
    /**
     * List of supported database drivers.
     */
    private const SUPPORTED = ['mysql', 'pgsql', 'sqlite'];

    /**
     * Determines whether a given driver is supported.
     *
     * @param string $driver
     * @return bool
     */
    private static function isSupported(string $driver): bool
    {
        return in_array($driver, self::SUPPORTED, true);
    }

    /**
     * Returns the default driver used if none is provided.
     *
     * @return string
     */
    public static function getDefault(): string
    {
        return self::SUPPORTED[0];
    }

    /**
     * Lists all supported drivers.
     *
     * @return string[]
     */
    public static function list(): array
    {
        return self::SUPPORTED;
    }

    /**
     * Resolves and validates a given driver string.
     *
     * @param string|null $driver
     * @return string
     * @throws MissingDriverConfigurationException if no driver was configured.
     * @throws UnsupportedDriverException if the provided driver is invalid.
     */
    public static function resolve(?string $driver): string
    {
        $normalized = strtolower(trim((string) $driver));

        if ($normalized === '' || $normalized === null) {
            throw new MissingDriverConfigurationException('DB_DRIVER is not configured.');
        }

        if (!self::isSupported($normalized)) {
            $available = implode(', ', self::list());
            throw new UnsupportedDriverException("Unsupported DB_DRIVER '{$normalized}'. Supported: {$available}.");
        }

        return $normalized;
    }
}
