<?php

declare(strict_types=1);

namespace Logging\Infrastructure;

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
    private readonly LogSanitizerInterface $sanitizer;
    private readonly LogEntryAssemblerInterface $assembler;
    private readonly LoggerInterface $logger;
    private readonly PsrLoggerInterface $adapter;
    private readonly LoggingFacadeInterface $facade;
    private readonly LogFilePathResolver $pathResolver;
    private readonly LogFileWriter $writer;
    private readonly LogLineFormatter $formatter;

    /**
     * @param string $baseLogPath Directory where logs will be stored.
     * @param array|null $sanitizerConfig Optional: array with sanitizer custom keys/patterns/token.
     */
    public function __construct(
        string $baseLogPath,
        ?array $sanitizerConfig = null
    ) {
        $this->sanitizer = $this->createSanitizer($sanitizerConfig);
        $this->assembler = new LogEntryAssembler($this->sanitizer);
        $this->pathResolver = new LogFilePathResolver($baseLogPath);
        $this->writer = new LogFileWriter();
        $this->formatter = new LogLineFormatter();
        $this->logger = new Logger(
            $this->pathResolver,
            $this->formatter,
            $this->writer
        );
        $this->adapter = new PsrLoggerAdapter(
            $this->logger,
            $this->assembler
        );
        $this->facade = new LoggingFacade(
            $this->logger,
            $this->assembler,
            $this->adapter
        );
    }

    public function getAssembler(): LogEntryAssemblerInterface
    {
        return $this->assembler;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    public function getAdapter(): PsrLoggerAdapter
    {
        return $this->adapter;
    }

    public function getFacade(): LoggingFacadeInterface
    {
        return $this->facade;
    }

    /**
     * Creates the sanitizer with optional config.
     *
     * @param array|null $config
     * @return LogSanitizerInterface
     */
    private function createSanitizer(?array $config): LogSanitizerInterface
    {
        $customKeys = $config['sensitive_keys'] ?? null;
        $customPatterns = $config['sensitive_patterns'] ?? null;
        $maxDepth = $config['max_depth'] ?? null;
        $maskToken = $config['mask_token'] ?? null;
        return new LogSanitizer($customKeys, $customPatterns, $maxDepth, $maskToken);
    }
}
