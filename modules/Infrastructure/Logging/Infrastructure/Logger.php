<?php

declare(strict_types=1);

namespace Logging\Infrastructure;

use Logging\Application\Contract\LoggerInterface;
use Logging\Domain\ValueObject\Contract\LogEntryInterface;

/**
 * Logger writes structured log entries to flat files in the local filesystem.
 *
 * Orchestrates file path resolution, line formatting, and writing using injected services.
 * Each log entry is persisted to a file based on its channel (as a folder) or log level (as filename).
 */
final class Logger implements LoggerInterface
{
    private LogFilePathResolver $pathResolver;
    private LogLineFormatter $formatter;
    private LogFileWriter $writer;

    /**
     * @param LogFilePathResolver $pathResolver Responsible for resolving the log file path.
     * @param LogLineFormatter    $formatter    Responsible for formatting log lines.
     * @param LogFileWriter       $writer       Responsible for writing to log files.
     */
    public function __construct(
        LogFilePathResolver $pathResolver,
        LogLineFormatter $formatter,
        LogFileWriter $writer
    ) {
        $this->pathResolver = $pathResolver;
        $this->formatter    = $formatter;
        $this->writer       = $writer;
    }

    /**
     * Logs a structured entry to the appropriate file.
     *
     * @param LogEntryInterface $entry Fully constructed and sanitized log entry.
     */
    public function log(LogEntryInterface $entry): void
    {
        $filepath = $this->pathResolver->resolve($entry);
        $line     = $this->formatter->format($entry);
        $this->writer->write($filepath, $line);
    }
}
