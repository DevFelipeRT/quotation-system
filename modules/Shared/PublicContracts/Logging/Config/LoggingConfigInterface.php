<?php

declare(strict_types=1);

namespace PublicContracts\Logging\Config;

/**
 * Contract for logging configuration objects.
 */
interface LoggingConfigInterface
{
    /**
     * Returns the absolute base path for log storage.
     *
     * @return string
     */
    public function baseLogPath(): string;

    /**
     * Returns the sanitization configuration for logging.
     *
     * @return SanitizationConfigInterface
     */
    public function sanitizationConfig(): SanitizationConfigInterface;

    /**
     * Returns the validation configuration for logging value objects.
     *
     * @return ValidationConfigInterface
     */
    public function validationConfig(): ValidationConfigInterface;

    /**
     * Returns the configuration for the LogEntryAssembler.
     *
     * @return AssemblerConfigInterface
     */
    public function assemblerConfig(): AssemblerConfigInterface;
}
