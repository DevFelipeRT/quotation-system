<?php

declare(strict_types=1);

namespace Logging\Security\Sanitizing\Service;

use Logging\Domain\Exception\InvalidSanitizationConfigException;
use Logging\Security\Sanitizing\Contract\SensitiveKeyDetectorInterface;

/**
 * CredentialPhraseSanitizer
 *
 * Responsible for detecting and masking sensitive credential phrases in strings.
 * Applies masking only to values clearly associated with sensitive keys,
 * supporting bidirectional analysis, tolerance for intermediate words,
 * and a customizable set of separators.
 */
final class CredentialPhraseSanitizer
{
    /**
     * Default separators used to distinguish key-value pairs in credential phrases.
     * This set can be extended via constructor.
     */
    private const DEFAULT_SEPARATORS = [
        ':', '=', 'is', 'foi', 'é', '-', '->'
    ];

    /**
     * Maximum number of allowed intermediate words/segments between the key and the separator.
     * Higher values increase coverage but may introduce false positives.
     */
    private const INTERMEDIATE_WORD_LIMIT = 3;

    /**
     * Sensitive key detector used to obtain prepared sensitive keys.
     *
     * @var SensitiveKeyDetectorInterface
     */
    private SensitiveKeyDetectorInterface $keyDetector;

    /**
     * List of recognized separators between key and value.
     *
     * @var string[]
     */
    private array $separators;

    /**
     * @param SensitiveKeyDetectorInterface $keyDetector
     * @param string[]|null                $customSeparators (optional) Custom separators to merge with defaults.
     */
    public function __construct(
        SensitiveKeyDetectorInterface $keyDetector,
        ?array $customSeparators = null
    ) {
        $this->keyDetector = $keyDetector;
        $this->separators = self::initializeSeparators($customSeparators);
    }

    /**
     * Detects and masks sensitive credential phrases in the input,
     * preserving all spacing and punctuation for original formatting fidelity.
     * Applies forward (key → separator → value) pattern first;
     * only if no substitution is made, applies the backward (value ← separator ← key) pattern.
     *
     * @param string $input
     * @param string $maskToken
     * @return string
     */
    public function sanitizePhrase(string $input, string $maskToken): string
    {
        $keys = $this->keyDetector->getPreparedKeys();
        if (empty($keys)) {
            return $input;
        }

        $forwardResult = $this->maskForwardPattern($input, $maskToken, $keys, $matchesCount);

        if ($matchesCount > 0) {
            return $forwardResult;
        }

        return $this->maskBackwardPattern($input, $maskToken, $keys);
    }

    /**
     * Applies forward masking: key [optional intermediate] [spaces] separator [spaces] value.
     *
     * @param string   $input
     * @param string   $maskToken
     * @param string[] $keys
     * @param int      $matchesCount Output: Number of matches performed
     * @return string
     */
    private function maskForwardPattern(string $input, string $maskToken, array $keys, ?int &$matchesCount = null): string
    {
        $sepPattern = implode('|', array_map('preg_quote', $this->separators));
        $intermediate = '(?:\s+\w+){0,' . self::INTERMEDIATE_WORD_LIMIT . '}?';

        $regex = '/\b('
            . implode('|', array_map('preg_quote', $keys))
            . ')\b'                       // [1] sensitive key
            . '(' . $intermediate . ')'    // [2] optional intermediate (with leading spaces)
            . '(\s*)'                     // [3] spaces before separator
            . '(' . $sepPattern . ')'     // [4] separator (including words)
            . '(\s*)'                     // [5] spaces after separator
            . '([^\s,;:."]+)/iu';         // [6] value

        $matchesCount = 0;
        $result = preg_replace_callback(
            $regex,
            static function ($matches) use ($maskToken, &$matchesCount) {
                ++$matchesCount;
                return $matches[1]
                    . $matches[2]
                    . $matches[3]
                    . $matches[4]
                    . $matches[5]
                    . $maskToken;
            },
            $input
        );
        return $result;
    }

    /**
     * Applies backward masking: value [spaces] separator [optional intermediate] key.
     *
     * @param string   $input
     * @param string   $maskToken
     * @param string[] $keys
     * @return string
     */
    private function maskBackwardPattern(string $input, string $maskToken, array $keys): string
    {
        $sepPattern = implode('|', array_map('preg_quote', $this->separators));
        $intermediate = '(?:\s+\w+){0,' . self::INTERMEDIATE_WORD_LIMIT . '}?';

        $regex = '/([^\s,;:."]+)'        // [1] value
            . '(\s*)'                    // [2] spaces after value
            . '(' . $sepPattern . ')'    // [3] separator
            . '(' . $intermediate . ')'  // [4] optional intermediate (with leading spaces)
            . '\b(' . implode('|', array_map('preg_quote', $keys)) . ')\b/iu'; // [5] key

        return preg_replace_callback(
            $regex,
            static function ($matches) use ($maskToken) {
                return $maskToken
                    . $matches[2]
                    . $matches[3]
                    . $matches[4]
                    . $matches[5];
            },
            $input
        );
    }

    /**
     * Initializes and validates the separators, merging custom with default values.
     *
     * @param string[]|null $customSeparators
     * @return string[]
     */
    private static function initializeSeparators(?array $customSeparators): array
    {
        $validatedCustom = $customSeparators !== null
            ? self::validateSeparators($customSeparators)
            : [];
        return array_values(array_unique(array_merge($validatedCustom, self::DEFAULT_SEPARATORS)));
    }

    /**
     * Validates the given separators array.
     * Each separator must be a non-empty string without whitespace.
     *
     * @param array $separators
     * @return array
     * @throws InvalidSanitizationConfigException If any separator is invalid.
     */
    private static function validateSeparators(array $separators): array
    {
        $validated = [];
        foreach ($separators as $separator) {
            if (!is_string($separator) || trim($separator) === '' || preg_match('/\s/', $separator)) {
                throw InvalidSanitizationConfigException::forSeparator($separator);
            }
            $validated[] = $separator;
        }
        return array_values(array_unique($validated));
    }
}
