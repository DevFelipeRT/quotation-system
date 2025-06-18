<?php

declare(strict_types=1);

namespace Config\Modules\Logging\Security;

use PublicContracts\Logging\Config\ValidationConfigInterface;

/**
 * Default implementation of ValidationConfigInterface backed by DefaultValidationValues enum.
 *
 * Uses compile-time constants defined in DefaultValidationValues to supply all
 * validation parameters for the logging domain.
 */
final class ValidationConfig implements ValidationConfigInterface
{
    public function defaultStringMaxLength(): int
    {
        return DefaultValidationValues::DEFAULT_STRING_MAX_LENGTH->getValue();
    }

    public function stringForbiddenCharsRegex(): string
    {
        return DefaultValidationValues::STRING_FORBIDDEN_CHARS_REGEX->getValue();
    }

    public function contextKeyMaxLength(): int
    {
        return DefaultValidationValues::CONTEXT_KEY_MAX_LENGTH->getValue();
    }

    public function contextValueMaxLength(): int
    {
        return DefaultValidationValues::CONTEXT_VALUE_MAX_LENGTH->getValue();
    }

    public function directoryRootString(): string
    {
        return DefaultValidationValues::DIRECTORY_ROOT_STRING->getValue();
    }

    public function directoryTraversalString(): string
    {
        return DefaultValidationValues::DIRECTORY_TRAVERSAL_STRING->getValue();
    }

    public function logMessageMaxLength(): int
    {
        return DefaultValidationValues::LOG_MESSAGE_MAX_LENGTH->getValue();
    }

    public function logMessageTerminalPunctuationRegex(): string
    {
        return DefaultValidationValues::LOG_MESSAGE_TERMINAL_PUNCTUATION_REGEX->getValue();
    }
}
