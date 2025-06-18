<?php

declare(strict_types=1);

namespace PublicContracts\Logging;

/**
 * Contract for LoggingKernel, responsible for exposing the main logging components.
 */
interface LoggingKernelInterface
{
    /**
     * Returns the logging facade, which provides a unified entry point for all logging operations.
     *
     * @return LoggingFacadeInterface
     */
    public function logger(): LoggingFacadeInterface;

    /**
     * Returns a PSR-compliant logger instance, compatible with standard PSR logging interfaces.
     *
     * @return PsrLoggerInterface
     */
    public function psrLogger(): PsrLoggerInterface;
}
