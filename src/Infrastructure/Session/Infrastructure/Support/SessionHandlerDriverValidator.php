<?php

declare(strict_types=1);

namespace App\Infrastructure\Session\Infrastructure\Support;

use App\Infrastructure\Session\Exceptions\UnsupportedSessionDriverException;
use Config\Session\SupportedSessionDrivers;

/**
 * SessionHandlerDriverValidator
 *
 * Centralizes the validation logic for supported session drivers.
 * This class is the only point responsible for determining if a driver is supported.
 */
final class SessionHandlerDriverValidator
{
    /**
     * Throws if the provided driver is not supported.
     *
     * @param string $driver The session driver key to check (e.g. 'native', 'redis')
     *
     * @throws UnsupportedSessionDriverException If the driver is not supported.
     */
    public static function ensureSupported(string $driver): void
    {
        if (!in_array($driver, SupportedSessionDrivers::list(), true)) {
            throw new UnsupportedSessionDriverException(
                "The session driver '{$driver}' is not supported by this system."
            );
        }
    }

    /**
     * Returns true if the given driver is among the supported drivers.
     *
     * @param string $driver
     * @return bool
     */
    public static function isSupported(string $driver): bool
    {
        return in_array($driver, SupportedSessionDrivers::list(), true);
    }

    /**
     * Returns the list of all session drivers supported by the system.
     *
     * @return string[]
     */
    public static function supportedDrivers(): array
    {
        return SupportedSessionDrivers::list();
    }
}
