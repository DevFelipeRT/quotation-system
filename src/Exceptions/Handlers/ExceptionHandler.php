<?php

declare(strict_types=1);

namespace App\Exceptions\Handlers;

use App\Exceptions\ExceptionHandlerInterface;
use App\Exceptions\Infrastructure\InfrastructureException;
use App\Logging\Domain\LogEntry;
use App\Logging\Domain\LogLevelEnum;
use App\Logging\LoggerInterface;
use App\Logging\Exceptions\LogWriteException;
use Throwable;

/**
 * Default exception handler that logs critical application errors and determines
 * the user-facing response based on the environment.
 */
final class ExceptionHandler implements ExceptionHandlerInterface
{
    private const LOG_CHANNEL = 'application.error';

    private LoggerInterface $logger;
    private string $environment;

    /**
     * @param LoggerInterface $logger      Logger used to record exception events.
     * @param string          $environment Application environment (e.g., 'development', 'production').
     */
    public function __construct(LoggerInterface $logger, string $environment = 'production')
    {
        $this->logger = $logger;
        $this->environment = $environment;
    }

    /**
     * Handles uncaught exceptions by logging and producing an appropriate response.
     *
     * @param Throwable $exception The thrown error or exception.
     */
    public function handle(Throwable $exception): void
    {
        try {
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
        } catch (LogWriteException $logError) {
            throw new InfrastructureException('Logging failure: ' . $logError->getMessage(), 0, $logError);
        }

        http_response_code(500);

        $this->environment === 'development'
            ? $this->displayDetailedError($exception)
            : $this->displayFriendlyError();

        exit;
    }

    /**
     * Renders a detailed error output for development environments.
     *
     * @param Throwable $exception The thrown error or exception.
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
     * Displays a friendly generic error page for production environments.
     */
    private function displayFriendlyError(): void
    {
        include __DIR__ . '/../views/error.php';
    }
}
