<?php

namespace App\Infrastructure\Logging\Security;

/**
 * Responsible for masking sensitive information in log context arrays
 * to prevent credential leaks and ensure secure observability.
 */
final class LogSanitizer
{
    /**
     * Keys that are considered sensitive and must be masked in logs.
     */
    private const SENSITIVE_KEYS = [
        'password',
        'senha',
        'token',
        'api_key',
        'secret',
        'authorization',
        'cpf',
        'credit_card',
    ];

    /**
     * Recursively sanitizes sensitive keys in a given array.
     *
     * @param array $context Arbitrary associative array for logging.
     * @return array Sanitized array with redacted sensitive values.
     */
    public static function sanitize(array $context): array
    {
        $sanitized = [];

        foreach ($context as $key => $value) {
            if (self::isSensitiveKey($key)) {
                $sanitized[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $sanitized[$key] = self::sanitize($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Alias for sanitize(), used for clarity in database logging contexts.
     *
     * @param array $params SQL parameter bindings.
     * @return array
     */
    public static function sanitizeSqlParams(array $params): array
    {
        return self::sanitize($params);
    }

    /**
     * Determines whether the given key should be treated as sensitive.
     *
     * @param string $key
     * @return bool
     */
    private static function isSensitiveKey(string $key): bool
    {
        return in_array(strtolower($key), self::SENSITIVE_KEYS, true);
    }
}
