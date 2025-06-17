<?php

declare(strict_types=1);

namespace Logging\Domain\ValueObject;

use Logging\Domain\Exception\InvalidLogMessageException;
use Logging\Domain\Security\Contract\LogSecurityInterface;

/**
 * Value Object representing a sanitized and validated log message.
 */
final class LogMessage
{
    private string $message;

    /**
     * Creates a sanitized and validated log message instance.
     *
     * @param string $message Raw input message.
     * @param LogSecurityInterface $security Domain security facade.
     *
     * @throws InvalidLogMessageException
     */
    public function __construct(string $message, LogSecurityInterface $security)
    {
        $sanitizedMessage = $security->sanitize(['message' => $message])['message'];
        $this->message = $this->validateMessage($sanitizedMessage, $security);
    }

    /**
     * Retrieves the validated and sanitized message.
     *
     * @return string
     */
    public function value(): string
    {
        return $this->message;
    }

    /**
     * Validates the sanitized message using the security facade.
     *
     * @param string $message
     * @param LogSecurityInterface $security
     *
     * @return string
     */
    private function validateMessage(string $message, LogSecurityInterface $security): string
    {
        return $security->validateMessage($message);
    }
}
