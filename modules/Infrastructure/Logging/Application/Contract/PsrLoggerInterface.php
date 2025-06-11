<?php

declare(strict_types=1);

namespace Logging\Application\Contract;

use Stringable;

/**
 * PSR-3 compatible logging interface, defined locally to maintain
 * modular independence from external dependencies like composer/psr-log.
 *
 * This interface allows middleware and infrastructure layers
 * to perform contextual logging without coupling to domain logic.
 */
interface PsrLoggerInterface
{
    /**
     * System is unusable.
     */
    public function emergency(string|Stringable $message, array $context = []): void;

    /**
     * Action must be taken immediately.
     */
    public function alert(string|Stringable $message, array $context = []): void;

    /**
     * Critical conditions.
     */
    public function critical(string|Stringable $message, array $context = []): void;

    /**
     * Runtime errors that do not require immediate action but should typically be logged.
     */
    public function error(string|Stringable $message, array $context = []): void;

    /**
     * Exceptional occurrences that are not errors.
     */
    public function warning(string|Stringable $message, array $context = []): void;

    /**
     * Normal but significant events.
     */
    public function notice(string|Stringable $message, array $context = []): void;

    /**
     * Interesting events.
     */
    public function info(string|Stringable $message, array $context = []): void;

    /**
     * Detailed debug information.
     */
    public function debug(string|Stringable $message, array $context = []): void;

    /**
     * Logs with an arbitrary level.
     *
     * @param string $level
     * @param string|Stringable $message
     * @param array<string, mixed> $context
     *
     * @throws \InvalidArgumentException
     */
    public function log(string $level, string|Stringable $message, array $context = []): void;
}
