<?php

declare(strict_types=1);

namespace Container\Exceptions;

/**
 * Class CircularDependencyException
 *
 * Thrown when a circular dependency is detected during autowiring or resolution.
 */
class CircularDependencyException extends ContainerException
{
    /**
     * Constructs a new CircularDependencyException instance.
     *
     * @param string $id
     * @param array $resolutionStack
     * @param \Throwable|null $previous
     */
    public function __construct(string $id, array $resolutionStack = [], ?\Throwable $previous = null)
    {
        $path = implode(' -> ', $resolutionStack);
        $message = "Circular dependency detected while resolving '{$id}'. Resolution path: {$path}";
        parent::__construct($message, 500, $previous);
    }
}
