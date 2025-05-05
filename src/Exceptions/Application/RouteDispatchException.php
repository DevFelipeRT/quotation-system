<?php

declare(strict_types=1);

namespace App\Exceptions\Application;

/**
 * Exception thrown when the application fails to resolve or dispatch a route.
 *
 * This may indicate misconfigured routes, unresolved controllers, or invalid
 * HTTP methods in the routing layer.
 */
final class RouteDispatchException extends ApplicationException
{
}
