<?php

declare(strict_types=1);

namespace App\Infrastructure\Routing\Infrastructure\Exceptions;

/**
 * Thrown when the routing infrastructure is unable to resolve a request to any registered route.
 *
 * This exception signals a 404 scenario at the routing level and allows inclusion of
 * contextual diagnostic data for observability, logging, and advanced error handling.
 */
final class RouteNotFoundException extends AbstractRoutingException
{
    /**
     * Returns the prefix to prepend to the error message.
     *
     * @return string
     */
    protected function prefix(): string
    {
        return '[Route Not Found] ';
    }

    /**
     * Returns the default error message.
     *
     * @return string
     */
    protected function defaultMessage(): string
    {
        return 'No route matched the given request.';
    }
}
