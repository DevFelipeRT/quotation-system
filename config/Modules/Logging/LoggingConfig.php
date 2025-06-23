<?php

declare(strict_types=1);

namespace Config\Modules\Logging;

use Config\Modules\Logging\Security\SanitizationConfig;
use Config\Modules\Logging\Security\ValidationConfig;
use PublicContracts\Logging\Config\AssemblerConfigInterface;
use PublicContracts\Logging\Config\LoggingConfigInterface;
use PublicContracts\Logging\Config\SanitizationConfigInterface;
use PublicContracts\Logging\Config\ValidationConfigInterface;

final class LoggingConfig implements LoggingConfigInterface
{
    private readonly string $baseLogDirectory;
    private readonly SanitizationConfigInterface $sanitizationConfig;
    private readonly ValidationConfigInterface $validationConfig;
    private readonly AssemblerConfigInterface $assemblerConfig;

    /**
     * @param string $loggingDirectory
     */
    public function __construct(string $loggingDirectory)
    {
        if (trim($loggingDirectory) === '') {
            throw new \InvalidArgumentException('Logging directory path cannot be empty.');
        }

        $this->baseLogDirectory = $loggingDirectory;
        $this->sanitizationConfig = new SanitizationConfig;
        $this->validationConfig = new ValidationConfig;
        $this->assemblerConfig = new AssemblerConfig;
    }

    /**
     * Returns the absolute base path for log storage.
     *
     * @return string
     */
    public function baseLogDirectory(): string
    {
        return $this->baseLogDirectory;
    }

    /**
     * Returns the associated sanitization configuration for logging.
     *
     * @return SanitizationConfig
     */
    public function sanitizationConfig(): SanitizationConfigInterface
    {
        return $this->sanitizationConfig;
    }

    /**
     * Returns the validation configuration for logging value objects.
     *
     * @return ValidationConfig
     */
    public function validationConfig(): ValidationConfigInterface
    {
        return $this->validationConfig;
    }
    
    /**
     * Returns the LogEntryAssembler configuration.
     *
     * @return assemblerConfig
     */
    public function assemblerConfig(): assemblerConfigInterface
    {
        return $this->assemblerConfig;
    }
}
