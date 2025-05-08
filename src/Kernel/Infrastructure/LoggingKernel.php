<?php

declare(strict_types=1);

namespace App\Kernel\Infrastructure;

/**
 * LoggingKernel
 *
 * Initializes structured logging infrastructure, including the logger
 * instance and log entry assembler. Designed to be composed by other
 * infrastructure kernels.
 */
final class LoggingKernel
{
    private readonly LoggerInterface $logger;
    private readonly LogEntryAssemblerInterface $logEntryAssembler;

    public function __construct(ConfigContainer $config)
    {
        $this->logger = new FileLogger(
            $config->getPathsConfig()->getLogsDirPath()
        );

        $this->logEntryAssembler = new LogEntryAssembler();
    }

    /**
     * Returns the logger service used for structured log persistence.
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Returns the assembler that transforms messages into log entries.
     */
    public function getLogEntryAssembler(): LogEntryAssemblerInterface
    {
        return $this->logEntryAssembler;
    }
}
