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
    private const MAX_RECURSION_DEPTH = 8;
    private const DEFAULT_MASK = '[MASKED]';
    private const DEFAULT_SENSITIVE_KEYS = [
        'password', 'token', 'api_key', 'secret', 'authorization', 'credit_card', 'ssn',
        'senha', 'chave_api', 'segredo', 'autorizacao', 'cartao_credito', 'cpf', 'cnpj', 'acesso_token',
    ];
    private const DEFAULT_SENSITIVE_PATTERNS = [
        '/\b\d{3}\.?\d{3}\.?\d{3}-?\d{2}\b/',                         // CPF
        '/\b\d{16}\b/',                                               // Credit card (16 digits)
        '/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}/i',                   // Email
        '/(senha|password|secret|token|chave)[\s:]*[a-z0-9\-\._]+/i', // Credential phrases
    ];
    private const DEFAULT_MASK_TOKEN_FORBIDDEN_PATTERN = '/[\x00-\x1F\x7F]|base64|script|php/i';

    /** @var string[] */
    private array $sensitiveKeys;
    /** @var string[] */
    private array $sensitivePatterns;
    /** @var int */
    private int $maxDepth;
    /** @var string */
    private string $maskToken;
    /** @var string */
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
     * Sanitizes any input value: arrays, objects, or scalars.
     * - For arrays/objects: recursively sanitizes keys and values.
     * - For strings: trims, normalizes, and applies sensitive pattern checks.
     * - For other scalar values: returns as is.
     *
     * @param mixed $input
     * @param string|null $maskToken
     * @return mixed
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
            if ($this->matchesSensitivePatterns($sanitized)) {
                return $mask;
            }
            if ($sanitized === $mask) {
                $unwrapped = str_replace(['[', ']'], '', $maskToken);
                return "[{$unwrapped}_ORIGINAL_VALUE]";
            }
            return $sanitized;
        }

        // For integer, float, bool, null: return as is (cannot be sensitive)
        return $input;
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
