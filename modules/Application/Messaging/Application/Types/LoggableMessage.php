<?php

namespace Messaging\Application\Types;

use Messaging\Application\AbstractMessage;
use Logging\Application\LoggableInputInterface;
use DateTimeImmutable;

/**
 * Represents a structured log message within the application.
 *
 * Implements LoggableInputInterface to support integration with the logging module.
 * Provides factory methods for common severity levels.
 */
final class LoggableMessage extends AbstractMessage implements LoggableInputInterface
{
    /**
     * Optional grouping channel for logs (e.g. subsystem name).
     *
     * @var string|null
     */
    private ?string $channel = null;

    /**
     * Identifies this message as a 'log' type.
     *
     * @return string
     */
    public function getType(): string
    {
        return 'log';
    }

    /**
     * Returns the channel associated with this log message, if defined.
     *
     * @return string|null
     */
    public function getChannel(): ?string
    {
        return $this->channel;
    }

    /**
     * Returns a new instance with the given channel assigned.
     *
     * @param string $channel
     * @return self
     */
    public function withChannel(string $channel): self
    {
        $clone = clone $this;
        $clone->channel = $channel;
        return $clone;
    }

    /**
     * Creates a DEBUG-level log message.
     */
    public static function debug(string $text, array $context = [], ?DateTimeImmutable $timestamp = null): self
    {
        return new self($text, $context, 'DEBUG', $timestamp);
    }

    /**
     * Creates an INFO-level log message.
     */
    public static function info(string $text, array $context = [], ?DateTimeImmutable $timestamp = null): self
    {
        return new self($text, $context, 'INFO', $timestamp);
    }

    /**
     * Creates a WARNING-level log message.
     */
    public static function warning(string $text, array $context = [], ?DateTimeImmutable $timestamp = null): self
    {
        return new self($text, $context, 'WARNING', $timestamp);
    }

    /**
     * Creates an ERROR-level log message.
     */
    public static function error(string $text, array $context = [], ?DateTimeImmutable $timestamp = null): self
    {
        return new self($text, $context, 'ERROR', $timestamp);
    }

    /**
     * Creates a CRITICAL-level log message.
     */
    public static function critical(string $text, array $context = [], ?DateTimeImmutable $timestamp = null): self
    {
        return new self($text, $context, 'CRITICAL', $timestamp);
    }
}
