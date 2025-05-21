<?php

declare(strict_types=1);

namespace App\Shared\Container;

use Exception;

/**
 * NotFoundException
 *
 * Exception thrown when a requested service is not found in the container.
 */
class NotFoundException extends Exception
{
}
