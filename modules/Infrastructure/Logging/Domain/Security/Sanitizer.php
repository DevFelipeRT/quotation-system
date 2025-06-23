<?php

declare(strict_types=1);

namespace Logging\Domain\Security;

use Normalizer;
use Logging\Domain\Security\Contract\SanitizerInterface;
use Logging\Domain\Exception\InvalidLogSanitizerConfigException;
use PublicContracts\Logging\Config\SanitizationConfigInterface;

/**
 * Sanitizer
 *
 * Masks sensitive data in log inputs or parameter arrays.
 * - English/Portuguese sensitive keys (with Unicode/fuzzy/vowel-omission match).
 * - Value patterns for PII/credentials (with user-extendable regex list).
 * - Recursion depth control and circular reference detection.
 * - Converts objects to arrays when necessary.
 * - Customizable, always-bracketed mask token.
 * - Blocks token collisions and ensures only safe tokens are used.
 */
final class Sanitizer implements SanitizerInterface
{
    /**
     * Maximum recursion depth allowed for array and object sanitization.
     */
    private const MAX_RECURSION_DEPTH = 8;

    /**
     * Default token used to mask sensitive data.
     */
    private const DEFAULT_MASK = '[MASKED]';

    /**
     * Default list of sensitive keys (English and Portuguese).
     * This list is merged with user-defined keys from the configuration.
     */
    private const DEFAULT_SENSITIVE_KEYS = [
        'password', 'token', 'api_key', 'secret', 'authorization', 'credit_card', 'ssn',
        'senha', 'chave_api', 'segredo', 'autorizacao', 'cartao_credito', 'cpf', 'cnpj', 'acesso_token',
    ];

    /**
     * Default regular expressions for direct detection of sensitive values.
     * These patterns are merged with user-defined patterns from the configuration.
     */
    private const DEFAULT_SENSITIVE_PATTERNS = [
        '/\b\d{3}\.?\d{3}\.?\d{3}-?\d{2}\b/',       // CPF
        '/\b\d{16}\b/',                             // Credit card (16 digits)
        '/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}/i', // Email
        // Credential phrase patterns are dynamically generated from sensitive keys
    ];

    /**
     * Regular expression for forbidden mask tokens (prevents unsafe values).
     */
    private const DEFAULT_MASK_TOKEN_FORBIDDEN_PATTERN = '/[\x00-\x1F\x7F]|base64|script|php/i';

    /**
     * All sensitive keys after merging and normalization.
     * Used for both key-based and phrase-based detection.
     *
     * @var string[]
     */
    private array $sensitiveKeys;

    /**
     * All regular expressions for value-based sensitive data detection.
     *
     * @var string[]
     */
    private array $sensitivePatterns;

    /**
     * Maximum allowed depth for recursive sanitization.
     *
     * @var int
     */
    private int $maxDepth;

    /**
     * Current mask token used to replace sensitive data.
     *
     * @var string
     */
    private string $maskToken;

    /**
     * Regular expression pattern to block unsafe mask tokens.
     *
     * @var string
     */
    private string $maskTokenForbiddenPattern;

    /**
     * @param SanitizationConfigInterface $config
     */
    public function __construct(SanitizationConfigInterface $config)
    {
        // Load sensitive keys from config or fallback to default.
        $customKeys = $config->sensitiveKeys();
        $this->sensitiveKeys = $this->prepareSensitiveKeys(
            array_merge(self::DEFAULT_SENSITIVE_KEYS, $customKeys)
        );

        // Load patterns from config or fallback to default.
        $customPatterns = $config->sensitivePatterns();
        $this->sensitivePatterns = array_merge(self::DEFAULT_SENSITIVE_PATTERNS, $customPatterns);

        // Use config values for maxDepth and maskToken or fallback to default.
        $this->maxDepth  = $config->maxDepth() ?? self::MAX_RECURSION_DEPTH;
        $this->maskTokenForbiddenPattern = $config->maskTokenForbiddenPattern() ?? self::DEFAULT_MASK_TOKEN_FORBIDDEN_PATTERN;
        $this->maskToken = $this->sanitizeMaskToken($config->maskToken() ?? self::DEFAULT_MASK);
    }

    /**
     * Sanitizes any input value (array, object, or scalar) by masking all sensitive information.
     * For arrays and objects, the process is recursive and covers both keys and values.
     * For strings, all direct value patterns and credential phrases (e.g., "password: ...") are masked in-place.
     * Scalars that are not strings are returned as-is.
     *
     * @param mixed $input The value to be sanitized.
     * @param string|null $maskToken Optional custom mask token to use for this operation.
     * @return mixed The sanitized input with all sensitive data masked.
     */
    public function sanitize(mixed $input, ?string $maskToken = null): mixed
    {
        $mask = $this->sanitizeMaskToken($maskToken ?? $this->maskToken);
        $seen = [];

        if (is_array($input)) {
            return $this->sanitizeRecursive($input, 0, $mask, $seen);
        }

        if (is_object($input)) {
            return $this->sanitizeRecursive($this->forceArray($input), 0, $mask, $seen);
        }

        if (is_string($input)) {
            $sanitized = trim($this->normalizeUnicode($input));

            // 1. Mask direct value patterns (CPF, credit card, email, etc.)
            if (!empty($this->sensitivePatterns)) {
                foreach ($this->sensitivePatterns as $pattern) {
                    $sanitized = preg_replace($pattern, $mask, $sanitized);
                }
            }

            // 2. Mask credential phrases ("key: value") ONLY in those cases
            if (!empty($this->sensitiveKeys)) {
                $phrasePattern = '/\b(' . implode('|', array_map('preg_quote', $this->sensitiveKeys)) . ')[\s:]+([^\s,;]+)/iu';
                $sanitized = preg_replace_callback(
                    $phrasePattern,
                    function ($matches) use ($mask) {
                        // $matches[1] = key, $matches[2] = sensitive value
                        return $matches[1] . ': ' . $mask;
                    },
                    $sanitized
                );
            }

            return $sanitized;
        }

        // Non-string scalars (int, float, bool, null) are not considered sensitive
        return $input;
    }


    /**
     * Determines whether a given value contains sensitive information, considering both keys and values.
     *
     * This method inspects strings and array/object keys using all configured sensitive patterns.
     * For arrays and objects, both keys and values are checked recursively.
     *
     * @param mixed $value Input to be analyzed.
     * @return bool True if any key or value is considered sensitive; otherwise, false.
     */
    public function isSensitive(mixed $value): bool
    {
        // Check for sensitive string value
        if (is_string($value)) {
            if ($this->matchesSensitivePatterns($value) || $this->isSensitiveKey($value)) {
                return true;
            }
            return false;
        }

        // Check arrays: both keys and values recursively
        if (is_array($value)) {
            foreach ($value as $key => $item) {
                if (is_string($key) && $this->isSensitiveKey($key)) {
                    return true;
                }
                if ($this->isSensitive($item)) {
                    return true;
                }
            }
            return false;
        }

        // Check objects: convert to array, check property names and values
        if (is_object($value)) {
            foreach (array_keys(get_object_vars($value)) as $property) {
                if ($this->isSensitiveKey($property)) {
                    return true;
                }
            }
            return $this->isSensitive($this->forceArray($value));
        }

        // Non-string scalars (int, float, bool, null) are not considered sensitive.
        return false;
    }

    /**
     * Prepares sensitive key list (lowercase, normalized, fuzzy, and vowel-omitted).
     * @param string[] $keys
     * @return string[]
     */
    private function prepareSensitiveKeys(array $keys): array
    {
        $out = [];
        foreach ($keys as $k) {
            $base = mb_strtolower(trim($k));
            $out[] = $base;
            $out[] = $this->normalizeUnicode($base);
            $out[] = str_replace(['_', '-', '@'], '', $base);
            $out[] = $this->removeVowels($base);
        }
        return array_unique($out);
    }

    /**
     * Returns true if key is sensitive (fuzzy, normalized, vowel-omitted, etc).
     * @param string $key
     * @return bool
     */
    private function isSensitiveKey(string $key): bool
    {
        $check = mb_strtolower(trim($key));
        $norm  = $this->normalizeUnicode($check);
        $fuzzy = str_replace(['_', '-', '@'], '', $check);
        $novowel = $this->removeVowels($check);

        foreach ([$check, $norm, $fuzzy, $novowel] as $variant) {
            if (in_array($variant, $this->sensitiveKeys, true)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Removes vowels from a string (English and Portuguese).
     * @param string $str
     * @return string
     */
    private function removeVowels(string $str): string
    {
        return preg_replace('/[aeiouáéíóúàèìòùãõâêîôûäëïöü]/iu', '', $str);
    }

    /**
     * Recursively sanitizes an array/object, with full protections.
     *
     * @param array<mixed> $data
     * @param int $currentDepth
     * @param string $maskToken
     * @param array<int, int> $seen
     * @return array<mixed>
     */
    private function sanitizeRecursive(array $data, int $currentDepth, string $maskToken, array &$seen): array
    {
        if ($currentDepth > $this->maxDepth) {
            return ['[DEPTH_LIMIT_EXCEEDED]' => true];
        }
        $arrayId = spl_object_id((object)$data);
        if (isset($seen[$arrayId])) {
            return ['[CIRCULAR_REFERENCE_DETECTED]' => true];
        }
        $seen[$arrayId] = 1;

        $sanitized = [];
        foreach ($data as $key => $value) {
            if ($this->isSensitiveKey((string)$key)) {
                $sanitized[$key] = $maskToken;
                continue;
            }
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeRecursive($value, $currentDepth + 1, $maskToken, $seen);
                continue;
            }
            if (is_object($value)) {
                $sanitized[$key] = $this->sanitizeRecursive($this->forceArray($value), $currentDepth + 1, $maskToken, $seen);
                continue;
            }
            if (
                is_string($value) &&
                $this->matchesSensitivePatterns($value)
            ) {
                $sanitized[$key] = $maskToken;
                continue;
            }
            if ($value === $maskToken) {
                $unwrapped = str_replace(['[', ']'], '', $maskToken);
                $sanitized[$key] = "[{$unwrapped}_ORIGINAL_VALUE]";
                continue;
            }
            $sanitized[$key] = $value;
        }

        unset($seen[$arrayId]);
        return $sanitized;
    }

    /**
     * Convert to array: arrays, toArray() objects, or get_object_vars().
     * @param mixed $input
     * @return array<mixed>
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
     * Returns true if value matches any sensitive pattern.
     * @param string $value
     * @return bool
     */
    private function matchesSensitivePatterns(string $value): bool
    {
        foreach ($this->sensitivePatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Unicode normalization (FORM_KC).
     * @param string $s
     * @return string
     */
    private function normalizeUnicode(string $s): string
    {
        if (class_exists('Normalizer')) {
            return Normalizer::normalize($s, Normalizer::FORM_KC) ?: $s;
        }
        return $s;
    }

    /**
     * Sanitizes and brackets the mask token, always [UPPERCASE].
     *
     * The forbidden character/pattern policy is obtained from the SanitizationConfigInterface,
     * ensuring security rules are externally configurable and domain-agnostic.
     *
     * @param string $maskToken
     * @return string
     * @throws InvalidLogSanitizerConfigException
     */
    private function sanitizeMaskToken(string $maskToken): string
    {
        $clean = trim($maskToken);
        $pattern = $this->maskTokenForbiddenPattern;

        if (
            $clean === '' ||
            mb_strlen($clean) > 40 ||
            preg_match($pattern, $clean)
        ) {
            throw InvalidLogSanitizerConfigException::forMaskToken($maskToken);
        }

        // Normalization
        $unwrapped = preg_replace('/^\[([^\[\]]*)\]$/', '$1', $clean);
        $unwrapped = str_replace(['[', ']'], '', $unwrapped);
        $final = '[' . mb_strtoupper($unwrapped) . ']';
        return $final;
    }
}
