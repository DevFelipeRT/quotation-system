<?php

namespace App\Infrastructure\Logging;

/**
 * Enumerates standard logging severity levels.
 */
enum LogLevelEnum: string
{
    case DEBUG     = 'debug';
    case INFO      = 'info';
    case WARNING   = 'warning';
    case ERROR     = 'error';
    case CRITICAL  = 'critical';

    /**
     * Returns all log levels ordered by severity (from least to most severe).
     *
     * @return LogLevelEnum[]
     */
    public static function ordered(): array
    {
        return [
            self::DEBUG,
            self::INFO,
            self::WARNING,
            self::ERROR,
            self::CRITICAL,
        ];
    }
}
