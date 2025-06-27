<?php

declare(strict_types=1);

namespace Logging\Security\Validation\Services;

use PublicContracts\Logging\Config\ValidationConfigInterface;
use Logging\Domain\Exception\InvalidLogContextException;

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
    private int $maxKeyLength;
    private int $maxValueLength;
    private string $forbiddenCharsRegex;

    /**
     * @param ValidationConfigInterface $config Configuration provider for context validation.
     */
    public function __construct(ValidationConfigInterface $config)
    {
        $this->maxKeyLength        = $config->contextKeyMaxLength();
        $this->maxValueLength      = $config->contextValueMaxLength();
        $this->forbiddenCharsRegex = $config->stringForbiddenCharsRegex();
    }

    /**
     * Validates the top-level context array.
     *
     * This method orchestrates the validation process by iterating through the
     * top-level context array. It validates each key, checks for top-level
     * duplicates, and delegates the validation of each value to the
     * normalizeValue() dispatcher.
     *
     * @param array<string, mixed> $context The context array to validate.
     * @return array<string, mixed> The validated and normalized context array.
     * @throws InvalidLogContextException If validation fails.
     */
    public function validate(array $context): array
    {
        $result   = [];
        $seenKeys = [];

        foreach ($context as $key => $value) {
            $cleanKey = $this->validateKey($key, (string) $key);

            if (isset($seenKeys[$cleanKey])) {
                throw InvalidLogContextException::duplicateKey($cleanKey);
            }
            $seenKeys[$cleanKey] = true;

            $result[$cleanKey] = $this->normalizeValue($value, $cleanKey);
        }

        return $result;
    }

    /**
     * Validates the syntax and content of a context key.
     *
     * This method ensures that a given key is a string and complies with all
     * defined constraints, such as being non-empty and not exceeding a maximum
     * length or containing forbidden characters.
     *
     * @param mixed  $key  The key to validate.
     * @param string $path The full path to the key, used for precise error reporting.
     * @return string The cleaned and validated key.
     * @throws InvalidLogContextException If the key is not a string or is invalid.
     */
    private function validateKey($key, string $path): string
    {
        if (!is_string($key)) {
            throw InvalidLogContextException::invalidKeyType($key);
        }

        $cleanKey = trim($key);

        if ($cleanKey === '') {
            throw InvalidLogContextException::invalidKeyContent($path);
        }

        if (mb_strlen($cleanKey) > $this->maxKeyLength) {
            throw InvalidLogContextException::invalidKeyContent($path);
        }

        if (preg_match($this->forbiddenCharsRegex, $cleanKey)) {
            throw InvalidLogContextException::invalidKeyContent($path);
        }

        return $cleanKey;
    }

    /**
     * Dispatches the value to the appropriate normalizer based on its type.
     *
     * This method acts as a router, inspecting the value's type and delegating
     * the actual validation and normalization work to a specialized method
     * (e.g., normalizeScalar or normalizeArray).
     *
     * @param mixed  $value The value to normalize.
     * @param string $path  The key path to this value, for precise error reporting.
     * @return mixed The normalized value (string or array).
     * @throws InvalidLogContextException If the value type is unsupported.
     */
    private function normalizeValue($value, string $path)
    {
        switch (true) {
            case $value === null || is_scalar($value):
                return $this->normalizeScalar($value, $path);

            case is_array($value):
                return $this->normalizeArray($value, $path);

            default:
                throw InvalidLogContextException::invalidValueType($path, $value);
        }
    }

    /**
     * Normalizes and validates an array by processing its elements recursively.
     *
     * @param array<mixed> $array The array to normalize.
     * @param string       $path  The key path leading to this array.
     * @return array<string, mixed> The normalized array with validated keys and values.
     * @throws InvalidLogContextException If any nested key or value is invalid.
     */
    private function normalizeArray(array $array, string $path): array
    {
        $normalizedArray = [];
        foreach ($array as $key => $value) {
            $nestedPath = $path . '.' . $key;
            $cleanKey = $this->validateKey($key, $nestedPath);
            $normalizedArray[$cleanKey] = $this->normalizeValue($value, $nestedPath);
        }
        return $normalizedArray;
    }

    /**
     * Normalizes and validates a scalar value or null.
     *
     * @param scalar|null $scalar The value to normalize.
     * @param string      $path   The key path leading to this scalar.
     * @return string The validated and normalized string value.
     * @throws InvalidLogContextException If the value violates any content constraints.
     */
    private function normalizeScalar($scalar, string $path): string
    {
        $stringValue = (string) $scalar;

        $isConsideredEmpty = ($scalar !== null && $scalar !== false && $scalar !== 0) && empty($stringValue);

        if ($isConsideredEmpty) {
            throw InvalidLogContextException::invalidValueContent($path);
        }

        if (mb_strlen($stringValue) > $this->maxValueLength) {
            throw InvalidLogContextException::invalidValueContent($path);
        }

        if (preg_match($this->forbiddenCharsRegex, $stringValue)) {
            throw InvalidLogContextException::invalidValueContent($path);
        }

        return $stringValue;
    }
}
