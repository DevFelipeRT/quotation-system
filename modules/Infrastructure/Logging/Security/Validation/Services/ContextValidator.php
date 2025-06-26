<?php

declare(strict_types=1);

namespace Logging\Security\Validation\Services;

use PublicContracts\Logging\Config\ValidationConfigInterface;
use Logging\Domain\Exception\InvalidLogContextException;
use Logging\Security\Validation\Tools\StringValidationTrait;

/**
 * ContextValidator
 *
 * Validates associative arrays used as log context.
 * Ensures that keys and values conform to domain constraints,
 * including type, forbidden characters, and length limits.
 * Throws domain-specific exceptions on validation failure.
 */
final class ContextValidator
{
    use StringValidationTrait;

    private ValidationConfigInterface $config;
    private int $maxKeyLength;
    private int $maxValueLength;
    private string $forbiddenCharsRegex;

    /**
     * @param ValidationConfigInterface $config Configuration provider for context validation.
     */
    public function __construct(ValidationConfigInterface $config)
    {
        $this->config = $config;
        $this->maxKeyLength       = $config->contextKeyMaxLength();
        $this->maxValueLength     = $config->contextValueMaxLength();
        $this->forbiddenCharsRegex = $config->stringForbiddenCharsRegex();
    }

    /**
     * Validates an associative context array for logging.
     *
     * Ensures that all keys are non-empty strings, do not exceed the maximum length,
     * and do not contain forbidden characters. Values must be scalar or null, string-castable,
     * and obey length and character constraints.
     *
     * @param array $context Associative array to validate.
     * @return array<string, string> Validated and normalized context array.
     *
     * @throws InvalidLogContextException If any key or value fails validation.
     */
    public function validate(array $context): array
    {
        $result   = [];
        $seenKeys = [];

        foreach ($context as $key => $value) {
            // Key must be string
            if (!is_string($key)) {
                throw InvalidLogContextException::invalidKeyType($key);
            }

            $cleanKey = $this->cleanString($key, true);

            // Key: empty, forbidden, length, duplicates
            if (
                $this->isEmpty($cleanKey) ||
                $this->exceedsMaxLength($cleanKey, $this->maxKeyLength) ||
                $this->hasForbiddenChars($cleanKey, $this->forbiddenCharsRegex)
            ) {
                throw InvalidLogContextException::invalidKeyContent($key);
            }
            if (isset($seenKeys[$cleanKey])) {
                throw InvalidLogContextException::duplicateKey($cleanKey);
            }
            $seenKeys[$cleanKey] = true;

            // Value must be scalar or null
            if (!is_scalar($value) && $value !== null) {
                throw InvalidLogContextException::invalidValueType($cleanKey, $value);
            }

            $strVal = (string)$value;
            $isEmptyString = $this->isEmpty($strVal) && $value !== 0 && $value !== false;

            if (
                $isEmptyString ||
                $this->exceedsMaxLength($strVal, $this->maxValueLength) ||
                $this->hasForbiddenChars($strVal, $this->forbiddenCharsRegex)
            ) {
                throw InvalidLogContextException::invalidValueContent($cleanKey);
            }

            $result[$cleanKey] = $strVal;
        }

        return $result;
    }
}
