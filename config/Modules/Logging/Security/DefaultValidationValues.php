<?php

declare(strict_types=1);

namespace Config\Modules\Logging\Security;

/**
 * Default validation parameters used when no custom configuration is provided.
 *
 * These values mirror the hard-coded defaults originally embedded in Validator.
 */
enum DefaultValidationValues
{
    case DEFAULT_STRING_MAX_LENGTH;
    case STRING_FORBIDDEN_CHARS_REGEX;
    case CONTEXT_KEY_MAX_LENGTH;
    case CONTEXT_VALUE_MAX_LENGTH;
    case CHANNEL_MAX_LENGTH;
    case DIRECTORY_ROOT_STRING;
    case DIRECTORY_TRAVERSAL_STRING;
    case LOG_MESSAGE_MAX_LENGTH;
    case LOG_MESSAGE_TERMINAL_PUNCTUATION_REGEX;

    /**
     * Returns the default value for this enum case.
     *
     * @return int|string
     */
    public function getValue(): int|string
    {
        return match ($this) {
            self::DEFAULT_STRING_MAX_LENGTH              => 255,
            self::STRING_FORBIDDEN_CHARS_REGEX           => '/[\x00-\x1F\x7F]/',
            self::CONTEXT_KEY_MAX_LENGTH                 => 128,
            self::CONTEXT_VALUE_MAX_LENGTH               => 256,
            self::DIRECTORY_ROOT_STRING                  => '/',
            self::DIRECTORY_TRAVERSAL_STRING             => '..',
            self::LOG_MESSAGE_MAX_LENGTH                 => 2000,
            self::LOG_MESSAGE_TERMINAL_PUNCTUATION_REGEX => '/[.!?]$/u',
            self::CHANNEL_MAX_LENGTH                     => 64,
        };
    }
}
