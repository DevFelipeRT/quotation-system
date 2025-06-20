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
use Logging\Domain\ValueObject\LogEntry;

final class LoggingTest extends IntegrationTestHelper
{
    private ?LoggingConfigInterface $config = null;
    // Data
    private string $testLogDir;
    private ?string $resolvedPath = null;
    private ?string $formattedLogLine = null;
    private ?array $logFilePathCollection = null;
    private ?array $logLineCollection = null;
    // Value Objects
    private ?array $loggableInputCollection = null;
    private ?array $logEntryCollection = null;
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

    public function run(int $indentLevel = 0)
    {
        $this->runTests(
            'Logging Module',
            [
                'runVOsTest',
                'runComponentsTest',
                'runKernelTest',
            ],
            $indentLevel
        );
        $this->finalResult();
    }

    public function runVOsTest(int $indentLevel = 0)
    {
        $this->runTests(
            'Logging Module Value Objects',
            [
                'runLoggableInputTest'
            ],
            $indentLevel
        );
    }

    public function runComponentsTest(int $indentLevel = 0)
    {
        $this->runTests(
            'Logging Module Components',
            [
                'runLogSecurityTest',
            ],
            $indentLevel
        );
        $this->runSteps(
            'Components',
            [
                'LogEntryAssembler' => 'testLogEntryAssembler',
                'LogFilePathResolver' => 'testLogFilePathResolver',
                'LogLineFormatter' => 'testLogLineFormatter',
                'LogWriter' => 'testLogWriter',
                'Logger' => 'testLogger',
            ],
            $indentLevel
        );
    }
    
    public function runKernelTest(int $indentLevel = 0)
    {
        $this->runSteps(
            'Logging Module Kernel',
            [
                'Kernel Instantiation' => 'testKernelCreation',
                'Kernel Objects Retrieval' => 'testKernelObjectsRetrieval',
                'Log Writing from Kernel Objects' => 'testBasicLogWrite',
            ],
            $indentLevel
        );
    }

    // ---------- Value Object Tests -----------

    //  LoggableInput
    public function runLoggableInputTest(int $indentLevel = 0)
    {
        $this->runSteps(
            'Loggable Input value object intantiantion',
            [
                'Loggable Input basic intantiation' => 'testBasicLoggableInput',
                'Loggable Input complete intantiation' => 'testCompleteLoggableInput',
                'Loggable Input sensitive context intantiation' => 'testLoggableInputSensitiveContext',
                'Loggable Input sensitive channel intantiation' => 'testLoggableInputSensitiveChannel',
            ],
            $indentLevel
        );
    }

    public function testBasicLoggableInput()
    {
        $this->loggableInput = new LoggableInput('Testing Basic Loggable Input');
        $this->loggableInputCollection['basicLoggableInput'] = $this->loggableInput;
        //return $this->loggableInput;
    }
    
    public function testCompleteLoggableInput()
    {
        $this->loggableInput = new LoggableInput(
            'Testing Complete Loggable Input',
            'info',
            ['user' => 'tester'],
            'channel',
            new DateTimeImmutable()
        );
        $this->loggableInputCollection['completeLoggableInput'] = $this->loggableInput;
        //return $this->loggableInput;
    }

    public function testLoggableInputSensitiveContext()
    {
        $this->loggableInput = new LoggableInput(
            'Sensitive context data',
            'info',
            ['cpf' => '12345678900'],
            'channel',
            new DateTimeImmutable(),
        );
        $this->loggableInputCollection['loggableInputSensitiveContext'] = $this->loggableInput;
        //return $this->loggableInput;
    }

    public function testLoggableInputSensitiveChannel()
    {
        $this->loggableInput = new LoggableInput(
            'Sensitive channel data',
            'info',
            ['user' => 'tester'],
            'password',
            new DateTimeImmutable(),
        );
        $this->loggableInputCollection['loggableInputSensitiveChannel'] = $this->loggableInput;
        //return $this->loggableInput;
    }

    // ---------- Components Tests ----------
    
    // Security Component
    public function runLogSecurityTest(int $indentLevel = 0)
    {
        $this->runSteps(
            'Log Security Component',
            [
                'Security Validator' => 'testValidator',
                'Security Sanitizer' => 'testSanitizer',
                'Security LogSecurity' => 'testLogSecurity',
            ],
            $indentLevel
        );
    }

    public function testValidator()
    {
        $this->validator = new Validator($this->config->validationConfig());
        //return $this->validator;
    }

    public function testSanitizer()
    {
        $this->sanitizer = new Sanitizer($this->config->sanitizationConfig());
        //return $this->sanitizer;
    }

    public function testLogSecurity()
    {
        $this->security = new LogSecurity($this->validator, $this->sanitizer);
        //return $this->security;
    }

    // Components

    public function testLogEntryAssembler()
    {
        $this->assembler = new LogEntryAssembler($this->security, $this->config->assemblerConfig());

        foreach ($this->loggableInputCollection as $key => $value) {
            $this->logEntryCollection[$key] = $this->assembler->assembleFromInput($value);
        }

        $output = [];
        foreach ($this->logEntryCollection as $key => $object) {
            $output[$key] = $this->assertInstanceOf(
                LogEntry::class, $object,
                "Should return a LogEntry instance for {$key}."
            );
        }
        return $output;
    }

    public function testLogFilePathResolver()
    {
        $this->pathResolver = new LogFilePathResolver($this->testLogDir);

        foreach ($this->logEntryCollection as $key => $value) {
            $this->logFilePathCollection[$key] =  $this->pathResolver->resolve($value);
        }

        $output = [];
        foreach ($this->logFilePathCollection as $key => $value) {
            $output[$key] = $this->assertIsString($value, "Should return a string path for {$key}.") . " $value";
        }

        return $output;
    }   
    
    public function testLogLineFormatter()
    {
        $this->lineFormatter = new LogLineFormatter();
        
        foreach ($this->logEntryCollection as $key => $value) {
            $this->logLineCollection[$key] = $this->lineFormatter->format($value);
        }
        
        $output = [];
        foreach ($this->logLineCollection as $key => $value) {
            $output[$key] = $this->assertIsString($value, "Should return a string log line for {$key}.") . " $value";
        }

        return $output;
    }

    public function testLogWriter()
    {
        $this->fileWriter = new LogFileWriter();
        
        $array = array_combine($this->logFilePathCollection, $this->logLineCollection);
        $output = [];
        foreach ($array as $key => $value) {
            $this->fileWriter->write($key, $value);
            if (file_exists($key)) {
                $position = array_search($value, array_values($array)) + 1;
                $output[] = $this->assertTrue(file_exists($key), "Log file $position was created");
                $contents = file_get_contents($key);
                $output[] = $this->assertStringContains($value, $contents, 'Log file contains the log content.');
            } else {
                $output[] = $this->assertTrue(false, 'Log file was created');
                $this->saveResult('Cannot check log contents: file not found.', false);
            }
        }

        return $output;
    }

    // Infrastructure Service Method

    public function testLogger()
    {
        $this->logger = new Logger(
            $this->pathResolver,
            $this->lineFormatter,
            $this->fileWriter

        );

        $array = array_combine($this->logFilePathCollection, $this->logEntryCollection);
        $output = [];
        foreach ($array as $key => $value) {
            $this->logger->log($value);

            if (file_exists($key)) {
                $position = array_search($value, array_values($array)) + 1;
                $output[] = $this->assertTrue(file_exists($key), "Log file $position was created");
                $contents = file_get_contents($key);
                $output[] = $this->assertStringContains($this->lineFormatter->format($value), $contents, 'Log file contains the log content.');
            } else {
                $output[] = $this->assertTrue(false, 'Log file was created');
                $this->saveResult('Cannot check log contents: file not found.', false);
            }
        }

        return $output;
    }

    // Kernel Method

    public function testKernelCreation()
    {
        $this->kernel = new LoggingKernel($this->config);
    }

    public function testKernelObjectsRetrieval()
    {
        $this->facade = $this->kernel->logger();
        $this->assertInstanceOf(LoggingFacadeInterface::class, $this->facade, 'Kernel Facade retrieval');
    }

    public function testBasicLogWrite()
    {
        $facade = $this->facade;
        $level = 'info';
        $message = 'Test log entry';
        $context = ['user' => 'tester'];
        $now = new DateTimeImmutable();

        $facade->log($level, $message, $context);
        $facade->error($message, $context);

        $expectedFile = $this->testLogDir . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . $level . '.log';

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

    private function cleanupLogs()
    {
        if (is_dir($this->testLogDir)) {
            $this->deleteDir($this->testLogDir);
        }
    }

    private function deleteDir(string $dir)
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
