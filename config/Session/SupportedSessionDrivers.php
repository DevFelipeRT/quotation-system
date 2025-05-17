<?php

declare(strict_types=1);

namespace Config\Session;

/**
 * SupportedSessionDrivers
 *
 * Provides the static list of supported session driver keys.
 * No logic, no validation — just data.
 */
final class SupportedSessionDrivers
{
    /**
     * Returns the list of supported driver keys.
     *
     * @return string[]
     */
    public static function list(): array
    {
        return [
            'native',
            // 'redis',
            // 'array',
        ];
    }
}
