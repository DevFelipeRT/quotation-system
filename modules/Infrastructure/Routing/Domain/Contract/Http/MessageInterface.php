<?php

declare(strict_types=1);

namespace Routing\Domain\Contract\Http;

use Routing\Domain\Contract\Http\HttpStreamInterface;

/**
 * Describes the components of a generic HTTP message.
 *
 * This contract represents the common elements of an HTTP request or response,
 * such as the protocol version, headers, and body. Its design is compatible
 * with the principles defined in PSR-7.
 */
interface MessageInterface
{
    /**
     * Retrieves the HTTP protocol version as a string.
     *
     * @return string The HTTP protocol version (e.g., "1.1", "2.0").
     */
    public function getProtocolVersion(): string;

    /**
     * Retrieves all message header values.
     *
     * @return array<string, string[]> An associative array where keys are header
     * names and values are arrays of strings for that header.
     */
    public function getHeaders(): array;

    /**
     * Checks if a header exists by the given case-insensitive name.
     *
     * @param string $name Case-insensitive header field name.
     * @return bool
     */
    public function hasHeader(string $name): bool;

    /**
     * Retrieves a message header value by its case-insensitive name.
     *
     * @param string $name Case-insensitive header field name.
     * @return string[] An array of string values for the given header.
     */
    public function getHeader(string $name): array;

    /**
     * Gets the body of the message.
     *
     * @return HttpStreamInterface Returns the body as a stream.
     */
    public function getBody(): HttpStreamInterface;
}