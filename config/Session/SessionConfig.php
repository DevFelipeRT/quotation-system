<?php

namespace App\Infrastructure\Session\Infrastructure\Support;

use InvalidArgumentException;

/**
 * SessionConfig
 *
 * Provides centralized access to session-specific configuration values,
 * as defined in `config/Session/SessionConfig.php`.
 *
 * This class is a static utility and should be treated as read-only.
 *
 * The configuration file must return an associative array with the following structure:
 *
 * ```php
 * return [
 *     'default_driver' => 'native',
 *     'drivers' => [
 *         'native' => [
 *             // Driver-specific options (if any)
 *         ],
 *     ],
 * ];
 * ```
 */
final class SessionConfig
{
    private const CONFIG_PATH = __DIR__ . '/../../../../../config/Session/SessionConfig.php';

    /**
     * Returns the name of the default session driver (e.g., 'native', 'redis', etc.).
     *
     * @return string The default driver key defined in the configuration file.
     *
     * @throws InvalidArgumentException If the key 'default_driver' is missing.
     */
    public static function defaultDriver(): string
    {
        return self::get('default_driver');
    }

    /**
     * Returns the configuration array for the specified driver.
     *
     * @param string $driver The driver key (e.g., 'native', 'redis').
     * @return array<string, mixed> Configuration options for the given driver.
     *
     * @throws InvalidArgumentException If the 'drivers' key or specific driver is not defined.
     */
    public static function driverOptions(string $driver): array
    {
        $drivers = self::get('drivers');

        if (!array_key_exists($driver, $drivers)) {
            throw new InvalidArgumentException("Driver '{$driver}' is not configured.");
        }

        return $drivers[$driver];
    }

    /**
     * Loads and returns the full session configuration array.
     *
     * @return array<string, mixed>
     *
     * @throws InvalidArgumentException If the configuration file is missing or invalid.
     */
    private static function load(): array
    {
        /** @var array $config */
        $config = require self::CONFIG_PATH;

        if (!is_array($config)) {
            throw new InvalidArgumentException("Invalid session configuration: expected array.");
        }

        return $config;
    }

    /**
     * Retrieves a top-level configuration value by key.
     *
     * @param string $key The key to retrieve from the session configuration.
     * @return mixed The configuration value associated with the key.
     *
     * @throws InvalidArgumentException If the key is not present.
     */
    private static function get(string $key): mixed
    {
        $config = self::load();

        if (!array_key_exists($key, $config)) {
            throw new InvalidArgumentException("Missing session config key: '{$key}'.");
        }

        return $config[$key];
    }
}
