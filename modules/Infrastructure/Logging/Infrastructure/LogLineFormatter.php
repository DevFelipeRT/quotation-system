<?php

declare(strict_types=1);

namespace Logging\Infrastructure;

use Logging\Domain\ValueObject\Contract\LogEntryInterface;
use DateTimeInterface;

/**
 * Responsible for formatting a LogEntry as a single text line for output.
 * 
 */
final class LogLineFormatter
{
    /**
     * Converts the log entry into a human-readable log line.
     *
     * @param LogEntryInterface $entry
     * @return string
     */
    public function format(LogEntryInterface $entry): string
    {
        $timestamp = $entry->getTimestamp()->format(DateTimeInterface::ATOM);
        $level = $entry->getLevel()->value();
        $message = $entry->getMessage()->value();

        $contextObj = $entry->getContext();
        $context = $contextObj ? $contextObj->value() : [];

        $contextStr = !empty($context)
            ? ' | context: ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            : '';

        return "[{$timestamp}] [{$level}] {$message}{$contextStr}" . PHP_EOL;
    }
}
