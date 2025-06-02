<?php

declare(strict_types=1);

namespace App\Infrastructure\Routing\Infrastructure\Exceptions;

/**
 * Thrown when attempting to register a duplicate route (by name or path+method).
 */
final class DuplicateRouteException extends AbstractRoutingException
{
    /**
     * Returns the prefix to prepend to the error message.
     */
    protected function prefix(): string
    {
        return '[Duplicate Route] ';
    }

    /**
     * Returns the default error message.
     */
    protected function defaultMessage(): string
    {
        return 'A route with the same name or path/method is already registered.';
    }
}
