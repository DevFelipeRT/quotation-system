<?php

declare(strict_types=1);

namespace Routing\Domain\Contract\Http;

use Routing\Domain\Contract\Http\MessageInterface;
use Routing\Domain\Contract\Http\UriInterface;
use Routing\Domain\Contract\RequestInterface;

/**
 * Represents an incoming, server-side HTTP request.
 *
 * This interface extends both the generic, domain-level RequestInterface and
 * the HTTP-specific MessageInterface, aggregating their capabilities. It adds
 * methods for accessing request-specific information. Its design is
 * compatible with the principles of PSR-7.
 */
interface HttpRequestInterface extends RequestInterface, MessageInterface
{
    /**
     * Retrieves the message's request target.
     *
     * @return string
     */
    public function getRequestTarget(): string;

    /**
     * Retrieves the URI instance.
     *
     * @return UriInterface Returns a UriInterface instance representing the URI of the request.
     */
    public function getUri(): UriInterface;

    /**
     * Retrieves a single cookie value by name.
     *
     * @param string $name The cookie name.
     * @param mixed|null $default The default value to return if the cookie does not exist.
     * @return mixed
     */
    public function getCookie(string $name, mixed $default = null): mixed;

    /**
     * Retrieves a single query parameter from the URL's query string.
     *
     * @param string $name The query parameter name.
     * @param mixed|null $default The default value to return if the parameter does not exist.
     * @return mixed
     */
    public function getQueryParam(string $name, mixed $default = null): mixed;

    /**
     * Retrieves the parsed body of the request (e.g., from POST data).
     *
     * @return array<mixed>|object|null The deserialized body data, if any.
     */
    public function getParsedBody(): array|object|null;
}