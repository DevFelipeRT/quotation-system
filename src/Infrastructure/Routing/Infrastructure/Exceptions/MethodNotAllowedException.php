<?php

declare(strict_types=1);

namespace App\Infrastructure\Routing\Infrastructure\Exceptions;

/**
 * Thrown when a route was found for the request path, but the HTTP method is not allowed.
 *
 * This exception signals a 405 Method Not Allowed scenario at the routing level,
 * providing optional context for observability and structured error handling.
 */
final class MethodNotAllowedException extends AbstractRoutingException
{
    /**
     * Returns the prefix to prepend to the error message.
     *
     * @return string
     */
    protected function prefix(): string
    {
        return '[Method Not Allowed] ';
    }

    /**
     * Returns the default error message.
     *
     * @return string
     */
    protected function defaultMessage(): string
    {
        return 'HTTP method not allowed for the requested route.';
    }
}
