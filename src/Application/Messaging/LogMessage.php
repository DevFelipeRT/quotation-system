<?php

namespace App\Application\Messaging;

use DateTimeImmutable;

/**
 * Represents a semantically structured log message created within the application layer.
 *
 * Can later be converted into a LogEntry for persistence by a logger.
 */
final class LogMessage extends AbstractMessage
{
    /**
     * Returns the message type identifier.
     *
     * @return string
     */
    public function getType(): string
    {
        return 'log';
    }

    /**
     * Creates a new log message of DEBUG severity.
     *
     * @param string $text
     * @param array $context
     * @param DateTimeImmutable|null $timestamp
     * @return static
     */
    public static function debug(string $text, array $context = [], ?DateTimeImmutable $timestamp = null): self
    {
        return new self($text, $context, 'DEBUG', $timestamp);
    }

    /**
     * Creates a new log message of INFO severity.
     *
     * @param string $text
     * @param array $context
     * @param DateTimeImmutable|null $timestamp
     * @return static
     */
    public static function info(string $text, array $context = [], ?DateTimeImmutable $timestamp = null): self
    {
        return new self($text, $context, 'INFO', $timestamp);
    }

    /**
     * Creates a new log message of WARNING severity.
     *
     * @param string $text
     * @param array $context
     * @param DateTimeImmutable|null $timestamp
     * @return static
     */
    public static function warning(string $text, array $context = [], ?DateTimeImmutable $timestamp = null): self
    {
        return new self($text, $context, 'WARNING', $timestamp);
    }

    /**
     * Creates a new log message of ERROR severity.
     *
     * @param string $text
     * @param array $context
     * @param DateTimeImmutable|null $timestamp
     * @return static
     */
    public static function error(string $text, array $context = [], ?DateTimeImmutable $timestamp = null): self
    {
        return new self($text, $context, 'ERROR', $timestamp);
    }

    /**
     * Creates a new log message of CRITICAL severity.
     *
     * @param string $text
     * @param array $context
     * @param DateTimeImmutable|null $timestamp
     * @return static
     */
    public static function critical(string $text, array $context = [], ?DateTimeImmutable $timestamp = null): self
    {
        return new self($text, $context, 'CRITICAL', $timestamp);
    }
}
