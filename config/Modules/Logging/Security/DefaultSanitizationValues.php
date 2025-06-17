<?php

declare(strict_types=1);

namespace Config\Modules\Logging\Security;

/**
 * Default values for sanitization settings in the logging domain.
 *
 * Provides only the default recursion depth and mask token,
 * since sensitive keys and patterns are defined elsewhere.
 */
enum DefaultSanitizationValues
{
    /** Default maximum recursion depth for nested sanitization. */
    case MAX_DEPTH;

    /** Default token used to mask sensitive values. */
    case MASK_TOKEN;

    /** Default regular expression pattern for forbidden characters or strings in the mask token. */
    case MASK_TOKEN_FORBIDDEN_PATTERN;

    /**
     * Returns the default value for this enum case.
     *
     * @return int|string
     */
    public function getValue(): int|string
    {
        return match ($this) {
            self::MAX_DEPTH   => 8,
            self::MASK_TOKEN  => '[MASKED]',
            self::MASK_TOKEN_FORBIDDEN_PATTERN => '/[\x00-\x1F\x7F]|base64|script|php/i'
        };
    }
}
