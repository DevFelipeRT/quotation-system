<?php

declare(strict_types=1);

namespace Rendering\Domain\Exception;

use InvalidArgumentException;

/**
 * Thrown when a directory path is deemed invalid or unsafe.
 */
class InvalidDirectoryException extends InvalidArgumentException
{
    // You can add custom methods here in the future if needed.
}