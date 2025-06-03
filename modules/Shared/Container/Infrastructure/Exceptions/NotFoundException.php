<?php

declare(strict_types=1);

namespace Container\Infrastructure\Exceptions;

/**
 * Class NotFoundException
 *
 * Thrown when a service binding or class cannot be resolved by the container.
 */
class NotFoundException extends \Exception
{
    /**
     * Constructs a new NotFoundException instance.
     *
     * @param string $id The requested identifier.
     * @param \Throwable|null $previous
     */
    public function __construct(string $id, ?\Throwable $previous = null)
    {
        parent::__construct("No binding or resolvable class found for identifier: {$id}.", 404, $previous);
    }
}
