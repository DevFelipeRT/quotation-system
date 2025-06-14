<?php

declare(strict_types=1);

namespace Logging\Domain\ValueObject;

use Logging\Domain\Security\Contract\LogSanitizerInterface;
use Logging\Domain\Exception\InvalidLogMessageException;

/**
 * Value Object representing a log message.
 * Always sanitized and validated to be safe for logging.
 */
final class LogMessage
{
    /**
     * @var string
     */
    private string $message;

    /**
     * @param string $message
     * @param LogSanitizerInterface $sanitizer
     * @throws InvalidLogMessageException
     */
    public function __construct(string $message, LogSanitizerInterface $sanitizer)
    {
        $sanitized = $this->sanitizeWithSanitizer($message, $sanitizer);
        $this->validateMessage($sanitized);
        $this->message = $sanitized;
    }

    /**
     * Returns the log message as string.
     */
    public function value(): string
    {
        return $this->message;
    }

    public function __toString(): string
    {
        return $this->message;
    }

    /**
     * Applies the domain sanitizer to the message string.
     *
     * @param string $message
     * @param LogSanitizerInterface $sanitizer
     * @return string
     */
    private function sanitizeWithSanitizer(string $message, LogSanitizerInterface $sanitizer): string
    {
        $result = $sanitizer->sanitize(['message' => $message])['message'] ?? '';
        return trim((string)$result);
    }

    /**
     * Validates the sanitized log message.
     *
     * @param string $message
     * @throws InvalidLogMessageException
     */
    private function validateMessage(string $message): void
    {
        if ($message === '') {
            throw InvalidLogMessageException::empty();
        }
        if (preg_match('/[\x00-\x08\x0B-\x1F\x7F]/', $message)) {
            throw InvalidLogMessageException::invalidCharacters();
        }
        if (mb_strlen($message) > 2000) {
            throw InvalidLogMessageException::tooLong();
        }
    }
}
