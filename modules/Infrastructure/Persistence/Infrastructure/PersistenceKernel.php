<?php

declare(strict_types=1);

namespace Persistence\Infrastructure;

use Config\Database\DatabaseConfig;
use Persistence\Domain\Contract\DatabaseConnectionInterface;
use Persistence\Domain\Contract\DatabaseCredentialsInterface;
use Persistence\Domain\Contract\DatabaseExecutionInterface;
use Persistence\Domain\ValueObject\DatabaseSecret;
use Persistence\Domain\ValueObject\MySqlCredentials;
use Persistence\Domain\ValueObject\PostgreSqlCredentials;
use Persistence\Domain\ValueObject\SqliteCredentials;
use Persistence\Infrastructure\Support\DriverValidator;
use PublicContracts\Persistence\PersistenceFacadeInterface;
use PDO;

final class PersistenceKernel
{
    private readonly DatabaseCredentialsInterface $credentials;
    private readonly DriverValidator $validator;
    private readonly DatabaseConnectionInterface $connectionService;
    private readonly DatabaseExecutionInterface $executionService;
    private readonly PersistenceFacadeInterface $facade;
    private readonly QueryBuilder $builder;
    private readonly PDO $connection;

    public function __construct(DatabaseConfig $config)
    {
        $this->credentials = $this->initializeCredentials($config);
        $this->connectionService  = new PdoConnectionService($this->credentials);
        $this->connection = $this->connectionService()->connect();
        $this->executionService   = new PdoExecutionService($this->connection);
        $this->facade = new PersistenceFacade($this->executionService);
        $this->builder = new QueryBuilder;
    }
    
    public function builder(): QueryBuilder
    {
        return $this->builder;
    }

    public function connection(): PDO
    {
        return $this->connection;
    }

    public function connectionService(): DatabaseConnectionInterface
    {
        return $this->connectionService;
    }

    public function executionService(): DatabaseExecutionInterface
    {
        return $this->executionService;
    }

    public function facade(): PersistenceFacadeInterface
    {
        return $this->facade;
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
}
