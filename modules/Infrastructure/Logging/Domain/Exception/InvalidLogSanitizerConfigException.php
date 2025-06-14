<?php

declare(strict_types=1);

namespace Logging\Domain\Exception;

use InvalidArgumentException;

/**
 * Exception thrown when LogSanitizer configuration is invalid or unsafe.
 */
final class InvalidLogSanitizerConfigException extends InvalidArgumentException
{
    public static function forMaskToken(string $token): self
    {
        return new self("The mask token is invalid or unsafe for use in log sanitization: '{$token}'");
    }

    public static function forSensitiveKey(string $key): self
    {
        return new self("Invalid sensitive key provided for LogSanitizer: '{$key}'");
    }

    public static function forPattern(string $pattern): self
    {
        return new self("Invalid sensitive pattern provided for LogSanitizer: '{$pattern}'");
    }
}
