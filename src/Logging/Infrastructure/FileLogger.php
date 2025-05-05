<?php

namespace App\Logging\Infrastructure;

use App\Logging\LoggerInterface;
use App\Logging\Domain\LogEntry;
use App\Logging\Security\LogSanitizer;
use App\Logging\Exceptions\LogWriteException;
use DateTimeInterface;

/**
 * Logs structured entries to local files.
 *
 * Ensures secure logging with sanitized context, proper formatting,
 * and resilient fallback behavior on file write failure.
 */
final class FileLogger implements LoggerInterface
{
    private string $basePath;

    /**
     * @param string $basePath Directory where log files will be stored.
     */
    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '/\\') . DIRECTORY_SEPARATOR;
    }

    /**
     * Logs a structured log entry to a file, with secure context handling and fallback.
     *
     * @param LogEntry $entry The entry to log.
     * @throws LogWriteException If file write fails.
     */
    public function log(LogEntry $entry): void
    {
        $filepath = $this->resolveFilePath($entry);
        $line = $this->formatLogLine($entry);
        $this->persistLog($filepath, $line);
    }

    /**
     * Determines the log file path based on channel or log level.
     */
    private function resolveFilePath(LogEntry $entry): string
    {
        $channel = $entry->getChannel() ?? $entry->getLevel()->value;
        $filename = "{$channel}.log";

        return $this->basePath . $filename;
    }

    /**
     * Formats the log message line with timestamp and sanitized context.
     */
    private function formatLogLine(LogEntry $entry): string
    {
        $timestamp = $entry->getTimestamp()->format(DateTimeInterface::ATOM);
        $context = LogSanitizer::sanitize($entry->getContext());

        $contextStr = !empty($context)
            ? ' | context: ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            : '';

        return "[{$timestamp}] [{$entry->getLevel()->value}] {$entry->getMessage()}{$contextStr}" . PHP_EOL;
    }

    /**
     * Attempts to persist the log line to the file system.
     *
     * If it fails, it triggers error_log as a fallback and throws an exception.
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
