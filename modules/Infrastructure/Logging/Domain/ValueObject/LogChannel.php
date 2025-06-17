<?php

declare(strict_types=1);

namespace Logging\Domain\ValueObject;

use Logging\Domain\Security\Contract\LogSecurityInterface;
use Logging\Domain\Exception\InvalidLogChannelException;

/**
 * Immutable Value Object representing a log channel (logger name or destination).
 *
 * This class encapsulates validation and sanitization logic to ensure
 * that the provided log channel conforms strictly to domain requirements.
 * 
 * @immutable
 */
final class LogChannel
{
    /**
     * @var string The validated and sanitized log channel.
     */
    private string $channel;

    /**
     * Constructs a LogChannel instance, validating and sanitizing the provided channel.
     *
     * @param string               $channel  The raw log channel string.
     * @param LogSecurityInterface $security Domain security facade for validation and sanitization.
     *
     * @throws InvalidLogChannelException If the provided channel is invalid.
     */
    public function __construct(string $channel, LogSecurityInterface $security)
    {
        $this->channel = $this->sanitizeAndValidate($channel, $security);
    }

    /**
     * Returns the log channel as a string.
     *
     * @return string
     */
    public function value(): string
    {
        return $this->channel;
    }

    /**
     * String representation of the log channel for direct echoing or printing.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->channel;
    }

    /**
     * Sanitizes and validates the log channel.
     *
     * Utilizes the domain security facade to sanitize the input,
     * then applies domain-specific validation rules.
     *
     * @param string               $channel
     * @param LogSecurityInterface $security
     *
     * @return string $sanitizedAndValidated
     *
     * @throws InvalidLogChannelException If validation rules are violated.
     */
    private function sanitizeAndValidate(string $channel, LogSecurityInterface $security): string
    {
        $sanitized = $security->sanitize($channel);
        $sanitizedAndValidated = $security->validateChannel($sanitized);

        return $sanitizedAndValidated;
    }
}
