<?php

namespace App\Infrastructure\Logging\Exceptions;

use RuntimeException;

/**
 * Base exception for all logging-related errors.
 */
abstract class LoggingException extends RuntimeException {}
