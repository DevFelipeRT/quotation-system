<?php

declare(strict_types=1);

namespace Tests;

use Config\ConfigProvider;

/**
 * Base class for integration and persistence tests.
 *
 * Provides environment bootstrap, result tracking, error reporting,
 * and integrates the IntegrationTestPrinter trait for all test output.
 */
class IntegrationTestHelper
{
    use TestAssertionTools;
    use IntegrationTestPrinter {
        IntegrationTestPrinter::printErrors as printErrorsFromTrait;
    }

    public string $testName = '';
    public ?ConfigProvider $configProvider = null;
    public array $testResults = [];
    public array $testErrors = [];
    public int $stepCounter = 1;

    /**
     * Initializes the test session and prepares the environment.
     *
     * @param string $testName A human-readable test name for reporting.
     */
    public function __construct(string $testName = '')
    {
        $this->testName = $testName;
        echo '<pre>';
        $this->printStatus("Starting test: {$this->testName}.", 'INFO');
        $this->setUp();
    }

    /**
     * Sets up the environment and loads configuration.
     */
    public function setUp(): void
    {
        try {
            $this->printStatus("Loading environment.", 'LOAD');
            $this->configProvider = require __DIR__ . '/test-bootstrap.php';
            $this->printStatus("Bootstrap executed successfully.", 'OK');
            $this->saveResult('Bootstrap execution', true);
            $this->printStatus('Environment setup complete.', 'END');
        } catch (\Throwable $e) {
            $this->saveResult('Bootstrap execution', false);
            $this->handleException($e);
            $this->printStatus("Bootstrap failed. Subsequent tests may fail.", 'ERROR');
        }
    }

    /**
     * Registers an exception for error reporting.
     *
     * @param \Throwable $e
     */
    public function handleException(\Throwable $e): void
    {
        $this->testErrors[] = $e;
    }

    /**
     * Stores a test result for summary reporting.
     *
     * @param string $description Description of the assertion.
     * @param bool   $result      Assertion outcome (true for pass).
     */
    public function saveResult(string $description, bool $result): void
    {
        $this->testResults[] = ['description' => $description, 'result' => $result];
        $status = $result ? 'OK' : 'FAIL';
    }

    /**
     * Prints a summary of all exceptions encountered using the trait's method.
     */
    public function printErrors(): void
    {
        $this->printErrorsFromTrait($this->testErrors);
    }

    /**
     * Prints the summary of all test results and errors.
     */
    public function finalResult(): void
    {
        $this->printStatus("All tests finished.", 'END');
        $this->printAll($this->testResults);
        $this->printErrors();
        echo "</pre>";
    }

    public function runTests(string $title, array $tests = [], int $indentLevel = 0): void
    {
        $this->printStatus("Starting {$title} tests.", 'RUN', null, $indentLevel);
        ++$indentLevel;

        $this->startStep();
        foreach ($tests as $test) {
            $this->{$test}($indentLevel, $this->getStep());
        }

        --$indentLevel;
        $this->printStatus("All {$title} tests finished.", 'END', null, $indentLevel);
    }

    public function runSteps(string $title, array $tests = [], int $indentLevel = 0): void
    {
        $this->printStatus("Starting {$title} tests.", 'RUN', null, $indentLevel);
        ++$indentLevel;

        $this->startStep();

        foreach ($tests as $key => $test) {
            if (is_string($key) && is_string($test)) {
                $stepTitle = $key;
                $callable = [$this, $test];
                $params = [];
            } elseif (is_string($test)) {
                $stepTitle = $test;
                $callable = [$this, $test];
                $params = [];
            } elseif (is_array($test) && isset($test['method'])) {
                $stepTitle = $test['title'] ?? $test['method'];
                $callable = [$this, $test['method']];
                $params = $test['params'] ?? [];
            } else {
                throw new \InvalidArgumentException('Invalid test definition');
            }

            $step = $this->getStep();

            $this->runStep($stepTitle, $callable, $params, $indentLevel, $step);
        }

        --$indentLevel;
        $this->printStatus("All {$title} tests finished.", 'END', null, $indentLevel);
    }

    public function runStep(string $title, callable $test, array $params = [], int $indentLevel = 0, ?string $step = null): void
    {
        $this->printStatus("Testing {$title}.", 'STEP', $step, $indentLevel);
        
        try {
            $output = $test(...$params);

            if ($output !== null) {
                ++$indentLevel;
                $this->printOutput($output, $indentLevel);
                --$indentLevel;
            }

            $this->printStatus("{$title} test succeeded.", 'OK', null, $indentLevel);
            $this->saveResult("{$title} test.", true);
        } catch (\Throwable $e) {
            $this->printStatus("{$title} test failed.", 'ERROR', null, $indentLevel);
            $this->saveResult("{$title} test.", false);
            $this->handleException($e);
        }
    }

    // -------------- Helper Methods --------------

    protected function startStep(int $startStep = 1): string
    {
        $this->stepCounter = $startStep;
        return (string)$this->stepCounter;
    }

    protected function getStep(): string
    {
        return (string)$this->stepCounter++;
    }
}
