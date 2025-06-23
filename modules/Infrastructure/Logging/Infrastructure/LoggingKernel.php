<?php

declare(strict_types=1);

namespace Logging\Infrastructure;

use PublicContracts\Logging\LoggingKernelInterface;
use PublicContracts\Logging\Config\LoggingConfigInterface;
use PublicContracts\Logging\Config\SanitizationConfigInterface;
use PublicContracts\Logging\Config\ValidationConfigInterface;
use PublicContracts\Logging\Config\AssemblerConfigInterface;
use PublicContracts\Logging\LoggingFacadeInterface;
use PublicContracts\Logging\PsrLoggerInterface;
use Logging\Domain\Security\Contract\LogSecurityInterface;
use Logging\Application\Contract\LogEntryAssemblerInterface;
use Logging\Application\Contract\LoggerInterface;
use Logging\Domain\Security\LogSecurity;
use Logging\Domain\Security\Sanitizer;
use Logging\Domain\Security\Validator;
use Logging\Application\LoggingFacade;
use Logging\Domain\ValueObject\LogDirectory;
use Logging\Infrastructure\LogFileWriter;
use Logging\Infrastructure\LogFilePathResolver;
use Logging\Infrastructure\LogLineFormatter;
use Logging\Infrastructure\Logger;
use Logging\Infrastructure\LogEntryAssembler;
use Logging\Infrastructure\PsrLoggerAdapter;

/**
 * LoggingKernel
 *
 * Responsible for instantiating and wiring all logging components, ensuring
 * correct dependency injection and centralizing configuration for the logging module.
 *
 * Usage:
 *   $config = new LoggingConfig('/var/log/myapp', ...);
 *   $kernel = new LoggingKernel($config);
 *   $logger = $kernel->logger();
 *   $facade = $kernel->logger();
 */
final class LoggingKernel implements LoggingKernelInterface
{
    // Config
    private readonly string $baseLogDirectory;
    private readonly SanitizationConfigInterface $sanitizationConfig;
    private readonly ValidationConfigInterface $validationConfig;
    private readonly AssemblerConfigInterface $assemblerConfig;
    // Components
    private readonly LogDirectory $logDirectory;
    private readonly LogSecurityInterface $security;
    private readonly LogEntryAssemblerInterface $assembler;
    private readonly LoggerInterface $logger;
    private readonly PsrLoggerInterface $adapter;
    private readonly LoggingFacadeInterface $facade;
    private readonly LogFilePathResolver $pathResolver;
    private readonly LogFileWriter $writer;
    private readonly LogLineFormatter $formatter;

    /**
     * @param LoggingConfigInterface $config
     */
    public function __construct(LoggingConfigInterface $config)
    {
        $this->bootConfig($config);
        $this->bootComponents();
    }

    public function logger(): LoggingFacadeInterface
    {
        return $this->facade;
    }

    public function psrLogger(): PsrLoggerInterface
    {
        return $this->adapter;
    }

    private function bootConfig(LoggingConfigInterface $config): void
    {
        $this->baseLogDirectory   = $config->baseLogDirectory();
        $this->sanitizationConfig = $config->sanitizationConfig();
        $this->validationConfig = $config->validationConfig();
        $this->assemblerConfig = $config->assemblerConfig();
    }

    private function bootComponents(): void
    {
        $this->security     = $this->createSecurity();
        $this->logDirectory = $this->createLogDirectory();
        $this->assembler    = $this->createAssembler();
        $this->pathResolver = $this->createLogFilePathResolver();
        $this->writer       = new LogFileWriter();
        $this->formatter    = new LogLineFormatter();
        $this->logger       = $this->createLogger();
        $this->adapter      = $this->createAdapter();
        $this->facade       = $this->createFacade();
    }

    private function createSecurity(): LogSecurityInterface
    {
        $sanitizer = new Sanitizer($this->sanitizationConfig);
        $validator = new Validator($this->validationConfig);
        return new LogSecurity($validator, $sanitizer);
    }

    private function createLogDirectory(): LogDirectory
    {
        return new LogDirectory($this->baseLogDirectory, $this->security);
    }

    private function createAssembler(): LogEntryAssemblerInterface
    {
        return new LogEntryAssembler(
            $this->security, 
            $this->assemblerConfig
        );
    }

    private function createLogFilePathResolver(): LogFilePathResolver
    {
        return new LogFilePathResolver($this->logDirectory);
    }

    private function createLogger(): LoggerInterface
    {
        return new Logger(
            $this->pathResolver,
            $this->formatter,
            $this->writer
        );
    }

    private function createAdapter(): PsrLoggerInterface
    {
        return new PsrLoggerAdapter(
            $this->logger,
            $this->assembler
        );
    }

    private function createFacade(): LoggingFacadeInterface
    {
        return new LoggingFacade(
            $this->logger,
            $this->assembler,
            $this->adapter
        );
    }
}
