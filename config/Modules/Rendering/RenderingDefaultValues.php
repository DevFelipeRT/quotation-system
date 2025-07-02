<?php

declare(strict_types=1);

namespace Config\Modules\Rendering;

/**
 * Provides a centralized and type-safe source for default configuration values.
 *
 * This enum defines the available default settings. The corresponding string value
 * for each case is retrieved via the `getValue()` method.
 */
enum RenderingDefaultValues
{
    /**
     * The default name of the copyright holder.
     */
    case COPYRIGHT_OWNER;

    /**
     * The default copyright message text.
     */
    case COPYRIGHT_MESSAGE;

    /**
     * Retrieves the string value associated with the enum case.
     *
     * @return string
     */
    public function getValue(): string
    {
        return match ($this) {
            self::COPYRIGHT_OWNER   => 'My Company',
            self::COPYRIGHT_MESSAGE => 'All rights reserved.',
        };
    }
}