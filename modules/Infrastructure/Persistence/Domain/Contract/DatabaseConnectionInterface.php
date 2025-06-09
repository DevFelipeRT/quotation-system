<?php

declare(strict_types=1);

namespace Persistence\Domain\Contract;

use PDO;

/**
 * Defines a contract for database connection providers.
 *
 * Implementations must return a valid PDO instance,
 * encapsulating connection logic and error handling.
 *
 */
interface DatabaseConnectionInterface
{
    /**
     * Creates and returns a configured PDO connection.
     *
     * @return PDO
     */
    public function connect(): PDO;

     /**
     * Returns the name of the current driver.
     */
    public function getDriver(): string;

    /**
     * Indicates whether a PDO instance has already been initialized.
     */
    public function isConnected(): bool;
}
