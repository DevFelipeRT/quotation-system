<?php

declare(strict_types=1);

namespace Routing\Domain\ValueObject\Definition;

use Routing\Domain\ValueObject\Definition\Verb;
use Routing\Domain\ValueObject\Definition\PathPattern;
use Routing\Domain\ValueObject\Definition\Handler;

/**
 * Represents a complete route definition as a single, immutable value object.
 *
 * This object aggregates all the necessary components for defining a route
 * (verb, path pattern, and handler), ensuring that a route is always
 * configured with valid, typesafe objects.
 */
final class RouteDefinition
{
    /**
     * @param Verb        $verb        The generic verb for the route (e.g., 'GET', 'CLI').
     * @param PathPattern $pathPattern The path pattern to match.
     * @param Handler     $handler     The handler to be executed.
     */
    public function __construct(
        private readonly Verb $verb,
        private readonly PathPattern $pathPattern,
        private readonly Handler $handler
    ) {
    }

    /**
     * Retrieves the route's generic verb.
     *
     * @return Verb
     */
    public function getVerb(): Verb
    {
        return $this->verb;
    }

    /**
     * Retrieves the route's path pattern.
     *
     * @return PathPattern
     */
    public function getPathPattern(): PathPattern
    {
        return $this->pathPattern;
    }

    /**
     * Retrieves the route's handler.
     *
     * @return Handler
     */
    public function getHandler(): Handler
    {
        return $this->handler;
    }
}