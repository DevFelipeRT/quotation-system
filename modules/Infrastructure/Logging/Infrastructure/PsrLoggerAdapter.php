<?php

declare(strict_types=1);

namespace Logging\Infrastructure;

use Stringable;
use DateTimeImmutable;
use Logging\Application\Contract\LoggerInterface;
use Logging\Application\Contract\PsrLoggerInterface;
use Logging\Application\Contract\LogEntryAssemblerInterface;
use Logging\Domain\ValueObject\LoggableInput;

/**
 * Adapter for structured logging that implements a PSR-3 compatible interface.
 *
 * This class receives PSR-style log calls and delegates the logging
 * process to a domain-driven LoggerInterface, translating log levels
 * and encapsulating messages as structured LogEntry instances.
 */
final class PsrLoggerAdapter implements PsrLoggerInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly LogEntryAssemblerInterface $assembler
    ) {}

    public function log(string $level, string|Stringable $message, array $context = []): void
    {
        $finalMessage = $this->interpolate($message, $context);

        $input = new LoggableInput(
            level: $level,
            message: (string)$finalMessage,
            context: $context,
            channel: null,
            timestamp: new DateTimeImmutable()
        );

        $entry = $this->assembler->assembleFromInput($input);

        $this->logger->log($entry);
    }

    public function emergency(string|Stringable $message, array $context = []): void
    {
        $this->log('emergency', $message, $context);
    }

    public function alert(string|Stringable $message, array $context = []): void
    {
        $this->log('alert', $message, $context);
    }

    public function critical(string|Stringable $message, array $context = []): void
    {
        $this->log('critical', $message, $context);
    }

    public function error(string|Stringable $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    public function warning(string|Stringable $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    public function notice(string|Stringable $message, array $context = []): void
    {
        $this->log('notice', $message, $context);
    }

    public function info(string|Stringable $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    public function debug(string|Stringable $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    /**
     * Interpolates context values into the message placeholders according to PSR-3.
     *
     * For each key in context, replaces occurrences of `{key}` in the message with its string value.
     * Only scalar values, null, or objects with __toString() are interpolated.
     * Non-interpolated context values permanecem no contexto para logging.
     *
     * @param string $message The message with PSR-3 placeholders (e.g., "User {user} created")
     * @param array<string, mixed> $context The context data to replace
     * @return string The message with placeholders replaced
     */
    private function interpolate(string $message, array $context): string
    {
        if (strpos($message, '{') === false) {
            return $message;
        }

        $replace = [];
        foreach ($context as $key => $value) {
            // Only interpolate scalar values, null, or objects with __toString()
            if (is_null($value) || is_scalar($value) || (is_object($value) && method_exists($value, '__toString'))) {
                $replace['{' . $key . '}'] = (string)$value;
            }
        }
        // Efficient strtr for replacement, as specified by PSR-3
        return strtr($message, $replace);
    }

}
