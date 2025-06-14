<?php

declare(strict_types=1);

namespace Logging\Infrastructure;

use Config\Modules\Logging\LoggingConfig;
use Config\Modules\Logging\LogSecurityConfig;
use Logging\Domain\Security\LogSanitizer;
use Logging\Domain\Security\Contract\LogSanitizerInterface;
use Logging\Infrastructure\LogFileWriter;
use Logging\Infrastructure\LogFilePathResolver;
use Logging\Infrastructure\LogLineFormatter;
use Logging\Infrastructure\Logger;
use Logging\Infrastructure\LogEntryAssembler;
use Logging\Infrastructure\PsrLoggerAdapter;
use Logging\Application\Contract\LoggerInterface;
use Logging\Application\Contract\LogEntryAssemblerInterface;
use Logging\Application\Contract\PsrLoggerInterface;
use Logging\Application\LoggingFacade;
use PublicContracts\Logging\LoggingFacadeInterface;

/**
 * LoggingKernel
 *
 * Responsible for instantiating and wiring all logging components, ensuring
 * correct dependency injection and centralizing configuration for the logging module.
 *
 * Usage:
 *   $kernel = new LoggingKernel('/var/log/myapp');
 *   $logger = $kernel->getLogger();
 *   $facade = $kernel->getFacade();
 */
final class LoggingKernel
{
    /** @param string $baseLogPath Directory where logs will be stored. */
    private readonly string $baseLogPath;

    private readonly LogSecurityConfig $securityConfig;
    private readonly LogSanitizerInterface $sanitizer;
    private readonly LogEntryAssemblerInterface $assembler;
    private readonly LoggerInterface $logger;
    private readonly PsrLoggerInterface $adapter;
    private readonly LoggingFacadeInterface $facade;
    private readonly LogFilePathResolver $pathResolver;
    private readonly LogFileWriter $writer;
    private readonly LogLineFormatter $formatter;

    /**
     * 
     * @param LoggingConfig $config
     */
    public function __construct(LoggingConfig $config) {
        $this->baseLogPath = $config->baseLogPath();
        $this->securityConfig = $config->logSecurityConfig();

        $this->sanitizer = $this->createSanitizer($this->securityConfig);
        $this->assembler = new LogEntryAssembler($this->sanitizer);
        $this->pathResolver = new LogFilePathResolver($this->baseLogPath);
        $this->writer = new LogFileWriter();
        $this->formatter = new LogLineFormatter();
        $this->logger  = $this->createLogger();
        $this->adapter = $this->createAdapter();
        $this->facade  = $this->createFacade();
    }

    /**
     * Returns the main Logging Facade which orchestrates the entire logging lifecycle,
     * encapsulating the adapter, assembler, and logger services.
     *
     * This is the recommended entry point for production applications. The facade
     * provides a unified, high-level API that ensures every log passes through
     * structured assembly, validation, adaptation, and persistence.
     *
     * Use this for all general-purpose logging needs when you require consistency,
     * security, and feature completeness.
     */
    public function logger(): LoggingFacadeInterface
    {
        return $this->facade;
    }

    /**
     * Returns a PSR-3 compatible logger adapter.
     *
     * Ready for immediate use with any framework or library that requires a PSR-3
     * logger (e.g., Monolog, Laravel, Symfony). All calls are routed through the
     * domain-validated logging pipeline for consistency and security.
     */
    public function psrLogger(): PsrLoggerInterface
    {
        return $this->adapter;
    }

    /**
     * Returns the LogEntryAssembler responsible for building validated, sanitized
     * domain LogEntry objects from generic loggable input.
     *
     * Ready for direct use whenever you need to create domain LogEntry instances
     * from external commands or user input.
     */
    public function entryAssembler(): LogEntryAssemblerInterface
    {
        return $this->assembler;
    }

    /**
     * Returns the low-level file logger for writing fully structured log entries
     * directly to disk with no further transformation or adaptation.
     *
     * Use this when you require full control over log persistence, such as for
     * custom integrations, advanced scenarios, or testing. The logger expects
     * a fully validated LogEntry instantiated by a LogEntryAssembler.
     */
    public function rawLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Creates the sanitizer with custom security configuration.
     *
     * @param LogSecurityConfig $config
     * @return LogSanitizerInterface
     */
    private function createSanitizer(LogSecurityConfig $config): LogSanitizerInterface
    {
        $customKeys     = $config->sensitiveKeys();
        $customPatterns = $config->sensitivePatterns();
        $maxDepth       = $config->maxDepth();
        $maskToken      = $config->maskToken();

        return new LogSanitizer($customKeys, $customPatterns, $maxDepth, $maskToken);
    }

    /**
     * Instantiates the logger component (FileLogger).
     */
    private function createLogger(): LoggerInterface
    {
        return new Logger(
            $this->pathResolver,
            $this->formatter,
            $this->writer
        );
    }

    /**
     * Instantiates the PSR-3 logger adapter.
     */
    private function createAdapter(): PsrLoggerInterface
    {
        return new PsrLoggerAdapter(
            $this->logger,
            $this->assembler
        );
    }

    /**
     * Instantiates the logging facade that orchestrates all logging services.
     */
    private function createFacade(): LoggingFacadeInterface
    {
        return new LoggingFacade(
            $this->logger,
            $this->assembler,
            $this->adapter
        );
    }
}
