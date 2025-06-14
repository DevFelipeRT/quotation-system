<?php

declare(strict_types=1);

namespace Logging\Infrastructure;

use Logging\Infrastructure\Exception\LogWriteException;

/**
 * Responsible for writing log lines to a file on the local filesystem.
 *
 * Automatically creates the parent directory if it does not exist.
 * Does not handle formatting or file path resolution.
 */
final class LogFileWriter
{
    /**
     * Appends a log line to the specified file.
     * Ensures the parent directory exists before writing.
     *
     * @param string $filepath Absolute or relative path to the log file.
     * @param string $line     Log line to append (should include PHP_EOL).
     * @throws LogWriteException If writing to disk fails.
     */
    public function write(string $filepath, string $line): void
    {
        $this->ensureDirectoryExists($filepath);
        $this->appendLine($filepath, $line);
    }

    /**
     * Ensures the parent directory for the file exists, creating it if necessary.
     *
     * @param string $filepath
     * @throws LogWriteException If directory creation fails.
     */
    private function ensureDirectoryExists(string $filepath): void
    {
        $dir = dirname($filepath);

        if (!is_dir($dir)) {
            if (!@mkdir($dir, 0777, true) && !is_dir($dir)) {
                error_log("Logging failure: could not create directory {$dir} for log file.");
                throw LogWriteException::cannotWrite($filepath);
            }
        }
    }

    /**
     * Appends the provided line to the given file.
     *
     * @param string $filepath
     * @param string $line
     * @throws LogWriteException If writing fails.
     */
    private function appendLine(string $filepath, string $line): void
    {
        $result = @file_put_contents($filepath, $line, FILE_APPEND);

        if ($result === false) {
            error_log("Logging failure: could not write to file {$filepath}. Original log: {$line}");
            throw LogWriteException::cannotWrite($filepath);
        }
    }
}
