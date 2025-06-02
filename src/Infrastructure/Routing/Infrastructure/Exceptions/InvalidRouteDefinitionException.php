<?php

declare(strict_types=1);

namespace App\Infrastructure\Routing\Infrastructure\Exceptions;

/**
 * Thrown when a route definition is invalid or inconsistent (e.g., bad path, invalid controller, etc).
 */
final class InvalidRouteDefinitionException extends AbstractRoutingException
{
    /**
     * Returns the prefix to prepend to the error message.
     */
    protected function prefix(): string
    {
        return '[Invalid Route Definition] ';
    }

    /**
     * Returns the default error message.
     */
    protected function defaultMessage(): string
    {
        return 'Invalid route definition or configuration.';
    }
}
