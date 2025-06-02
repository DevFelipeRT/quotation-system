<?php

declare(strict_types=1);

namespace Database\Domain\Connection;

use PDO;

/**
 * Defines a contract for database connection providers.
 *
 * Implementations must return a valid PDO instance,
 * encapsulating connection logic and error handling.
 *
 * @package Database\Connection
 */
interface DatabaseConnectionInterface
{
    /**
     * Creates and returns a configured PDO connection.
     *
     * @return PDO
     */
    public function connect(): PDO;
}
