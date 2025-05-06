<?php

namespace Config\Database\Exceptions;

use InvalidArgumentException;

/**
 * Exception thrown when the DB_DRIVER environment variable is missing or empty.
 */
class MissingDriverConfigurationException extends InvalidArgumentException {}
