<?php

declare(strict_types=1);

namespace Logging\Infrastructure;

use DateTimeInterface;
use Logging\Application\Contract\LoggerInterface;
use Logging\Domain\Contract\LogEntryInterface;
use Logging\Exception\LogWriteException;

/**
 * FileLogger writes structured log entries to flat files in the local filesystem.
 *
 * This implementation assumes the LogEntryInterface has already been validated and sanitized.
 * Each log entry is persisted to a file based on its logical channel or log level.
 *
 * Typical usage includes simple production environments, container logs,
 * or fallback when no external logging service is available.
 *
 * Errors during write operations are logged to PHP's error_log and
 * trigger a LogWriteException to support higher-layer resilience.
 */
final class FileLogger implements LoggerInterface
{
    /**
     * @var string Base directory path for log files.
     */
    private string $basePath;

    /**
     * @param string $basePath Absolute or relative path where log files will be written.
     */
    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '/\\') . DIRECTORY_SEPARATOR;
    }

    /**
     * Logs a structured entry to the appropriate file.
     *
     * @param LogEntryInterface $entry Fully constructed and sanitized log entry.
     *
     * @throws LogWriteException If writing to disk fails.
     */
    public function log(LogEntryInterface $entry): void
    {
        $filepath = $this->resolveFilePath($entry);
        $line = $this->formatLogLine($entry);
        $this->persistLog($filepath, $line);
    }

    /**
     * Determines the target file based on channel or log level.
     *
     * @param LogEntryInterface $entry
     * @return string Full path to the log file.
     */
    private function resolveFilePath(LogEntryInterface $entry): string
    {
        $channel = $entry->getChannel() ?? $entry->getLevel()->value;
        $filename = "{$channel}.log";

        return $this->basePath . $filename;
    }

    /**
     * Converts the log entry into a human-readable log line.
     *
     * @param LogEntryInterface $entry
     * @return string
     */
    private function formatLogLine(LogEntryInterface $entry): string
    {
        $timestamp = $entry->getTimestamp()->format(DateTimeInterface::ATOM);
        $context = $entry->getContext();

        $contextStr = !empty($context)
            ? ' | context: ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            : '';

        return "[{$timestamp}] [{$entry->getLevel()->value}] {$entry->getMessage()}{$contextStr}" . PHP_EOL;
    }

    /**
     * Attempts to write the log line to disk.
     *
     * @param string $filepath
     * @param string $line
     *
     * @throws LogWriteException
     */
    private function persistLog(string $filepath, string $line): void
    {
        $written = @file_put_contents($filepath, $line, FILE_APPEND);

        if ($written === false) {
            error_log("Logging failure: could not write to file {$filepath}. Original log: {$line}");
            throw LogWriteException::cannotWrite($filepath);
        }
    }
}
