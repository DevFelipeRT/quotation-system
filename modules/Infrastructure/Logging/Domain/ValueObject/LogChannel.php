<?php

declare(strict_types=1);

namespace Logging\Domain\ValueObject;

use Logging\Domain\Security\Contract\LogSanitizerInterface;
use Logging\Domain\Exception\InvalidLogChannelException;

/**
 * Value Object representing a log channel (logger name or destination).
 * Ensures safety, normalization, and immutability by using a LogSanitizer.
 *
 * @immutable
 */
final class LogChannel
{
    /**
     * @var string
     */
    private string $channel;

    /**
     * @param string $channel
     * @param LogSanitizerInterface $sanitizer
     * @throws InvalidLogChannelException
     */
    public function __construct(string $channel, LogSanitizerInterface $sanitizer)
    {
        $sanitized = $this->sanitizeChannel($channel, $sanitizer);
        $this->channel = $this->validateChannel($sanitized);
    }

    /**
     * Returns the channel as string.
     */
    public function value(): string
    {
        return $this->channel;
    }

    /**
     * String representation for direct echo, print, etc.
     */
    public function __toString(): string
    {
        return $this->channel;
    }

    /**
     * Applies the sanitizer and normalization to the input.
     */
    private function sanitizeChannel(string $channel, LogSanitizerInterface $sanitizer): string
    {
        $sanitized = $sanitizer->sanitize(['channel' => $channel])['channel'] ?? '';
        return mb_strtolower(trim($sanitized));
    }

    /**
     * Validates a sanitized, normalized channel string.
     *
     * @param string $chan
     * @return string
     * @throws InvalidLogChannelException
     */
    private function validateChannel(string $chan): string
    {
        if ($chan === '') {
            throw InvalidLogChannelException::empty();
        }
        if (preg_match('/[\x00-\x1F\x7F]/', $chan)) {
            throw InvalidLogChannelException::invalidCharacters();
        }
        if (mb_strlen($chan) > 128) {
            throw InvalidLogChannelException::tooLong();
        }
        return $chan;
    }
}
