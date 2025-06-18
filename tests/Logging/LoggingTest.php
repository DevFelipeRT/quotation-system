<?php

declare(strict_types=1);

namespace Tests\Persistence;

require __DIR__ . '/../test-bootstrap.php';

use Tests\IntegrationTestHelper;
use PublicContracts\Logging\Config\LoggingConfigInterface;
use PublicContracts\Logging\LoggingKernelInterface;
use PublicContracts\Logging\LoggingFacadeInterface;
use Logging\Application\Contract\LoggerInterface;
use Logging\Domain\Security\Contract\LogSecurityInterface;
use Logging\Domain\Security\Contract\SanitizerInterface;
use Logging\Domain\Security\Contract\ValidatorInterface;
use Logging\Domain\ValueObject\Contract\LoggableInputInterface;
use Logging\Domain\ValueObject\Contract\LogEntryInterface;
use Logging\Domain\Security\LogSecurity;
use Logging\Domain\Security\Sanitizer;
use Logging\Domain\Security\Validator;
use Logging\Domain\ValueObject\LoggableInput;
use Logging\Infrastructure\LoggingKernel;
use Logging\Infrastructure\LogEntryAssembler;
use Logging\Infrastructure\LogFilePathResolver;
use Logging\Infrastructure\LogFileWriter;
use Logging\Infrastructure\Logger;
use Logging\Infrastructure\LogLineFormatter;
use DateTimeImmutable;

final class LoggingTest extends IntegrationTestHelper
{
    private ?LoggingConfigInterface $config = null;
    // Data
    private string $testLogDir;
    private ?string $resolvedPath = null;
    private ?string $formattedLogLine = null;
    // Value Objects
    private ?LoggableInputInterface $loggableInput = null;
    private ?LogEntryInterface $logEntry = null;
    // Components
    private ?ValidatorInterface $validator = null;
    private ?SanitizerInterface $sanitizer = null;
    private ?LogSecurityInterface $security = null;
    private ?LogEntryAssembler $assembler = null;
    private ?LogFilePathResolver $pathResolver = null;
    private ?LogLineFormatter $lineFormatter = null;
    private ?LogFileWriter $fileWriter = null;
    private ?LoggerInterface $logger = null;
    // Public
    private ?LoggingKernelInterface $kernel = null;
    private ?LoggingFacadeInterface $facade = null;

    public function __construct()
    {
        parent::__construct('Logging Module Test');
        $this->testLogDir = __DIR__ . DIRECTORY_SEPARATOR . 'tmp';
        $this->config = $this->configProvider->loggingConfig($this->testLogDir);
        $this->cleanupLogs();
    }

    public function run(): void
    {
        $this->printStatus('Starting Logging Module tests.', 'RUN');

        $this->runVOsTest();
        $this->runComponentsTest();
        $this->runKernelTest();

        $this->printStatus("All Logging tests finished.", 'END');
        $this->finalResult();
    }

    public function runVOsTest(): void
    {
        $this->printStatus('Starting Logging Module value objects tests.', 'RUN');

        $this->testLoggableInput();

        $this->printStatus("All Logging value objects tests finished.", 'END');
    }

    public function runComponentsTest(): void
    {
        $this->printStatus('Starting Logging Module components tests.', 'RUN');

        $this->testLogSecurity();
        $this->testLogEntryAssembler();
        $this->testLogFilePathResolver();
        $this->testLogLineFormatter();
        $this->testLogWriter();
        $this->testLogger();

        $this->printStatus("All Logging components tests finished.", 'END');
    }
    
    public function runKernelTest(): void
    {
        $this->printStatus('Starting Logging Module kernel tests.', 'RUN');

        $this->testKernelCreation();
        $this->testKernelObjectsRetrieval();
        $this->testBasicLogWrite();

        $this->printStatus("All Logging kernel tests finished.", 'END');
    }

    // Public Value Object test

    private function testLoggableInput(): void
    {
        try {
            $this->loggableInput = new LoggableInput(
                'testing loggable input',
                'debug',
                ['password' => '1232dfsw'],
                'channel'
            );
        } catch (\Throwable $e) {
            $this->handleException($e);
        }
    }

    // Components Method

    private function testLogSecurity(): void
    {
        try {
            $this->validator = new Validator($this->config->validationConfig());
            $this->sanitizer = new Sanitizer($this->config->sanitizationConfig());
            $this->security = new LogSecurity($this->validator, $this->sanitizer);
        } catch (\Throwable $e) {
            $this->handleException($e);
        }
    }

    private function testLogEntryAssembler(): void
    {
        try {
            $this->assembler = new LogEntryAssembler($this->security, $this->config->assemblerConfig());
            $this->logEntry = $this->assembler->assembleFromInput($this->loggableInput);
        } catch (\Throwable $e) {
            $this->handleException($e);
        }
    }

    private function testLogFilePathResolver(): void
    {
        try {
            $this->pathResolver = new LogFilePathResolver($this->testLogDir);
            $this->resolvedPath = $this->pathResolver->resolve($this->logEntry);
        } catch (\Throwable $e) {
            $this->handleException($e);
        }
    }    
    
    private function testLogLineFormatter(): void
    {
        try {
            $this->lineFormatter = new LogLineFormatter();
            $this-> formattedLogLine = $this->lineFormatter->format($this->logEntry);
            echo "{$this-> formattedLogLine}";
        } catch (\Throwable $e) {
            $this->handleException($e);
        }
    }

    private function testLogWriter(): void
    {
        try {
            $this->fileWriter = new LogFileWriter();
            $this->fileWriter->write($this->resolvedPath, $this-> formattedLogLine);
        } catch (\Throwable $e) {
            $this->handleException($e);
        }
    }

    // Infrastructure Service Method

    private function testLogger(): void
    {
        try {
            $this->logger = new Logger(
                $this->pathResolver,
                $this->lineFormatter,
                $this->fileWriter

            );
            $this->logger->log($this->logEntry);
        } catch (\Throwable $e) {
            $this->handleException($e);
        }
    }

    // Kernel Method

    private function testKernelCreation(): void
    {
        $this->kernel = new LoggingKernel($this->config);
        $this->assertNotNull($this->kernel, 'Kernel instantiation');
    }

    private function testKernelObjectsRetrieval(): void
    {
        $this->facade = $this->kernel->logger();
        $this->assertInstanceOf(LoggingFacadeInterface::class, $this->facade, 'Facade retrieval');
    }

    private function testBasicLogWrite(): void
    {
        $facade = $this->facade;
        $level = 'info';
        $message = 'Test log entry';
        $context = ['user' => 'tester'];
        $now = new DateTimeImmutable();

        $facade->log($level, $message, $context);
        $facade->error($message, $context);

        $expectedFile = $this->testLogDir . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . $level . '.log';

        var_dump($expectedFile);

        if (file_exists($expectedFile)) {
        $this->assertTrue(file_exists($expectedFile), 'Log file was created');
        $contents = file_get_contents($expectedFile);
        $this->assertStringContains($message, $contents, 'Log file contains the message');
        $this->assertStringContains('user', $contents, 'Log file contains the context key');
        $this->assertStringContains((string)$now->format('Y'), $contents, 'Log file contains the timestamp');
        } else {
            $this->assertTrue(false, 'Log file was created');
            $this->saveResult('Cannot check log contents: file not found.', false);
        }
    }

    // Helper Methods

    private function cleanupLogs(): void
    {
        if (is_dir($this->testLogDir)) {
            $this->deleteDir($this->testLogDir);
        }
    }

    private function deleteDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $filePath = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($filePath)) {
                $this->deleteDir($filePath);
            } else {
                unlink($filePath);
            }
        }
        rmdir($dir);
    }
}

$test = new LoggingTest();
$test->run();
