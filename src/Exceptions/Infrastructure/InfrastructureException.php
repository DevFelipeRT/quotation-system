<?php

declare(strict_types=1);

namespace App\Exceptions\Infrastructure;

use RuntimeException;

/**
 * Base exception for infrastructure-level failures such as I/O, database,
 * network, or service connectivity errors.
 *
 * Used when the system encounters technical problems external to the domain
 * or application logic.
 */
class InfrastructureException extends RuntimeException
{
}
