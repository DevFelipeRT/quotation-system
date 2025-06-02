<?php

declare(strict_types=1);

namespace Database\Domain\Execution\RequestBuilders;

use Database\Domain\Execution\DatabaseRequestInterface;
use PDO;

/**
 * Interface for building database request objects from active PDO connections.
 *
 * Implementations should be preconfigured via constructor (e.g., with dispatcher)
 * and must return event-aware request objects.
 */
interface RequestBuilderInterface
{
    /**
     * Creates a new instance of a DatabaseRequestInterface from a PDO connection.
     *
     * @param PDO $pdo The active PDO connection instance.
     * @return DatabaseRequestInterface
     */
    public function build(PDO $pdo): DatabaseRequestInterface;
}
