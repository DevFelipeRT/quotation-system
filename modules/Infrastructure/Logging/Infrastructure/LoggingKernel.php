<?php

declare(strict_types=1);

namespace Logging\Infrastructure;

use Config\Modules\Logging\LoggingConfig;
use Logging\Domain\Security\LogSecurity;
use Logging\Domain\Security\Contract\LogSecurityInterface;
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
use Logging\Domain\Security\Sanitizer;
use Logging\Domain\Security\Validator;
use PublicContracts\Logging\AssemblerConfigInterface;
use PublicContracts\Logging\LoggingFacadeInterface;
use PublicContracts\Logging\SanitizationConfigInterface;
use PublicContracts\Logging\ValidationConfigInterface;

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
final class LoggingKernel
{
    private readonly string $baseLogPath;
    private readonly SanitizationConfigInterface $sanitizationConfig;
    private readonly ValidationConfigInterface $validationConfig;
    private readonly AssemblerConfigInterface $assemblerConfig;

    private readonly LogSecurityInterface $security;
    private readonly LogEntryAssemblerInterface $assembler;
    private readonly LoggerInterface $logger;
    private readonly PsrLoggerInterface $adapter;
    private readonly LoggingFacadeInterface $facade;
    private readonly LogFilePathResolver $pathResolver;
    private readonly LogFileWriter $writer;
    private readonly LogLineFormatter $formatter;

    /**
     * @param LoggingConfig $config
     */
    public function __construct(LoggingConfig $config)
    {
        $this->bootConfig($config);

        $this->security     = $this->createSecurity();
        $this->assembler    = $this->createAssembler();
        $this->pathResolver = new LogFilePathResolver($this->baseLogPath);
        $this->writer       = new LogFileWriter();
        $this->formatter    = new LogLineFormatter();
        $this->logger       = $this->createLogger();
        $this->adapter      = $this->createAdapter();
        $this->facade       = $this->createFacade();
    }

    public function logger(): LoggingFacadeInterface
    {
        return $this->facade;
    }

    public function psrLogger(): PsrLoggerInterface
    {
        return $this->adapter;
    }

    public function entryAssembler(): LogEntryAssemblerInterface
    {
        return $this->assembler;
    }

    public function rawLogger(): LoggerInterface
    {
        return $this->logger;
    }

    private function bootConfig(LoggingConfig $config): void
    {
        $this->baseLogPath   = $config->baseLogPath();
        $this->sanitizationConfig = $config->sanitizationConfig();
        $this->validationConfig = $config->validationConfig();
        $this->assemblerConfig = $config->assemblerConfig();
    }

    private function createSecurity(): LogSecurityInterface
    {
        $sanitizer = new Sanitizer($this->sanitizationConfig);
        $validator = new Validator($this->validationConfig);
        return new LogSecurity($validator, $sanitizer);
    }

    private function createAssembler(): LogEntryAssemblerInterface
    {
        return new LogEntryAssembler(
            $this->security, 
            $this->assemblerConfig
        );
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
