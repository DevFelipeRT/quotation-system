<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Execution\RequestBuilders;

use App\Infrastructure\Database\Execution\DatabaseRequestInterface;
use App\Infrastructure\Database\Execution\PdoDatabaseRequest;
use PDO;

/**
 * Concrete builder for creating PDO-based database request objects.
 */
final class PdoRequestBuilder implements RequestBuilderInterface
{
    /**
     * @inheritDoc
     */
    public function build(PDO $pdo, array $observers = []): DatabaseRequestInterface
    {
        return new PdoDatabaseRequest($pdo, $observers);
    }
}
