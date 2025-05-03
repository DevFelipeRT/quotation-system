<?php

namespace App\Infrastructure\Logging;

/**
 * LogSanitizer
 *
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
     * Sanitizes parameters commonly used in SQL statements.
     *
     * This method is an alias for sanitize(), retained for clarity and readability
     * when used specifically in database query logs.
     *
     * @param array $params SQL parameter bindings.
     * @return array Sanitized parameters.
     */
    public static function sanitizeSqlParams(array $params): array
    {
        return self::sanitize($params);
    }

    /**
     * Determines whether the given key should be treated as sensitive.
     *
     * @param string $key The key to evaluate.
     * @return bool True if the key is sensitive; false otherwise.
     */
    private static function isSensitiveKey(string $key): bool
    {
        return in_array(strtolower($key), self::SENSITIVE_KEYS, true);
    }
}
