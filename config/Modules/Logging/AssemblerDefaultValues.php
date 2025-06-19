<?php

declare(strict_types=1);

namespace Config\Modules\Logging;

/**
 * AssemblerDefaultValues
 *
 * Provides default fallback values for log entry assembly.
 */
enum AssemblerDefaultValues
{
    case DEFAULT_LEVEL;
    case DEFAULT_CONTEXT;
    case DEFAULT_CHANNEL;
    case DEFAULT_MASK_TOKEN;

    /**
     * Returns the value associated with the enum case.
     *
     * @return string|array<string, string>|null
     */
    public function getValue(): string|array|null
    {
        return match ($this) {
            self::DEFAULT_LEVEL   => 'info',
            self::DEFAULT_CONTEXT => [],
            self::DEFAULT_CHANNEL => 'application',
            self::DEFAULT_MASK_TOKEN => '[SANITIZED_BY_CHANNEL]',
        };
    }
}
