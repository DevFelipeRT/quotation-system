<?php

declare(strict_types=1);

namespace Logging\Infrastructure;

use Logging\Domain\ValueObject\Contract\LogEntryInterface;
use InvalidArgumentException;
use Logging\Infrastructure\Exception\InvalidLogBasePathException;

/**
 * Responsible for resolving the absolute file path for a given log entry.
 *
 * Rule:
 *  - If a channel is present, the path is: <basePath>/<channel>/<level>.log
 *  - If no channel, the path is: <basePath>/<level>.log
 *  - This class does not create directories, it only computes the path.
 *
 * Example:
 *  - Channel: "auth", Level: "error" => "/var/log/app/auth/error.log"
 *  - No channel, Level: "info"       => "/var/log/app/info.log"
 *
 * Usage: Inject into FileLogger to abstract away the logic of file path construction.
 */
final class LogFilePathResolver
{
    /**
     * @var string Base directory for log files. Always with trailing separator.
     */
    private string $basePath;

    /**
     * @param string $basePath Base directory where log files will be stored.
     * @throws InvalidArgumentException if basePath is invalid.
     */
    public function __construct(string $basePath)
    {
        $this->basePath = $this->validateAndNormalizeBasePath($basePath);
    }

    /**
     * Resolves the file path for the provided log entry.
     *
     * @param LogEntryInterface $entry
     * @return string Absolute path to the target log file, following the naming convention.
     */
    public function resolve(LogEntryInterface $entry): string
    {
        $level = $entry->getLevel()->value();
        $channelObj = $entry->getChannel();

        if ($channelObj !== null) {
            $channel = trim($channelObj->value(), "/\\");
            return $this->basePath . $channel . DIRECTORY_SEPARATOR . $level . '.log';
        }

        return $this->basePath . $level . '.log';
    }

    /**
     * Validates and normalizes the base path.
     *
     * @param string $path
     * @return string
     * @throws InvalidArgumentException if invalid.
     */
    private function validateAndNormalizeBasePath(string $path): string
    {
        $clean = trim($path);

        if ($clean === '') {
            throw InvalidLogBasePathException::empty();
        }
        if (preg_match('/[\x00-\x1F\x7F]/', $clean)) {
            throw InvalidLogBasePathException::invalidCharacters();
        }
        if (preg_match('/\.log$/i', $clean)) {
            throw InvalidLogBasePathException::notADirectory();
        }

        return rtrim($clean, '/\\') . DIRECTORY_SEPARATOR;
    }
}
