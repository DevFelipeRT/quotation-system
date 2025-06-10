<?php

declare(strict_types=1);

namespace Tests\Persistence;

require __DIR__ . '/../test-bootstrap.php';

use Config\Database\DatabaseConfig;
use Persistence\Domain\Contract\DatabaseConnectionInterface;
use Persistence\Domain\Contract\DatabaseCredentialsInterface;
use Persistence\Domain\Contract\DatabaseExecutionInterface;
use Persistence\Domain\ValueObject\MySqlCredentials;
use Persistence\Domain\ValueObject\SqliteCredentials;
use Persistence\Domain\ValueObject\PostgreSqlCredentials;
use Persistence\Domain\ValueObject\DatabaseSecret;
use Persistence\Infrastructure\PdoConnectionService;
use Persistence\Infrastructure\PdoExecutionService;
use Tests\IntegrationTestHelper;
use PDO;
use Persistence\Domain\Contract\QueryInterface;
use Persistence\Infrastructure\QueryBuilder;

final class PersistenceTest extends IntegrationTestHelper
{
    private ?DatabaseConfig $config = null;
    private ?DatabaseCredentialsInterface $credentials = null;
    private ?DatabaseConnectionInterface $connection = null;
    private ?DatabaseExecutionInterface $execution = null;
    private ?QueryInterface $query = null;
    private ?PDO $pdo = null;

    public function __construct()
    {
        parent::__construct('Persistence Module Test');
        $this->setUp();
        $this->config = $this->configProvider->getDatabaseConfig();
        $this->printStatus("Environment setup complete.", 'OK');
    }

    public function run(): void
    {
        $this->printStatus("Starting Persistence Module tests.", 'RUN');
        $this->testCredentialCreation();
        $this->testConnectionEstablishment();
        $this->testQueryBuilding();
        $this->testQueryExecution();
        $this->printStatus("All Persistence Module tests completed.", 'END');
        $this->finalResult();
    }

    private function testCredentialCreation(): void
    {
        $this->printStatus("STEP 1: Creating credentials based on configured driver.", 'STEP');

        try {
            $driver = $this->config->getDriver();
            $this->printStatus("Using driver: $driver", 'INFO');

            switch ($driver) {
                case 'mysql':
                    $credentials = new MySqlCredentials(
                        host: $this->config->getHost(),
                        port: $this->config->getPort(),
                        database: $this->config->getDatabase(),
                        username: $this->config->getUsername(),
                        password: new DatabaseSecret($this->config->getPassword()),
                        options: $this->config->getOptions()
                    );
                    break;

                case 'pgsql':
                    $credentials = new PostgreSqlCredentials(
                        host: $this->config->getHost(),
                        port: $this->config->getPort(),
                        database: $this->config->getDatabase(),
                        username: $this->config->getUsername(),
                        password: new DatabaseSecret($this->config->getPassword()),
                        options: $this->config->getOptions()
                    );
                    break;

                case 'sqlite':
                    $credentials = new SqliteCredentials(
                        filePath: $this->config->getFile(),
                        options: $this->config->getOptions()
                    );
                    break;

                default:
                    throw new \RuntimeException("Unsupported driver: $driver");
            }

            $this->credentials = $credentials;

            $this->printStatus("Credential object created successfully.", 'OK');
            $this->saveResult('Credential creation', true);
        } catch (\Throwable $e) {
            $this->printStatus("Failed to create credential object.", 'ERROR');
            $this->saveResult('Credential creation', false);
            $this->handleException($e);
        }
    }

    private function testConnectionEstablishment(): void
    {
        $this->printStatus("STEP 2: Testing PDO connection establishment.", 'STEP');

        try {
            $this->connection = new PdoConnectionService($this->credentials);
            $this->pdo = $this->connection?->connect();
            $this->printStatus("Connection established. Driver: " . $this->connection?->getDriver(), 'OK');
            $this->saveResult('Connection establishment', true);
        } catch (\Throwable $e) {
            $this->printStatus("Connection failed.", 'ERROR');
            $this->saveResult('Connection establishment', false);
            $this->handleException($e);
        }
    }

    private function testQueryBuilding(): void
    {
        $this->printStatus("STEP 3: Testing Query building.", 'STEP');
        try {
            $builder = new QueryBuilder();
            $this->query = $builder
                ->selectRaw('1 as test_value')
                ->table('DUAL')
                ->build();
            ;
            $this->printStatus("Query built. Result: " . json_encode($this->query), 'OK');
            $this->saveResult('Query building.', true);
        } catch (\Throwable $e) {
            $this->printStatus("Query building failed.", 'ERROR');
            $this->saveResult('Query building', false);
            $this->handleException($e);
        }
    }

    private function testQueryExecution(): void
    {
        $this->printStatus("STEP 4: Executing test query.", 'STEP');

        try {
            $this->execution = new PdoExecutionService($this->pdo);
            $result = $this->execution?->execute($this->query);
            $this->printStatus("Query executed. Result: " . json_encode($result), 'OK');
            $this->saveResult('Query execution', true);
        } catch (\Throwable $e) {
            $this->printStatus("Query failed to execute.", 'ERROR');
            $this->saveResult('Query execution', false);
            $this->handleException($e);
        }
    }
}

$test = new PersistenceTest();
$test->run();
