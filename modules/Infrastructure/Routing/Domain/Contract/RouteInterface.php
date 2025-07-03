<?php

declare(strict_types=1);

namespace Routing\Domain\Contract;

use Routing\Domain\ValueObject\Match\Parameters;
use Routing\Domain\ValueObject\Definition\Handler;

/**
 * Describes a successful route match.
 *
 * This contract represents the result of a router match, containing the handler
 * to be executed and any parameters extracted from the request URI. Its design
 * is compatible with PSR principles for routing components.
 */
interface RouteInterface
{
    /**
     * Retrieves the handler associated with the route.
     *
     * The handler is encapsulated in a Handler value object.
     *
     * @return Handler
     */
    public function getHandler(): Handler;

    /**
     * Retrieves the parameters extracted from the URI.
     *
     * The parameters are encapsulated in a typesafe Parameters collection.
     *
     * @return Parameters
     */
    public function getParameters(): Parameters;
}