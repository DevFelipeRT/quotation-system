<?php

declare(strict_types=1);

namespace Tests\Persistence;

require __DIR__ . '/../test-bootstrap.php';

use Config\Database\DatabaseConfig;
use Persistence\Domain\Contract\DatabaseConnectionInterface;
use Persistence\Domain\Contract\DatabaseExecutionInterface;
use Persistence\Domain\ValueObject\MySqlCredentials;
use Persistence\Domain\ValueObject\SqliteCredentials;
use Persistence\Domain\ValueObject\PostgreSqlCredentials;
use Persistence\Domain\ValueObject\DatabaseSecret;
use Persistence\Infrastructure\PdoConnectionService;
use Persistence\Infrastructure\PdoExecutionService;
use Tests\IntegrationTestHelper;

final class PersistenceTest extends IntegrationTestHelper
{
    private ?DatabaseConfig $config = null;
    private ?DatabaseConnectionInterface $connection = null;
    private ?DatabaseExecutionInterface $execution = null;

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

            $this->connection = new PdoConnectionService($credentials);
            $this->execution = new PdoExecutionService($this->connection->connect());

            $this->printStatus("Credential object created and injected successfully.", 'OK');
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
            $pdo = $this->connection?->connect();
            $this->printStatus("Connection established. Driver: " . $this->connection?->getDriver(), 'OK');
            $this->saveResult('Connection establishment', true);
        } catch (\Throwable $e) {
            $this->printStatus("Connection failed.", 'ERROR');
            $this->saveResult('Connection establishment', false);
            $this->handleException($e);
        }
    }

    private function testQueryExecution(): void
    {
        $this->printStatus("STEP 3: Executing test query.", 'STEP');

        try {
            $result = $this->execution?->select("SELECT 1");
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
