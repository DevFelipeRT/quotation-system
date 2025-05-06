<?php

namespace Config\Database\Exceptions;

use InvalidArgumentException;

/**
 * Exception thrown when a database driver is not supported.
 */
class UnsupportedDriverException extends InvalidArgumentException {}
