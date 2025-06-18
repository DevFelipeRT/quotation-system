<?php

declare(strict_types=1);

namespace Logging\Application;

use PublicContracts\Logging\LoggingFacadeInterface;
use PublicContracts\Logging\PsrLoggerInterface;
use Logging\Application\Contract\LoggerInterface;
use Logging\Application\Contract\LogEntryAssemblerInterface;
use Logging\Domain\ValueObject\Contract\LoggableInputInterface;
use Logging\Domain\ValueObject\LoggableInput;
use Stringable;

/**
 * LoggingFacade
 *
 * Orchestrates the complete logging cycle for the application layer.
 * Provides unified entry points for PSR-3 and structured logging.
 */
final class LoggingFacade implements LoggingFacadeInterface
{
    private LoggerInterface $logger;
    private LogEntryAssemblerInterface $assembler;
    private PsrLoggerInterface $adapter;

    /**
     * @param LoggerInterface $logger
     * @param LogEntryAssemblerInterface $assembler
     * @param PsrLoggerInterface $adapter
     */
    public function __construct(
        LoggerInterface $logger,
        LogEntryAssemblerInterface $assembler,
        PsrLoggerInterface $adapter
    ) {
        $this->logger = $logger;
        $this->assembler = $assembler;
        $this->adapter = $adapter;
    }

    /**
     * Assembles and logs from a generic loggable input.
     */
    public function logInput(
        string|Stringable $message, 
        ?string $level, 
        ?string $channel,
        ?array $context = [], 
    ): void {
        $input = $this->createInput(
            message:   $message,
            level:     $level,
            context:   $context,
            channel:   $channel,
        );
        $entry = $this->assembler->assembleFromInput($input);
        $this->logger->log($entry);
    }

    /**
     * Logs a message with PSR-3 compatible arguments via the adapter.
     */
    public function log(string $level, string|Stringable $message, array $context = []): void
    {
        $this->adapter->log($level, $message, $context);
    }

    public function emergency(string|Stringable $message, array $context = []): void
    {
        $this->adapter->emergency($message, $context);
    }

    public function alert(string|Stringable $message, array $context = []): void
    {
        $this->adapter->alert($message, $context);
    }

    public function critical(string|Stringable $message, array $context = []): void
    {
        $this->adapter->critical($message, $context);
    }

    public function error(string|Stringable $message, array $context = []): void
    {
        $this->adapter->error($message, $context);
    }

    public function warning(string|Stringable $message, array $context = []): void
    {
        $this->adapter->warning($message, $context);
    }

    public function notice(string|Stringable $message, array $context = []): void
    {
        $this->adapter->notice($message, $context);
    }

    public function info(string|Stringable $message, array $context = []): void
    {
        $this->adapter->info($message, $context);
    }

    public function debug(string|Stringable $message, array $context = []): void
    {
        $this->adapter->debug($message, $context);
    }

    private function createInput(
        string $level, 
        string|Stringable $message, 
        array $context, 
        string $channel
    ): LoggableInputInterface {
        return new LoggableInput(
            message:   $message,
            level:     $level,
            context:   $context,
            channel:   $channel,
        );
    }
}
