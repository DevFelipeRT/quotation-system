<?php

declare(strict_types=1);

namespace App\Kernel\Infrastructure;

use App\Infrastructure\Logging\Application\LogEntryAssembler;
use App\Infrastructure\Logging\Application\LogEntryAssemblerInterface;
use App\Infrastructure\Logging\Infrastructure\Contracts\LoggerInterface;
use App\Infrastructure\Logging\Infrastructure\Adapter\PsrLoggerAdapter;
use App\Infrastructure\Logging\Infrastructure\Contracts\PsrLoggerInterface;
use App\Infrastructure\Logging\Infrastructure\FileLogger;
use App\Infrastructure\Logging\Security\LogSanitizer;
use Config\ConfigProvider;

/**
 * Composes logging infrastructure for use in modular kernels, CLI, or background workers.
 *
 * Exposes the logger and entry assembler components,
 * encapsulating their initialization with optional override support.
 */
final class LoggingKernel
{
    private readonly string $logsDirPath;
    private readonly LoggerInterface $logger;
    private readonly LogSanitizer $logSanitizer;
    private readonly LogEntryAssemblerInterface $logEntryAssembler;
    private readonly PsrLoggerInterface $loggerAdapter;

    public function __construct(
        ConfigProvider $config,
        ?LoggerInterface $logger = null,
        ?LogSanitizer $sanitizer = null
    ) {
        $this->logsDirPath = $config->getPathsConfig()->getLogsDirPath();
        $this->logSanitizer = $sanitizer ?? new LogSanitizer();
        $this->logger = $logger ?? new FileLogger($this->logsDirPath);
        $this->logEntryAssembler = new LogEntryAssembler($this->logSanitizer);
        $this->loggerAdapter = new PsrLoggerAdapter($this->logger);
    }

    /**
     * Returns the internal logger instance.
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Returns the psr logger instance.
     * 
     */
    public function getPsrLogger(): PsrLoggerInterface
    {
        return $this->getLoggerAdapter('psr');
    }

    /**
     * Returns the assembler that transforms messages into structured log entries.
     */
    public function getLogEntryAssembler(): LogEntryAssemblerInterface
    {
        return $this->logEntryAssembler;
    }

    /**
     * Returns an adapted logger interface for external compatibility.
     *
     * @return PsrLoggerAdapter
     */
    public function getLoggerAdapter(): PsrLoggerAdapter
    {  
        return $this->loggerAdapter;
    }
}
