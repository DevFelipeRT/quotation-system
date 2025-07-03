<?php

declare(strict_types=1);

namespace Routing\Domain\ValueObject\Http;

/**
 * Represents an HTTP status code as a typesafe value object.
 *
 * This object ensures that only valid HTTP status codes (100-599) can be
 * used within the application. It can also provide the standard reason
 * phrase associated with a given code.
 */
final class StatusCode
{
    private const MIN_CODE = 100;
    private const MAX_CODE = 599;

    private const REASON_PHRASES = [
        // 1xx Informational
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        // 2xx Success
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        // 3xx Redirection
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        // 4xx Client Error
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        409 => 'Conflict',
        410 => 'Gone',
        422 => 'Unprocessable Entity',
        // 5xx Server Error
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
    ];

    private readonly int $code;

    /**
     * @param int $code The HTTP status code.
     * @throws \InvalidArgumentException If the code is not a valid HTTP status code.
     */
    public function __construct(int $code)
    {
        $this->ensureIsInRange($code);
        $this->code = $code;
    }

    /**
     * Retrieves the integer value of the status code.
     *
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * Retrieves the standard reason phrase for the status code.
     *
     * @return string Returns the standard reason phrase or an empty string if unknown.
     */
    public function getReasonPhrase(): string
    {
        return self::REASON_PHRASES[$this->code] ?? '';
    }

    /**
     * Checks if two StatusCode objects are equal.
     *
     * @param self $other
     * @return bool
     */
    public function equals(self $other): bool
    {
        return $this->code === $other->code;
    }

    /**
     * @param int $code
     * @return void
     * @throws \InvalidArgumentException
     */
    private function ensureIsInRange(int $code): void
    {
        if ($code < self::MIN_CODE || $code > self::MAX_CODE) {
            throw new \InvalidArgumentException(
                sprintf('Invalid HTTP status code. Must be between %d and %d.', self::MIN_CODE, self::MAX_CODE)
            );
        }
    }
}