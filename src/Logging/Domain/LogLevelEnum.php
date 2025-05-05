<?php

namespace App\Logging\Domain;

/**
 * Enumerates standard logging severity levels.
 *
 * These levels are used to classify log entries by importance,
 * allowing filtering and prioritization of events.
 */
enum LogLevelEnum: string
{
    case DEBUG     = 'debug';
    case INFO      = 'info';
    case WARNING   = 'warning';
    case ERROR     = 'error';
    case CRITICAL  = 'critical';

    /**
     * Returns all log levels ordered by severity,
     * from least to most critical.
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
