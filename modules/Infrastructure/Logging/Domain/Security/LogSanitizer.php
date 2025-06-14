<?php

declare(strict_types=1);

namespace Logging\Domain\Security;

use Logging\Domain\Security\Contract\LogSanitizerInterface;
use Logging\Domain\Exception\InvalidLogSanitizerConfigException;
use Normalizer;

/**
 * LogSanitizer
 *
 * Masks sensitive data in log inputs or parameter arrays.
 * - English/Portuguese sensitive keys (with Unicode/fuzzy/vowel-omission match).
 * - Value patterns for PII/credentials (with user-extendable regex list).
 * - Recursion depth control and circular reference detection.
 * - Converts objects to arrays when necessary.
 * - Customizable, always-bracketed mask token.
 * - Blocks token collisions and ensures only safe tokens are used.
 */
final class LogSanitizer implements LogSanitizerInterface
{
    private const DEFAULT_MASK = '[MASKED]';
    private const DEFAULT_SENSITIVE_KEYS = [
        'password', 'token', 'api_key', 'secret', 'authorization', 'credit_card', 'ssn',
        'senha', 'chave_api', 'segredo', 'autorizacao', 'cartao_credito', 'cpf', 'cnpj', 'acesso_token',
    ];
    private const DEFAULT_SENSITIVE_PATTERNS = [
        '/\b\d{3}\.\?\d{3}\.\?\d{3}-?\d{2}\b/',                       // CPF
        '/\b\d{16}\b/',                                               // Credit card (16 digits)
        '/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}/i',                   // Email
        '/(senha|password|secret|token|chave)[\s:]*[a-z0-9\-\._]+/i', // Credential phrases
    ];

    /** @var string[] */
    private array $sensitiveKeys;
    /** @var string[] */
    private array $sensitivePatterns;
    /** @var int */
    private int $maxDepth;
    /** @var string */
    private string $maskToken;

    /**
     * @param string[]|null $customSensitiveKeys
     * @param string[]|null $customSensitivePatterns
     * @param int|null $maxDepth
     * @param string|null $maskToken
     */
    public function __construct(
        ?array $customSensitiveKeys = null,
        ?array $customSensitivePatterns = null,
        ?int $maxDepth = null,
        ?string $maskToken = null
    ) {
        $customKeys = $customSensitiveKeys ?? [];
        $this->sensitiveKeys = $this->prepareSensitiveKeys(
            array_merge(self::DEFAULT_SENSITIVE_KEYS, $customKeys)
        );
        $customPatterns = $customSensitivePatterns ?? [];
        $this->sensitivePatterns = array_merge(self::DEFAULT_SENSITIVE_PATTERNS, $customPatterns);
        $this->maxDepth = $maxDepth ?? 8;
        $this->maskToken = $this->sanitizeMaskToken($maskToken ?? self::DEFAULT_MASK);
    }

    /**
     * Sanitizes sensitive keys and values from the provided input array, 
     * returning a copy with all confidential data masked or removed.
     *
     * @param array<string, mixed> $input
     * @return array<string, mixed> Sanitized array safe for logging or export.
     */
    public function sanitize(array $input, ?string $maskToken = null): array
    {
        $mask = $this->sanitizeMaskToken($maskToken ?? $this->maskToken);
        $seen = [];
        return $this->sanitizeRecursive($this->forceArray($input), 0, $mask, $seen);
    }

    /**
     * Sanitize SQL parameter bindings.
     *
     * @param array<mixed> $params
     * @param string|null $maskToken
     * @return array<mixed>
     */
    public function sanitizeSqlParams(array $params, ?string $maskToken = null): array
    {
        $mask = $this->sanitizeMaskToken($maskToken ?? $this->maskToken);
        $seen = [];
        return $this->sanitizeRecursive($this->forceArray($params), 0, $mask, $seen);
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
                $sanitized[$key] = '[MASKED_ORIGINAL_VALUE]';
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
     * @param string $maskToken
     * @return string
     * @throws InvalidLogSanitizerConfigException
     */
    private function sanitizeMaskToken(string $maskToken): string
    {
        $clean = trim($maskToken);
        if (
            $clean === '' ||
            mb_strlen($clean) > 40 ||
            preg_match('/[\x00-\x1F\x7F]|base64|script|php/i', $clean)
        ) {
            throw InvalidLogSanitizerConfigException::forMaskToken($maskToken);
        }
        // Remove brackets if already present
        $unwrapped = preg_replace('/^\[([^\[\]]*)\]$/', '$1', $clean);
        $unwrapped = str_replace(['[', ']'], '', $unwrapped);
        $final = '[' . mb_strtoupper($unwrapped) . ']';
        return $final;
    }
}
