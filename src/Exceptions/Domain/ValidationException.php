<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

use RuntimeException;

/**
 * Exception thrown when input data or domain invariants are violated.
 *
 * Used to indicate that a request or command contains invalid data
 * that prevents the operation from being executed as intended.
 */
class ValidationException extends RuntimeException
{
}
