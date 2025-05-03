<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Infrastructure\Logging\LogEntry;
use App\Infrastructure\Logging\LogLevelEnum;
use App\Interfaces\Infrastructure\LoggerInterface;

/**
 * Class AbstractHttpController
 *
 * Base controller for HTTP interface layer.
 * Provides reusable methods for logging, redirection, and standardized responses.
 */
abstract class AbstractHttpController
{
    protected LoggerInterface $logger;

    /**
     * Sets the logger for this controller.
     *
     * @param LoggerInterface $logger Structured logging service.
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Logs an informational message with context.
     *
     * @param string $message
     * @param array<string, mixed> $context
     * @param string $channel
     *
     * @return void
     */
    protected function logInfo(string $message, array $context = [], string $channel = 'presentation'): void
    {
        $this->logger->log(new LogEntry(
            level: LogLevelEnum::INFO,
            message: $message,
            context: $context,
            channel: $channel
        ));
    }

    /**
     * Logs an error message with context.
     *
     * @param string $message
     * @param array<string, mixed> $context
     * @param string $channel
     *
     * @return void
     */
    protected function logError(string $message, array $context = [], string $channel = 'presentation'): void
    {
        $this->logger->log(new LogEntry(
            level: LogLevelEnum::ERROR,
            message: $message,
            context: $context,
            channel: $channel
        ));
    }

    /**
     * Issues a redirect response to the given URL and terminates execution.
     *
     * @param string $url
     *
     * @return void
     */
    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    /**
     * Sends an error response with HTTP status code and message.
     *
     * @param string $message
     * @param int $statusCode
     *
     * @return void
     */
    protected function respondWithError(string $message, int $statusCode = 400): void
    {
        http_response_code($statusCode);
        echo $message;
    }
}
