<?php

namespace App\Infrastructure\Exceptions\Handlers;

use App\Infrastructure\Logging\LogEntry;
use App\Infrastructure\Logging\LogLevelEnum;
use App\Interfaces\Infrastructure\ExceptionHandlerInterface;
use App\Interfaces\Infrastructure\LoggerInterface;
use Throwable;

/**
 * ExceptionHandler
 *
 * Default exception handler that logs errors and responds appropriately
 * based on the application environment.
 */
final class ExceptionHandler implements ExceptionHandlerInterface
{
    private const LOG_CHANNEL = 'application.error';

    private LoggerInterface $logger;
    private string $environment;

    /**
     * Constructs the exception handler with a logger and environment flag.
     *
     * @param LoggerInterface $logger Logger used to record exceptions.
     * @param string $environment Application environment name (e.g. development, production).
     */
    public function __construct(LoggerInterface $logger, string $environment = 'production')
    {
        $this->logger = $logger;
        $this->environment = $environment;
    }

    /**
     * Handles an uncaught exception by logging and displaying an error response.
     *
     * @param Throwable $exception The exception to handle.
     */
    public function handle(Throwable $exception): void
    {
        $this->logger->log(new LogEntry(
            level: LogLevelEnum::ERROR,
            message: $exception->getMessage(),
            context: [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'code' => $exception->getCode(),
                'trace' => $exception->getTraceAsString(),
            ],
            channel: self::LOG_CHANNEL
        ));

        http_response_code(500);

        if ($this->environment === 'development') {
            $this->displayDetailedError($exception);
        } else {
            $this->displayFriendlyError();
        }

        exit;
    }

    /**
     * Displays a detailed error page with stack trace (development only).
     *
     * @param Throwable $exception The exception to render.
     */
    private function displayDetailedError(Throwable $exception): void
    {
        echo '<h1>Erro no Sistema</h1>';
        echo '<p><strong>Mensagem:</strong> ' . htmlspecialchars($exception->getMessage()) . '</p>';
        echo '<p><strong>Arquivo:</strong> ' . htmlspecialchars($exception->getFile()) . '</p>';
        echo '<p><strong>Linha:</strong> ' . (int) $exception->getLine() . '</p>';
        echo '<pre>' . htmlspecialchars($exception->getTraceAsString()) . '</pre>';
    }

    /**
     * Displays a generic error page (production).
     */
    private function displayFriendlyError(): void
    {
        include __DIR__ . '/../views/error.php';
    }
}
