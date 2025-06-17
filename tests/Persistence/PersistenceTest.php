<?php

declare(strict_types=1);

namespace Tests\Persistence;

require __DIR__ . '/../test-bootstrap.php';

use Tests\IntegrationTestHelper;
use Config\Database\DatabaseConfig;
use Persistence\Infrastructure\Contract\DatabaseConnectionInterface;
use Persistence\Infrastructure\Contract\DatabaseExecutionInterface;
use Persistence\Domain\Contract\QueryInterface;
use Persistence\Domain\Contract\DatabaseCredentialsInterface;
use Persistence\Domain\ValueObject\MySqlCredentials;
use Persistence\Domain\ValueObject\SqliteCredentials;
use Persistence\Domain\ValueObject\PostgreSqlCredentials;
use Persistence\Domain\ValueObject\DatabaseSecret;
use Persistence\Infrastructure\PersistenceKernel;
use Persistence\Infrastructure\QueryBuilder;
use Persistence\Infrastructure\PdoConnectionService;
use Persistence\Infrastructure\PdoExecutionService;
use PDO;

final class PersistenceTest extends IntegrationTestHelper
{
    private ?DatabaseConfig $config = null;
    private ?DatabaseCredentialsInterface $credentials = null;
    private ?DatabaseConnectionInterface $connection = null;
    private ?DatabaseExecutionInterface $execution = null;
    private ?QueryBuilder $builder = null;
    private ?PersistenceKernel $kernel = null;
    private ?QueryInterface $query = null;
    private ?PDO $pdo = null;

    public function __construct()
    {
        parent::__construct('Persistence Module Test');
        $this->config = $this->configProvider->getDatabaseConfig();
    }

    public function run(): void
    {
        $this->printStatus('Starting Persistence Module tests.', 'RUN');
        $this->runComponentsMethodTest(1);
        $this->runKernelMethodTest(1);
        $this->printStatus('All Persistence Module tests completed.', 'END');
        $this->finalResult();
    }

    public function runComponentsMethodTest(int $indentation): void
    {
        $this->printStatus('Starting components method test.', 'TEST', null, $indentation);
        $this->testCredentialCreation('1', $indentation + 1);
        $this->testConnectionService('2', $indentation + 1);
        $this->testConnectionEstablishment('3', $indentation + 1);
        $this->testExecutionService('4', $indentation + 1);
        $this->testQueryBuilder('5', $indentation + 1);
        $this->testQueryBuilding('6', $indentation + 1);
        $this->testQueryExecution('7', $indentation + 1);
        $this->testPersistenceEvents('8', $indentation + 1);
        $this->printStatus('All components method tests completed.', 'END', null, $indentation);
    }

    public function runKernelMethodTest(int $indentation): void
    {
        $this->printStatus('Starting kernel method test.', 'TEST', null, $indentation);
        $this->testKernel('1', $indentation + 1);
        $this->testConnectionEstablishment('2', $indentation + 1);
        $this->testQueryBuilding('3', $indentation + 1);
        $this->testQueryExecution('4', $indentation + 1);
        $this->testKernelFacade('5', $indentation + 1);
        $this->printStatus('All kernel method tests completed.', 'END', null, $indentation);
    }

    private function testCredentialCreation(string $stepNumber, int $indentation): void
    {
        $this->printStatus('Creating credentials based on configured driver.', 'STEP', $stepNumber, $indentation);

        try {
            $driver = $this->config->getDriver();
            $this->printStatus("Using driver: $driver", 'INFO', null, $indentation);

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

            $this->printStatus("Credential object created successfully.", 'OK', null, $indentation);
            $this->saveResult('Credential creation', true);
        } catch (\Throwable $e) {
            $this->printStatus("Failed to create credential object.", 'ERROR', null, $indentation);
            $this->saveResult('Credential creation', false);
            $this->handleException($e);
        }
    }

    private function testConnectionService(string $stepNumber, int $indentation): void
    {
        $this->printStatus('Testing connection service instantiation.', 'STEP', $stepNumber, $indentation);

        try {
            $this->connection = new PdoConnectionService($this->credentials);
            $this->printStatus("Connection service instantiated.", 'OK', null, $indentation);
            $this->saveResult('Connection service instantiation', true);
        } catch (\Throwable $e) {
            $this->printStatus("Connection service instantiation failed.", 'ERROR', null, $indentation);
            $this->saveResult('Connection service instantiation', false);
            $this->handleException($e);
        }
    }

    private function testConnectionEstablishment(string $stepNumber, int $indentation): void
    {
        $this->printStatus('Testing PDO connection establishment.', 'STEP', $stepNumber, $indentation);

        try {
            $this->pdo = $this->connection?->connect();
            $this->printStatus("Connection established. Driver: " . $this->connection?->getDriver(), 'OK', null, $indentation);
            $this->saveResult('Connection establishment', true);
        } catch (\Throwable $e) {
            $this->printStatus("Connection failed.", 'ERROR', null, $indentation);
            $this->saveResult('Connection establishment', false);
            $this->handleException($e);
        }
    }

    private function testExecutionService(string $stepNumber, int $indentation): void
    {
        $this->printStatus('Testing execution service instantiation.', 'STEP', $stepNumber, $indentation);

        try {
            $this->execution = new PdoExecutionService($this->pdo);
            $this->printStatus("Execution service instantiated.", 'OK', null, $indentation);
            $this->saveResult('Execution service instantiation', true);
        } catch (\Throwable $e) {
            $this->printStatus("Execution service instantiation failed.", 'ERROR', null, $indentation);
            $this->saveResult('Execution service instantiation', false);
            $this->handleException($e);
        }
    }

    private function testQueryBuilder(string $stepNumber, int $indentation): void
    {
        $this->printStatus('Testing query builder instantiation.', 'STEP', $stepNumber, $indentation);

        try {
            $this->builder = new QueryBuilder();
            $this->printStatus("Query builder instantiated.", 'OK', null, $indentation);
            $this->saveResult('Query builder instantiation', true);
        } catch (\Throwable $e) {
            $this->printStatus("Query builder instantiation failed.", 'ERROR', null, $indentation);
            $this->saveResult('Query builder instantiation', false);
            $this->handleException($e);
        }
    }

    private function testQueryBuilding(string $stepNumber, int $indentation): void
    {
        $this->printStatus('Testing Query building.', 'STEP', $stepNumber, $indentation);
        try {
            $this->query = $this->builder
                ->selectRaw('1 as test_value')
                ->table('DUAL')
                ->build();
            ;
            $this->printStatus("Query built. Result: " . json_encode($this->query->getSql()), 'OK', null, $indentation);
            $this->saveResult('Query building.', true);
        } catch (\Throwable $e) {
            $this->printStatus("Query building failed.", 'ERROR', null, $indentation);
            $this->saveResult('Query building', false);
            $this->handleException($e);
        }
    }

    private function testQueryExecution(string $stepNumber, int $indentation): void
    {
        $this->printStatus('Testing query execution.', 'STEP', $stepNumber, $indentation);

        try {
            $this->execution = new PdoExecutionService($this->pdo);
            $result = $this->execution?->execute($this->query);
            $this->printStatus("Query executed. Result: " . json_encode($result), 'OK', null, $indentation);
            $this->saveResult('Query execution', true);
        } catch (\Throwable $e) {
            $this->printStatus("Query failed to execute.", 'ERROR', null, $indentation);
            $this->saveResult('Query execution', false);
            $this->handleException($e);
        }
    }

    private function testKernel(string $stepNumber, int $indentation): void
    {
        $this->printStatus('Executing kernel test.', 'STEP', $stepNumber, $indentation);

        try {
            $this->kernel = new PersistenceKernel($this->config);
            $this->connection = $this->kernel->connectionService();
            $this->execution = $this->kernel->executionService();
            $this->builder = $this->kernel->builder();
            $this->printStatus('Kernel test executed.','OK', null, $indentation);
            $this->saveResult('Kernel test', true);
        } catch (\Throwable $e) {
            $this->printStatus("Kernel test failed to execute.", 'ERROR', null, $indentation);
            $this->saveResult('Kernel test', false);
            $this->handleException($e);
        }
    }

    private function testKernelFacade(string $stepNumber, int $indentation): void
    {
        $this->printStatus('Executing kernel facade test.', 'STEP', $stepNumber, $indentation);

        try {
            $facade = $this->kernel->facade();
            $this->builder = $facade->queryBuilder();
            $this->testQueryBuilding("$stepNumber.1", $indentation + 1);
            $this->testQueryExecution("$stepNumber.2", $indentation + 1);
            $this->printStatus('Kernel facade test executed.','OK', null, $indentation);
            $this->saveResult('Kernel facade test', true);
        } catch (\Throwable $e) {
            $this->printStatus("Kernel facade test failed to execute.", 'ERROR', null, $indentation);
            $this->saveResult('Kernel facade test', false);
            $this->handleException($e);
        }
    }

    private function testPersistenceEvents(string $stepNumber, int $indentation): void
    {
        $this->printStatus('Executing Persistence module events test.', 'STEP', $stepNumber, $indentation);

        try {
            $events = $this->connection->peekEvents();
            var_dump($events);

            $this->printStatus('Kernel test executed.','OK', null, $indentation);
            $this->saveResult('Kernel test', true);
        } catch (\Throwable $e) {
            $this->printStatus("Kernel test failed to execute.", 'ERROR', null, $indentation);
            $this->saveResult('Kernel test', false);
            $this->handleException($e);
        }
    }
}

$test = new PersistenceTest();
$test->run();
