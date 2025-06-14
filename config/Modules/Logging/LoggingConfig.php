<?php

declare(strict_types=1);

namespace Config\Modules\Logging;

use Config\Paths\PathsConfig;

final class LoggingConfig
{
    private readonly string $baseLogPath;
    private readonly LogSecurityConfig $securityConfig;

    /**
     * LoggingConfig constructor.
     *
     * @param PathsConfig $pathsConfig Source for base log directory.
     * @param LogSecurityConfig $securityConfig Security configuration for logging (e.g. sanitizer config).
     */
    public function __construct(PathsConfig $pathsConfig)
    {
        $logPath = $pathsConfig->getLogsDirPath();

        if (trim($logPath) === '') {
            throw new \InvalidArgumentException('Base log path cannot be empty.');
        }

        $this->baseLogPath = $logPath;
        $this->securityConfig = new LogSecurityConfig();
    }

    /**
     * Returns the absolute base path for log storage.
     *
     * @return string
     */
    public function baseLogPath(): string
    {
        return $this->baseLogPath;
    }

    /**
     * Returns the associated security configuration for logging.
     *
     * @return LogSecurityConfig
     */
    public function logSecurityConfig(): LogSecurityConfig
    {
        return $this->securityConfig;
    }
}
