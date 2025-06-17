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
    use IntegrationTestPrinter {
        IntegrationTestPrinter::printErrors as printErrorsFromTrait;
    }

    public string $testName = '';
    public ?ConfigProvider $configProvider = null;
    public array $testResults = [];
    public array $testErrors = [];

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

    public function runTestSafely(callable $testMethod): void
    {
        try {
            call_user_func($testMethod);
        } catch (\Throwable $e) {
            $this->handleException($e);
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
        $this->printStatus($description, $status);
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

    // -------------- Assertion Methods --------------

    /**
     * Asserts that a value is not null.
     */
    protected function assertNotNull($actual, string $message = ''): void
    {
        $description = $message !== '' ? $message : 'Assert value is not null.';
        $result = $actual !== null;
        $this->saveResult($description, $result);
    }

    /**
     * Asserts that a value is true.
     */
    protected function assertTrue($actual, string $message = ''): void
    {
        $description = $message !== '' ? $message : 'Assert value is true.';
        $result = $actual === true;
        $this->saveResult($description, $result);
    }

    /**
     * Asserts that a value is false.
     */
    protected function assertFalse($actual, string $message = ''): void
    {
        $description = $message !== '' ? $message : 'Assert value is false.';
        $result = $actual === false;
        $this->saveResult($description, $result);
    }

    /**
     * Asserts that two values are equal (==).
     */
    protected function assertEquals($expected, $actual, string $message = ''): void
    {
        $description = $message !== '' ? $message : 'Assert equality.';
        $result = $expected == $actual;
        $this->saveResult($description, $result);
    }

    /**
     * Asserts that a string contains a substring.
     */
    protected function assertStringContains(string $needle, string $haystack, string $message = ''): void
    {
        $description = $message !== '' ? $message : "Assert string contains '{$needle}'.";
        $result = strpos($haystack, $needle) !== false;
        $this->saveResult($description, $result);
    }

    /**
     * Asserts that a variable is an instance of the given class/interface.
     */
    protected function assertInstanceOf(string $expected, $actual, string $message = ''): void
    {
        $description = $message !== '' ? $message : "Assert instance of {$expected}.";
        $result = $actual instanceof $expected;
        $this->saveResult($description, $result);
    }

    /**
     * Asserts that a condition is true (alias for assertTrue).
     */
    protected function assert($condition, string $message = ''): void
    {
        $this->assertTrue($condition, $message);
    }
}
