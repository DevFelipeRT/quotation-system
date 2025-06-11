<?php

declare(strict_types=1);

namespace Logging\Exception;

use Logging\Exception\Contract\LoggingException;

/**
 * Thrown when no concrete LoggerInterface implementation has been configured.
 *
 * Useful for service containers or kernels that require a default logger binding.
 */
final class LoggerNotConfiguredException extends LoggingException
{
}
