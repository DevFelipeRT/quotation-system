<?php

declare(strict_types=1);

namespace App\Kernel\Infrastructure;

use App\Infrastructure\Logging\Application\LogEntryAssembler;
use App\Infrastructure\Logging\Application\LogEntryAssemblerInterface;
use App\Infrastructure\Logging\Infrastructure\Contracts\LoggerInterface;
use App\Infrastructure\Logging\Infrastructure\FileLogger;
use App\Infrastructure\Logging\Security\LogSanitizer;
use Config\Container\ConfigContainer;

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

    public function __construct(
        ConfigContainer $config,
        ?LoggerInterface $logger = null,
        ?LogSanitizer $sanitizer = null
    ) {
        $this->logsDirPath = $config->getPathsConfig()->getLogsDirPath();

        $this->logSanitizer = $sanitizer ?? new LogSanitizer();
        $this->logger = $logger ?? new FileLogger($this->logsDirPath);
        $this->logEntryAssembler = new LogEntryAssembler($this->logSanitizer);
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
