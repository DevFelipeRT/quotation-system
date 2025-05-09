<?php

declare(strict_types=1);

namespace App\Infrastructure\Routing\Application\Exceptions;

use App\Shared\Exceptions\ApplicationException;

/**
 * Exception thrown when the application fails to resolve or dispatch a route.
 *
 * This may indicate misconfigured routes, unresolved controllers, or invalid
 * HTTP methods in the routing layer.
 */
final class RouteDispatchException extends ApplicationException
{
}
