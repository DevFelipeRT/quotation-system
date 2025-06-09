<?php

declare(strict_types=1);

namespace Persistence\Infrastructure;

use Config\Database\DatabaseConfig;
use PDO;
use Persistence\Domain\Contract\DatabaseConnectionInterface;
use Persistence\Domain\Contract\DatabaseCredentialsInterface;
use Persistence\Domain\Contract\DatabaseExecutionInterface;
use Persistence\Domain\ValueObject\DatabaseSecret;
use Persistence\Domain\ValueObject\MySqlCredentials;
use Persistence\Domain\ValueObject\PostgreSqlCredentials;
use Persistence\Domain\ValueObject\SqliteCredentials;
use Persistence\Support\DriverValidator;

final class PersistenceKernel
{
    private readonly DatabaseCredentialsInterface $credentials;
    private readonly DriverValidator $validator;
    private readonly DatabaseConnectionInterface $connection;
    private readonly DatabaseExecutionInterface $execution;

    public function __construct(DatabaseConfig $config)
    {
        $this->credentials = $this->initializeCredentials($config);
        $this->connection  = $this->initializeConnection($this->credentials);
        $this->execution   = $this->initializeExecution($this->connection->connect());
    }

    public function connection(): DatabaseConnectionInterface
    {
        return $this->connection;
    }

    public function execution(): DatabaseExecutionInterface
    {
        return $this->execution;
    }

    private function initializeCredentials(DatabaseConfig $config): DatabaseCredentialsInterface
    {
        $this->validator = new DriverValidator($config::getDriversList());
        $driver = $this->validator->resolve($config->getDriver());
        $this->validator->assertIsSupported($driver);

        return match ($driver) {
            'mysql' => new MySqlCredentials(
                host:     $config->getHost(),
                port:     $config->getPort(),
                database: $config->getDatabase(),
                username: $config->getUsername(),
                password: new DatabaseSecret($config->getPassword()),
                options:  $config->getOptions()
            ),
            'pgsql' => new PostgreSqlCredentials(
                host:     $config->getHost(),
                port:     $config->getPort(),
                database: $config->getDatabase(),
                username: $config->getUsername(),
                password: new DatabaseSecret($config->getPassword()),
                options:  $config->getOptions()
            ),
            'sqlite' => new SqliteCredentials(
                filePath: $config->getFile(),
                password: $config->getPassword() !== null
                    ? new DatabaseSecret($config->getPassword())
                    : null,
                options: $config->getOptions()
            ),
        };
    }

    private function initializeConnection(DatabaseCredentialsInterface $credentials): DatabaseConnectionInterface
    {
        return new PdoConnectionService($credentials);
    }

    private function initializeExecution(PDO $pdo): DatabaseExecutionInterface
    {
        return new PdoExecutionService($pdo);
    }
}
