<?php

declare(strict_types=1);

namespace Routing\Infrastructure\Exceptions;

/**
 * Exception thrown when the module fails to resolve or dispatch a route.
 *
 * This may indicate misconfigured routes, unresolved controllers, or invalid
 * HTTP methods in the routing layer.
 */
final class RouteDispatchException extends AbstractRoutingException
{
    /**
     * Returns the prefix to prepend to the error message.
     *
     * @return string
     */
    protected function prefix(): string
    {
        return '[Dispatch Error] ';
    }

    /**
     * Returns the default error message.
     *
     * @return string
     */
    protected function defaultMessage(): string
    {
        return 'Route dispatch failed.';
    }
}
