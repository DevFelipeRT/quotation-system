<?php 

declare(strict_types=1);

namespace Tests;

use Config\ConfigProvider;

class IntegrationTestHelper
{
    public string $testName = '';
    public ?ConfigProvider $configProvider = null;
    public array $testResult = [];

    public function __construct(string $testName = '')
    {
        $this->testName = $testName;
    }

    /**
     * Sets up the test environment, including loading the bootstrap file.
     * This method is called before each test to ensure the environment is ready.
     *
     * @return void
     */
    public function setUp(): void
    {
        echo "<pre>";
        $this->printStatus("Starting test: $this->testName.", 'INFO');
        try {
            $this->configProvider = require __DIR__ . '/test-bootstrap.php';
            $this->printStatus("Bootstrap executed successfully.", 'OK');
            $this->saveResult('Bootstrap execution', true);
        } catch (\Throwable $e) {
            $this->saveResult('Bootstrap execution', false);
            $this->handleException($e);
            $this->printStatus("Bootstrap failed. Subsequent tests might be affected or fail.", 'ERROR');
        }
    }

    /**
     * Prints a formatted status message.
     * If a step number is provided and the status is 'STEP', it formats as [STEP X].
     *
     * @param string $message The message to print.
     * @param string $status The status type (e.g., INFO, STEP, OK, ERROR).
     * @param ?int $stepNumber Optional step number, used if status is 'STEP'.
     * @return void
     */
    public function printStatus(string $message, string $status = 'INFO', ?string $stepNumber = null): void
    {
        $statusTag = strtoupper($status);
        // Only prepend step number if status is 'STEP' and a number is provided
        if ($stepNumber !== null && $status === 'STEP') {
            $statusOutput = sprintf("%s %s", $statusTag, $stepNumber);
        } else {
            $statusOutput = $statusTag;
        }
        echo sprintf("[%s] %s%s", $statusOutput, $message, PHP_EOL);
    }

    /**
     * Prints an immediate test result message.
     * This is often used for direct feedback, while saveResult() is used for the final summary.
     *
     * @param string $description Description of the check.
     * @param bool $result The outcome (true for OK, false for FAIL).
     * @param int $length Optional length for padding the description, default is 100.
     * @return void
     */
    public function printResult(string $description, bool $result, int $length = 100): void
    {
        echo str_pad($description, $length, '.') . ($result ? "OK\n" : "FAIL\n");
    }

    /**
     * Prints a summary of all accumulated test results.
     *
     * @param array<int, array{description: string, result: bool}> $results The array of test results.
     * @return void
     */
    public function printAll(array $results): void
    {
        echo PHP_EOL . "--- TEST SUMMARY ---" . PHP_EOL;
        foreach ($results as $result) {
            // Uses the printResult function for consistent formatting.
            $this->printResult($result['description'], $result['result']);
        }
    }

    /**
     * Saves a test result to the global collection for the final summary.
     *
     * @param string $description Description of the test.
     * @param bool $result The outcome of the test (true for OK, false for FAIL).
     * @return void
     */
    public function saveResult(string $description, bool $result): void
    {
        $this->testResult[] = ['description' => $description, 'result' => $result];
    }

    /**
     * Handles and prints details of an exception that occurred during test execution.
     *
     * @param \Throwable $e The caught exception/error.
     * @return void
     */
    public function handleException(\Throwable $e): void
    {
        $this->printStatus("Exception occurred: " . get_class($e), 'ERROR_DETAIL');
        echo "    [Message] {$e->getMessage()}\n";
        echo "    [File] {$e->getFile()}:{$e->getLine()}\n";
        echo "    [Trace] " . $e->getTraceAsString() . "\n";
        $this->saveResult("Exception: " . get_class($e), false);
    }

    /**
     * Outputs the final results of all tests after completion.
     *
     * @return void
     */	
    public function finalResult(): void
    {
        echo "\nAll tests finished.\n";
        $this->printAll($this->testResult);
        echo "</pre>";
    }
}