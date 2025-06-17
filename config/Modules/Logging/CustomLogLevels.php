<?php

declare(strict_types=1);

namespace Config\Modules\Logging;

/**
 * CustomLogLevels
 *
 * Provides a centralized, static list of custom log levels accepted by the domain.
 * Extend or modify as needed for your application's specific logging strategy.
 */
final class CustomLogLevels
{
    /**
     * Returns a list of custom log levels supported by the logging domain.
     *
     * @return string[]
     */
    public static function list(): array
    {
        return [
            'debug',
            'info',
            'notice',
            'warning',
            'error',
            'critical',
            'alert',
            'emergency',
            // 'audit',
        ];
    }

    private function __construct()
    {
    }
}
