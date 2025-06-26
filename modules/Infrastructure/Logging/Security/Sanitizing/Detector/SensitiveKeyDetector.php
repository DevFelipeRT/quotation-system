<?php

declare(strict_types=1);

namespace Logging\Security\Sanitizing\Detector;

use Logging\Security\Sanitizing\Contract\SensitiveKeyDetectorInterface;
use Logging\Security\Sanitizing\Tools\UnicodeNormalizer;
use Logging\Domain\Exception\InvalidSanitizationConfigException;

/**
 * SensitiveKeyDetector
 *
 * Responsible for detecting whether a key is sensitive for logging purposes.
 * Applies Unicode normalization, lowercasing, fuzzy matching, and vowel removal strategies to improve detection accuracy across multiple languages and formats.
 *
 * - Merges default and custom sensitive keys.
 * - Normalizes all keys to maximize fuzzy and cross-locale matching.
 * - Designed for high-performance usage in recursive sanitization operations.
 */
final class SensitiveKeyDetector implements SensitiveKeyDetectorInterface
{
    private const DEFAULT_SENSITIVE_KEYS = [
        'password', 'token', 'api_key', 'secret', 'authorization', 'credit_card', 'ssn',
        'senha', 'chave_api', 'segredo', 'autorizacao', 'cartao_credito', 'cpf', 'cnpj', 'acesso_token',
    ];

    /**
     * All prepared sensitive keys after normalization and fuzzy transforms.
     *
     * @var string[]
     */
    private array $preparedKeys;

    /**
     * @var UnicodeNormalizer
     */
    private UnicodeNormalizer $unicodeNormalizer;

    /**
     * Constructs a new SensitiveKeyDetector with optional custom keys.
     *
     * @param string[] $customKeys Custom sensitive keys to supplement the defaults (optional).
     * @throws InvalidSanitizationConfigException If any custom key is invalid.
     */
    public function __construct(array $customKeys = [])
    {
        $this->unicodeNormalizer = new UnicodeNormalizer();

        $this->validateKeys($customKeys);

        $merged = array_merge(self::DEFAULT_SENSITIVE_KEYS, $customKeys);
        $this->preparedKeys = $this->prepareSensitiveKeys($merged);
    }

    /**
     * Determines whether the given key is considered sensitive in the logging domain.
     * Detection uses lowercase, Unicode normalization, fuzzy transform, and vowel removal.
     *
     * @param string $key
     * @return bool
     */
    public function isSensitiveKey(string $key): bool
    {
        $check   = mb_strtolower(trim($key));
        $norm    = $this->unicodeNormalizer->normalize($check);
        $fuzzy   = str_replace(['_', '-', '@'], '', $check);
        $novowel = $this->removeVowels($check);

        foreach ([$check, $norm, $fuzzy, $novowel] as $variant) {
            if (in_array($variant, $this->preparedKeys, true)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Prepares the list of sensitive keys by applying normalization, fuzzy transform, and vowel removal.
     *
     * @param string[] $keys
     * @return string[]
     */
    private function prepareSensitiveKeys(array $keys): array
    {
        $out = [];
        foreach ($keys as $k) {
            $base = mb_strtolower(trim($k));
            $out[] = $base;
            $out[] = $this->unicodeNormalizer->normalize($base);
            $out[] = str_replace(['_', '-', '@'], '', $base);
            $out[] = $this->removeVowels($base);
        }
        return array_unique($out);
    }

    /**
     * Removes all vowels (including accented vowels) from a string.
     *
     * @param string $str
     * @return string
     */
    private function removeVowels(string $str): string
    {
        return preg_replace('/[aeiouáéíóúàèìòùãõâêîôûäëïöü]/iu', '', $str);
    }

    /**
     * Returns all prepared sensitive keys (useful for debugging or audit).
     *
     * @return string[]
     */
    public function getPreparedKeys(): array
    {
        return $this->preparedKeys;
    }

    /**
     * Validates sensitive keys.
     *
     * Each key must be a non-empty string, without control characters or only whitespace.
     *
     * @param string[] $keys
     * @throws InvalidSanitizationConfigException
     */
    private function validateKeys(array $keys): void
    {
        foreach ($keys as $key) {
            if (!is_string($key) || trim($key) === '' || preg_match('/[\x00-\x1F\x7F]/', $key)) {
                throw InvalidSanitizationConfigException::forSensitiveKey((string)$key);
            }
        }
    }
}
