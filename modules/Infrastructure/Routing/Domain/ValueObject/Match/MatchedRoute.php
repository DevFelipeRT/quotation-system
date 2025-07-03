<?php

declare(strict_types=1);

namespace Routing\Domain\ValueObject\Match;

use Routing\Domain\Contract\RouteInterface;
use Routing\Domain\ValueObject\Definition\Handler;
use Routing\Domain\ValueObject\Match\Parameters;

/**
 * Represents a successful route match as an immutable value object.
 *
 * This object is the concrete result of a successful routing operation,
 * encapsulating the handler to be executed and the parameters extracted
 * from the URI. It implements the RouteInterface contract.
 */
final class MatchedRoute implements RouteInterface
{
    /**
     * @param Handler    $handler    The handler associated with the matched route.
     * @param Parameters $parameters The parameters extracted from the URI.
     */
    public function __construct(
        private readonly Handler $handler,
        private readonly Parameters $parameters
    ) {
    }

    /**
     * Retrieves the handler associated with the route.
     *
     * @return Handler
     */
    public function getHandler(): Handler
    {
        return $this->handler;
    }

    /**
     * Retrieves the parameters extracted from the URI.
     *
     * @return Parameters
     */
    public function getParameters(): Parameters
    {
        return $this->parameters;
    }
}