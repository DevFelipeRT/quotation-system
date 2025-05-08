<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Domain\Execution\RequestBuilders;

use PDO;

/**
 * Interface for building database request objects.
 *
 * Provides a generic contract for constructing a DatabaseRequestInterface
 * from a PDO connection and optional observers.
 */
interface RequestBuilderInterface
{
    /**
     * Creates a new instance of a DatabaseRequestInterface.
     *
     * @param PDO $pdo Active PDO connection.
     * @param array $observers Optional array of request observers.
     * @return DatabaseRequestInterface
     */
    public function build(PDO $pdo, array $observers = []): DatabaseRequestInterface;
}
