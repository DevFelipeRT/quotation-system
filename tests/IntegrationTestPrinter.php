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

    public function printOutput(
        $value,
        ?int $indentLevel = 0,
        string $status = 'OUTPUT',
        ?string $stepNumber = null,
        int $maxLine = 100
    ): void {
        ob_start();
        var_dump($value);
        $raw = rtrim(ob_get_clean(), "\r\n");

        // Define prefixos e larguras
        $statusTag    = strtoupper($status);
        $statusText   = ($status === 'STEP' && $stepNumber) ? "$statusTag $stepNumber" : $statusTag;
        $firstPrefix  = "[$statusText] | ";
        $otherPrefix  = str_repeat(' ', strlen($firstPrefix) - 2) . "| ";
        $spacesPerLevel = 2;

        $lines = explode("\n", $raw);

        // 1. Wrap manual, já considerando prefixo de cada linha!
        $wrapped = [];
        foreach ($lines as $lineIdx => $logicalLine) {
            $isFirstOfBlock = true;
            $str = $logicalLine;
            while (mb_strlen($str) > 0) {
                $prefix = ($lineIdx === 0 && $isFirstOfBlock) ? $firstPrefix : $otherPrefix;
                $width = $maxLine - ($indentLevel * $spacesPerLevel) - strlen($prefix);
                if ($width < 10) { // proteção para larguras absurdas
                    $width = 10;
                }
                if (mb_strlen($str) > $width) {
                    // Quebra preferencial em espaço antes do limite
                    $breakAt = mb_strrpos(mb_substr($str, 0, $width), ' ');
                    if ($breakAt === false || $breakAt < 1) {
                        $breakAt = $width;
                    }
                    $wrapped[] = [$prefix, mb_substr($str, 0, $breakAt)];
                    $str = ltrim(mb_substr($str, $breakAt));
                    $isFirstOfBlock = false;
                } else {
                    $wrapped[] = [$prefix, $str];
                    break;
                }
            }
        }

        // 2. Pós-processamento para evitar fecho isolado
        $closing = ['"', "'", '}', ']', ')'];
        $final = [];
        $i = 0;
        while ($i < count($wrapped)) {
            [$prefix, $line] = $wrapped[$i];
            // Se não é a última linha, e a próxima é só fechamento
            if (
                isset($wrapped[$i + 1]) &&
                mb_strlen(trim($wrapped[$i + 1][1])) === 1 &&
                in_array(trim($wrapped[$i + 1][1]), $closing, true)
            ) {
                $joined = rtrim($line) . trim($wrapped[$i + 1][1]);
                // Se cabe junto na linha, faça merge
                $width = $maxLine - ($indentLevel * $spacesPerLevel) - strlen($prefix);
                if (mb_strlen($joined) <= $width) {
                    $final[] = [$prefix, $joined];
                    $i += 2;
                    continue;
                }
            }
            $final[] = [$prefix, $line];
            $i++;
        }

        // 3. Imprimir
        foreach ($final as [$prefix, $outLine]) {
            $this->printIndented($prefix . $outLine . PHP_EOL, $indentLevel, $spacesPerLevel);
        }
    }
}