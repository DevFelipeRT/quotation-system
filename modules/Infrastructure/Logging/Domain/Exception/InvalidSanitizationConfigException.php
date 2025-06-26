<?php

declare(strict_types=1);

namespace Logging\Domain\Exception;

use InvalidArgumentException;

/**
 * Exception thrown when sanitizatiom configuration is invalid or unsafe.
 */
final class InvalidSanitizationConfigException extends InvalidArgumentException
{
    public static function forMaskToken(string $token): self
    {
        return new self("The mask token is invalid or unsafe: '{$token}'");
    }

    public static function forSensitiveKey(string $key): self
    {
        return new self("Invalid sensitive key provided for LogSanitizer: '{$key}'");
    }

    public static function forPattern(string $pattern): self
    {
        return new self("Invalid sensitive regular expression pattern: '{$pattern}'");
    }

    public static function forSeparator(string $separator): self
    {
        return new self(sprintf(
            'Invalid separator provided: [%s]. Each separator must be a non-empty string without whitespace.',
            $separator
        ));
    }
}
