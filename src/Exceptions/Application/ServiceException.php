<?php

declare(strict_types=1);

namespace App\Exceptions\Application;

/**
 * Thrown when an application service operation fails due to an internal error
 * or an unrecoverable condition in the service layer.
 *
 * This exception represents high-level use case failures, distinct from infrastructure
 * issues or validation problems, and may optionally include contextual data via the
 * inherited ApplicationException contract.
 */
class ServiceException extends ApplicationException
{
}
