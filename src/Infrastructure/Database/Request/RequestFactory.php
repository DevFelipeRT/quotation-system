<?php

namespace App\Infrastructure\Database\Request;

use PDO;
use App\Interfaces\Infrastructure\LoggerInterface;

/**
 * RequestFactory
 *
 * Responsible for creating instances of DatabaseRequestInterface.
 *
 * This factory encapsulates the instantiation of PdoDatabaseRequest,
 * ensuring that PDO and logger dependencies are correctly injected,
 * and abstracts infrastructure concerns away from upper layers.
 */
final class RequestFactory
{
    private PDO $pdo;
    private LoggerInterface $logger;

    /**
     * Constructs the factory with its required dependencies.
     *
     * @param PDO $pdo An active PDO database connection.
     * @param LoggerInterface $logger Logger instance for query diagnostics.
     */
    public function __construct(PDO $pdo, LoggerInterface $logger)
    {
        $this->pdo = $pdo;
        $this->logger = $logger;
    }

    /**
     * Instantiates a DatabaseRequestInterface implementation.
     *
     * @return DatabaseRequestInterface A configured request executor.
     */
    public function create(): DatabaseRequestInterface
    {
        return new PdoDatabaseRequest($this->pdo, $this->logger);
    }
}
