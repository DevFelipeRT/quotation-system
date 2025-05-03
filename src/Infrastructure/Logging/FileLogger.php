<?php

namespace App\Infrastructure\Logging;

use App\Interfaces\Infrastructure\LoggerInterface;
use DateTimeInterface;

/**
 * Logs structured entries to local files.
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
     * {@inheritdoc}
     */
    public function log(LogEntry $entry): void
    {
        $level     = $entry->getLevel()->value;
        $channel   = $entry->getChannel() ?? $level;
        $filename  = "{$channel}.log";
        $filepath  = $this->basePath . $filename;
        $timestamp = $entry->getTimestamp()->format(DateTimeInterface::ATOM);

        $context   = LogSanitizer::sanitize($entry->getContext());

        $contextStr = !empty($context)
            ? ' | context: ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            : '';

        $line = "[{$timestamp}] [{$level}] {$entry->getMessage()}{$contextStr}" . PHP_EOL;

        file_put_contents($filepath, $line, FILE_APPEND);
    }
}