<?php

declare(strict_types=1);

namespace Logging\Security\Sanitizing\Service;

use InvalidArgumentException;
use Logging\Domain\Security\Contract\SanitizerInterface;
use Logging\Security\Sanitizing\Contract\ArraySanitizerInterface;
use Logging\Security\Sanitizing\Contract\ObjectSanitizerInterface;
use Logging\Security\Sanitizing\Contract\StringSanitizerInterface;
use Logging\Security\Sanitizing\Contract\SensitivePatternDetectorInterface;
use Logging\Security\Sanitizing\Contract\SensitiveKeyDetectorInterface;
use Logging\Security\Sanitizing\Tools\MaskTokenValidator;

/**
 * SanitizingService
 *
 * Orchestrates all sanitization logic within the logging domain.
 * Provides centralized entry points for recursive sanitization and sensitivity detection.
 * Internal routing is based on the input type and delegates to specialized collaborators.
 */
final class SanitizingService implements SanitizerInterface
{
    /**
     * Internal default token used to mask sensitive data.
     */
    private const DEFAULT_MASK = '[MASKED]';

    /**
     * Current mask token used to replace sensitive data.
     *
     * @var string
     */
    private string $maskToken;

    private ArraySanitizerInterface $arraySanitizer;
    private ObjectSanitizerInterface $objectSanitizer;
    private StringSanitizerInterface $stringSanitizer;
    private SensitivePatternDetectorInterface $patternDetector;
    private SensitiveKeyDetectorInterface $keyDetector;
    private MaskTokenValidator $maskTokenValidator;
    
    /**
     * @param ArraySanitizerInterface $arraySanitizer
     * @param ObjectSanitizerInterface $objectSanitizer
     * @param StringSanitizerInterface $stringSanitizer
     * @param SensitivePatternDetectorInterface $patternDetector
     * @param SensitiveKeyDetectorInterface $keyDetector
     * @param MaskTokenValidator $maskTokenValidator;
     * @param string $maskToken;
     */
    public function __construct(
        ArraySanitizerInterface $arraySanitizer,
        ObjectSanitizerInterface $objectSanitizer,
        StringSanitizerInterface $stringSanitizer,
        SensitivePatternDetectorInterface $patternDetector,
        SensitiveKeyDetectorInterface $keyDetector,
        MaskTokenValidator $maskTokenValidator,
        string $maskToken
    ) {
        $this->arraySanitizer = $arraySanitizer;
        $this->objectSanitizer = $objectSanitizer;
        $this->stringSanitizer = $stringSanitizer;
        $this->patternDetector = $patternDetector;
        $this->keyDetector = $keyDetector;
        $this->maskTokenValidator = $maskTokenValidator;
        $this->maskToken = $this->validateMaskToken($maskToken);
    }

    /**
     * Sanitizes any input value by delegating to the appropriate strategy.
     *
     * @param mixed $input The value to sanitize.
     * @param string $maskToken The mask token to use.
     * @return mixed The sanitized value.
     *
     * @throws InvalidArgumentException If the mask token is invalid.
     */
    public function sanitize(mixed $input, ?string $maskToken = null): mixed
    {
        return $this->routeSanitize($input, $maskToken ?? $this->maskToken);
    }

    /**
     * Determines whether the given value is or contains sensitive data.
     *
     * @param mixed $value The value to analyze.
     * @return bool True if the value is or contains sensitive data, false otherwise.
     */
    public function isSensitive(mixed $value): bool
    {
        return $this->routeIsSensitive($value);
    }

    /**
     * Routes the input value to the correct sanitizer based on its type.
     * Handles recursion depth for complex structures.
     *
     * @param mixed $value
     * @param string $maskToken
     * @param int $depth
     * @return mixed
     */
    private function routeSanitize(mixed $value, string $maskToken): mixed
    {
        $validMaskToken = $this->maskTokenValidator->validate($maskToken);

        if (is_array($value)) {
            return $this->arraySanitizer->sanitizeArray($value);
        }

        if (is_object($value)) {
            return $this->objectSanitizer->sanitizeObject($value);
        }

        if (is_string($value)) {
            return $this->stringSanitizer->sanitizeString($value, $validMaskToken);
        }

        // Non-string scalars (int, float, bool, null) are not considered sensitive and are returned as-is.
        return $value;
    }

    /**
     * Routes the input value to the appropriate sensitivity checker.
     *
     * @param mixed $value
     * @return bool
     */
    private function routeIsSensitive(mixed $value): bool
    {
        if (is_string($value)) {
            return $this->patternDetector->matchesSensitivePatterns($value)
                || $this->keyDetector->isSensitiveKey($value);
        }

        if (is_array($value)) {
            foreach ($value as $key => $item) {
                if ((is_string($key) && $this->keyDetector->isSensitiveKey($key)) || $this->routeIsSensitive($item)) {
                    return true;
                }
            }
            return false;
        }

        if (is_object($value)) {
            foreach (array_keys(get_object_vars($value)) as $property) {
                if ($this->keyDetector->isSensitiveKey($property)) {
                    return true;
                }
            }
            return $this->routeIsSensitive($this->forceArray($value));
        }

        // Non-string scalars (int, float, bool, null) are not considered sensitive.
        return false;
    }

    /**
     * Converts an object to array if necessary.
     *
     * @param mixed $input
     * @return array
     */
    private function forceArray($input): array
    {
        if (is_array($input)) {
            return $input;
        }
        if (is_object($input)) {
            if (method_exists($input, 'toArray')) {
                return $input->toArray();
            }
            return get_object_vars($input);
        }
        return [];
    }

    /**
     * Validates the provided mask token.
     *
     * If the mask token is null or the route is considered sensitive for the given token,
     * the internal default mask is returned. Otherwise, the token is validated using the mask token validator.
     *
     * @param string|null $maskToken The mask token to validate, or null.
     * @return string The validated mask token or the default mask.
     */
    private function validateMaskToken(?string $maskToken = null): string
    {
        if ($maskToken === null || $this->routeIsSensitive($maskToken)) {
            return self::DEFAULT_MASK;
        }
        return $this->maskTokenValidator->validate($maskToken);
    }
}
