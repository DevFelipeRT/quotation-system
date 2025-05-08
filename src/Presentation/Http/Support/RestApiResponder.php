<?php

declare(strict_types=1);

namespace App\Presentation\Http\Support;

use Throwable;

/**
 * RestApiResponder
 *
 * Base utility for controllers that serve RESTful or external HTTP interfaces.
 *
 * Provides standardized helpers for emitting HTTP responses (redirects, plain text),
 * and for logging structured diagnostics using the application's logging strategy.
 *
 * Suitable for use in REST APIs, webhooks, or lightweight endpoints that do not
 * rely on templating, sessions, or request routing infrastructure.
 */
abstract class RestApiResponder
{
    protected LoggerInterface $logger;
    protected LogEntryAssemblerInterface $logAssembler;

    public function __construct(
        LoggerInterface $logger,
        LogEntryAssemblerInterface $logAssembler
    ) {
        $this->logger = $logger;
        $this->logAssembler = $logAssembler;
    }

    /**
     * Emits a redirect response and terminates execution.
     */
    protected function redirectTo(string $url): void
    {
        if (!headers_sent()) {
            header("Location: {$url}", true, 302);
        }
        exit;
    }

    /**
     * Emits a plain text HTTP error response.
     */
    protected function sendPlainTextErrorResponse(string $message, int $statusCode = 400): void
    {
        if (!headers_sent()) {
            http_response_code($statusCode);
            header('Content-Type: text/plain; charset=utf-8');
        }
        echo $message;
        exit;
    }

    /**
     * Logs an informational message using structured logging.
     */
    protected function logInfoMessage(string $text, array $context = [], string $channel = 'presentation'): void
    {
        $this->writeStructuredLog('INFO', $text, $context, $channel);
    }

    /**
     * Logs an error message using structured logging.
     */
    protected function logErrorMessage(string $text, array $context = [], string $channel = 'presentation'): void
    {
        $this->writeStructuredLog('ERROR', $text, $context, $channel);
    }

    /**
     * Logs a throwable as an error with contextual trace.
     */
    protected function logUnexpectedException(Throwable $e, string $channel = 'presentation'): void
    {
        $this->logErrorMessage('Unhandled exception', [
            'exception' => $e->getMessage(),
            'trace'     => $e->getTraceAsString(),
        ], $channel);
    }

    /**
     * Internal helper for logging messages consistently.
     */
    private function writeStructuredLog(string $level, string $message, array $context, string $channel): void
    {
        $structured = LoggableMessage::$level($message, $context)->withChannel($channel);
        $entry = $this->logAssembler->assembleFromMessage($structured);
        $this->logger->log($entry);
    }
}
