<?php

declare(strict_types=1);

namespace Config\Database;

/**
 * Immutable list of database driver identifiers supported by the system.
 *
 * This configuration acts as the source of truth for all driver validation logic.
 */
final class SupportedDrivers
{
    /**
     * @var string[]
     */
    public const LIST = [
        'mysql',
        'pgsql',
        'sqlite',
    ];
}
