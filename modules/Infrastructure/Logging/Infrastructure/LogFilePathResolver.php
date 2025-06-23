<?php

declare(strict_types=1);

namespace Logging\Infrastructure;

use Logging\Domain\ValueObject\Contract\LogEntryInterface;
use Logging\Domain\ValueObject\LogDirectory;

/**
 * Responsible for resolving the absolute file path for a given log entry.
 *
 * Rules:
 *  - If a channel is present, the path is: <basePath>/<channel>/<level>.log
 *  - If no channel, the path is: <basePath>/<level>.log
 *  - This class does not create directories, it only computes the path.
 *
 * Examples:
 *  - Channel: "auth", Level: "error" => "/var/log/app/auth/error.log"
 *  - No channel, Level: "info"       => "/var/log/app/info.log"
 *
 * Usage: Inject into Logger to abstract away the logic of file path construction.
 */
final class LogFilePathResolver
{
    /**
     * @var LogDirectory
     */
    private LogDirectory $logDirectory;

    /**
     * @param LogDirectory $logDirectory Immutable, validated base log directory.
     */
    public function __construct(LogDirectory $logDirectory)
    {
        $this->logDirectory = $logDirectory;
    }

    /**
     * Resolves the absolute file path for the provided log entry.
     *
     * @param LogEntryInterface $entry
     * @return string Absolute, normalized path to the target log file.
     */
    public function resolve(LogEntryInterface $entry): string
    {
        $basePath = $this->logDirectory->getPath();
        $level = $entry->getLevel()->value();
        $channelObj = $entry->getChannel();

        if ($channelObj !== null) {
            $channel = trim($channelObj->value(), "/\\");
            $path = $basePath . DIRECTORY_SEPARATOR . $channel . DIRECTORY_SEPARATOR . $level . '.log';
        } else {
            $path = $basePath . DIRECTORY_SEPARATOR . $level . '.log';
        }

        return $this->normalizePath($path);
    }

    /**
     * Normalizes a filesystem path, collapsing redundant separators and removing trailing slashes,
     * except for root. Ensures consistent usage of DIRECTORY_SEPARATOR.
     *
     * @param string $path
     * @return string
     */
    private function normalizePath(string $path): string
    {
        $normalized = preg_replace('#[\\/\\\\]+#', DIRECTORY_SEPARATOR, $path);

        if (strlen($normalized) > 1) {
            $normalized = rtrim($normalized, DIRECTORY_SEPARATOR);
        }

        return $normalized;
    }
}
