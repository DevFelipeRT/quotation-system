<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Infrastructure\Execution\RequestBuilders;

use App\Infrastructure\Database\Domain\Execution\DatabaseRequestInterface;
use App\Infrastructure\Database\Domain\Execution\RequestBuilders\RequestBuilderInterface;
use App\Infrastructure\Database\Infrastructure\Execution\PdoDatabaseRequest;
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
