<?php

declare(strict_types=1);

namespace PublicContracts\Logging;

/**
 * Provides configuration for LogEntryAssembler.
 *
 * Supplies default values and custom log levels for log entry construction.
 */
interface AssemblerConfigInterface
{
    /**
     * Returns the default log level to use when none is provided.
     *
     * @return string|null
     */
    public function defaultLevel(): ?string;

    /**
     * Returns the default context array to use when none is provided.
     *
     * @return array<string, string>|null
     */
    public function defaultContext(): ?array;

    /**
     * Returns the default channel to use when none is provided.
     *
     * @return string|null
     */
    public function defaultChannel(): ?string;

    /**
     * Returns the list of custom log levels accepted by LogLevel VO.
     *
     * @return string[]|null
     */
    public function customLogLevels(): ?array;
}
