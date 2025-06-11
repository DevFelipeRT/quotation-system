<?php

declare(strict_types=1);

namespace Logging\Domain;

use Logging\Exceptions\InvalidLogLevelException;

/**
 * Enumerates standard logging severity levels, aligned with PSR-3.
 *
 * These levels classify log entries by importance,
 * enabling structured filtering and prioritization.
 */
enum LogLevelEnum: string
{
    case DEBUG     = 'debug';
    case INFO      = 'info';
    case NOTICE    = 'notice';
    case WARNING   = 'warning';
    case ERROR     = 'error';
    case CRITICAL  = 'critical';
    case ALERT     = 'alert';
    case EMERGENCY = 'emergency';

    /**
     * Returns all log levels ordered by severity, from least to most critical.
     *
     * @return self[]
     */
    public static function ordered(): array
    {
        return [
            self::DEBUG,
            self::INFO,
            self::NOTICE,
            self::WARNING,
            self::ERROR,
            self::CRITICAL,
            self::ALERT,
            self::EMERGENCY,
        ];
    }

    /**
     * Maps a PSR-3 log level string to the corresponding enum case.
     *
     * @param string $level
     * @return self
     *
     * @throws InvalidLogLevelException If the level is not a valid PSR-3 level
     */
    public static function fromPsrLevel(string $level): self
    {
        return match (strtolower($level)) {
            'debug'     => self::DEBUG,
            'info'      => self::INFO,
            'notice'    => self::NOTICE,
            'warning'   => self::WARNING,
            'error'     => self::ERROR,
            'critical'  => self::CRITICAL,
            'alert'     => self::ALERT,
            'emergency' => self::EMERGENCY,
            default     => throw new InvalidLogLevelException("Unsupported log level: '{$level}'"),
        };
    }
}
