<?php

declare(strict_types=1);

namespace Tests;

/**
 * Trait IntegrationTestPrinter
 *
 * Provides reusable utilities for status printing, result summary,
 * error reporting, and context display in integration and persistence tests.
 */
trait IntegrationTestPrinter
{
    /**
     * Prints a formatted status message.
     * If a step number is provided and the status is 'STEP', it formats as [STEP X].
     *
     * @param string      $message     The message to print.
     * @param string      $status      The status type (e.g., INFO, STEP, OK, ERROR).
     * @param string|null $stepNumber  Optional step number, used if status is 'STEP'.
     * @param int|null    $indentLevel The number of indentation levels.
     * @return void
     */
    public function printStatus(string $message, string $status = 'INFO', ?string $stepNumber = null, ?int $indentLevel = 0): void
    {
        $statusTag = strtoupper($status);
        $statusOutput = ($stepNumber !== null && $status === 'STEP')
            ? sprintf("%s %s", $statusTag, $stepNumber)
            : $statusTag;
        $string = sprintf("[%s] %s%s", $statusOutput, $message, PHP_EOL);
        $this->printIndented($string, $indentLevel);
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
            $this->printResult($result['description'], $result['result']);
        }
    }

    /**
     * Prints an immediate test result message.
     *
     * @param string $description Description of the check.
     * @param bool $result The outcome (true for OK, false for FAIL).
     * @param int $length Optional length for padding the description.
     * @return void
     */
    public function printResult(string $description, bool $result, int $length = 100): void
    {
        echo str_pad($description, $length, '.') . ($result ? "OK\n" : "FAIL\n");
    }

    /**
     * Outputs all exceptions that occurred during test execution.
     *
     * @return void
     */
    public function printErrors(array $testErrors): void
    {
        echo PHP_EOL . "--- ERRORS ---" . PHP_EOL;
        if (empty($testErrors)) {
            $this->printStatus('No exceptions thrown.', 'INFO');
            return;
        }
        foreach ($testErrors as $e) {
            $this->printStatus("Exception occurred: " . get_class($e), 'ERROR_DETAIL');
            $this->printContext($e);

            echo "    [Message] {$e->getMessage()}" . PHP_EOL;
            echo "    [File] {$e->getFile()}:{$e->getLine()}" . PHP_EOL;
            echo "    [Trace]" . PHP_EOL;

            $trace = $e->getTrace();
            foreach ($trace as $i => $frame) {
                $file = $frame['file'] ?? '[internal function]';
                $line = $frame['line'] ?? '-';
                $function = $frame['function'] ?? '';
                $class = $frame['class'] ?? '';
                $type = $frame['type'] ?? '';
                $args = array_map(function ($arg) {
                    if (is_object($arg)) {
                        return get_class($arg);
                    } elseif (is_array($arg)) {
                        return 'Array';
                    } elseif (is_string($arg)) {
                        return '"' . $arg . '"';
                    } elseif (is_null($arg)) {
                        return 'NULL';
                    } elseif (is_bool($arg)) {
                        return $arg ? 'true' : 'false';
                    }
                    return (string)$arg;
                }, $frame['args'] ?? []);
                $argsString = implode(', ', $args);

                printf(
                    "        #%02d %s(%s): %s%s%s(%s)" . PHP_EOL,
                    $i,
                    $file,
                    $line,
                    $class,
                    $type,
                    $function,
                    $argsString
                );
            }
            printf("        #%02d {main}" . PHP_EOL, count($trace));
        }
    }

    /**
     * Prints a string with the specified indentation level.
     *
     * @param string $text            The text to be printed.
     * @param int    $indentLevel     The number of indentation levels.
     * @param int    $spacesPerLevel  The number of spaces per indentation level (default: 2).
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

    /**
     * Prints additional context if present on the exception.
     *
     * @param \Throwable $e
     * @return void
     */
    private function printContext(\Throwable $e): void
    {
        if (property_exists($e, 'context') && !empty($e->context)) {
            echo "    [Context] ";
            $parts = [];
            foreach ($e->context as $key => $value) {
                $parts[] = "{$key}: {$value}";
            }
            echo implode(', ', $parts) . PHP_EOL;
        }
    }
}
