<?php 

declare(strict_types=1);

namespace Tests;

use Config\ConfigProvider;

class IntegrationTestHelper
{
    public string $testName = '';
    public ?ConfigProvider $configProvider = null;
    public array $testResult = [];
    public array $testErrors = [];

    public function __construct(string $testName = '')
    {
        $this->testName = $testName;
        echo "<pre>";
        $this->printStatus("Starting test: $this->testName.", 'INFO');
        $this->setUp();
    }

    /**
     * Sets up the test environment, including loading the bootstrap file.
     * This method is called before each test to ensure the environment is ready.
     *
     * @return void
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
            $this->printStatus("Bootstrap failed. Subsequent tests might be affected or fail.", 'ERROR');
        }
    }

    /**
     * Prints a formatted status message.
     * If a step number is provided and the status is 'STEP', it formats as [STEP X].
     *
     * @param string $message     The message to print.
     * @param string $status      The status type (e.g., INFO, STEP, OK, ERROR).
     * @param ?int   $stepNumber  Optional step number, used if status is 'STEP'.
     * @param int    $indentLevel The number of indentation levels.
     * @return void
     */
    public function printStatus(string $message, string $status = 'INFO', ?string $stepNumber = null, ?int $indentLevel = 0): void
    {
        $statusTag = strtoupper($status);
        // Only prepend step number if status is 'STEP' and a number is provided
        if ($stepNumber !== null && $status === 'STEP') {
            $statusOutput = sprintf("%s %s", $statusTag, $stepNumber);
        } else {
            $statusOutput = $statusTag;
        }
        $string = sprintf("[%s] %s%s", $statusOutput, $message, PHP_EOL);
        $this->printIndented($string, $indentLevel);
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
     * Saves an exception that occurred during test execution.
     *
     * @param \Throwable $e The caught exception/error.
     * @return void
     */
    public function handleException(\Throwable $e): void
    {
        $this->testErrors[] = $e;
    }

    /**
     * Outputs alls exceptions that occurred during test execution.
     *
     * @return void
     */	
    public function printErrors(): void
    {
        echo PHP_EOL . "--- ERRORS ---" . PHP_EOL;
        if (empty($this->testErrors)) {
            $this->printStatus('No exception thrown.', 'INFO');
        }
        foreach ($this->testErrors as $e) {
            $this->printStatus("Exception occurred: " . get_class($e), 'ERROR_DETAIL');
            $this->printContext($e);
            echo "    [Message] {$e->getMessage()}\n";
            echo "    [File] {$e->getFile()}:{$e->getLine()}\n";
            echo "    [Trace] " . $e->getTraceAsString() . "\n";
            $this->saveResult("Exception: " . get_class($e), false);
        }
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
     * Prints a string with the specified indentation level.
     *
     * @param string $text            The text to be printed.
     * @param int    $indentLevel     The number of indentation levels.
     * @param int    $spacesPerLevel  The number of spaces per indentation level (default: 2).
     *
     * @return void
     */
    private function printIndented(string $text, int $indentLevel = 0, int $spacesPerLevel = 2): void
    {
        if ($indentLevel < 0) {
            $indentLevel = 0;
        }
        $indentation = str_repeat(' ', $indentLevel * $spacesPerLevel);
        echo $indentation . $text;
    }

    private function printContext(\Throwable $e): void
    {
        if (property_exists($e, 'context') && !empty($e->context)) {
            echo "    [Context] ";
            foreach ($e->context as $key => $value) {
                $parts[] = "{$key}: {$value}";
            }
            echo implode(', ', $parts) . PHP_EOL;
        }
    }
}