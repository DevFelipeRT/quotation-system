<?php

declare(strict_types=1);

namespace Logging\Application\Contract;

use PublicContracts\Logging\LoggableInputInterface;
use Stringable;

/**
 * LoggingFacadeInterface
 *
 * Orchestrates the full logging cycle: adapts PSR-style calls, assembles entries, and delegates to the logger.
 * Provides unified entrypoints for logging structured data at multiple abstraction levels.
 */
interface LoggingFacadeInterface
{
    /**
     * Assembles and logs from a generic loggable input (application usage).
     *
     * @param LoggableInputInterface $input
     * @return void
     */
    public function logInput(LoggableInputInterface $input): void;

    /**
     * Logs a message with PSR-3 compatible arguments (adapter usage).
     *
     * @param string $level    The log level (e.g., 'error', 'info')
     * @param string|Stringable $message The message, possibly with placeholders
     * @param array<string, mixed> $context Context data for interpolation and structured logging
     * @return void
     */
    public function log(string $level, string|Stringable $message, array $context = []): void;

    //PSR-3 adapter methods.
    public function emergency(string|Stringable $message, array $context = []): void;
    public function alert(string|Stringable $message, array $context = []): void;
    public function critical(string|Stringable $message, array $context = []): void;
    public function error(string|Stringable $message, array $context = []): void;
    public function warning(string|Stringable $message, array $context = []): void;
    public function notice(string|Stringable $message, array $context = []): void;
    public function info(string|Stringable $message, array $context = []): void;
    public function debug(string|Stringable $message, array $context = []): void;
}
