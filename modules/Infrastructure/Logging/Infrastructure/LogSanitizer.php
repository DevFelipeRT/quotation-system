<?php

declare(strict_types=1);

namespace Logging\Infrastructure;

/**
 * Responsible for masking sensitive information in log contexts
 * to prevent leaks of credentials or private data.
 */
final class LogSanitizer
{
    /**
     * @var string[] List of keys considered sensitive.
     */
    private array $sensitiveKeys;

    /**
     * @param string[]|null $customSensitiveKeys Override default key list if provided
     */
    public function __construct(?array $customSensitiveKeys = null)
    {
        $this->sensitiveKeys = $customSensitiveKeys ?? [
            'password',
            'senha',
            'token',
            'api_key',
            'secret',
            'authorization',
            'cpf',
            'credit_card',
        ];
    }

    /**
     * Sanitizes sensitive keys from the provided context.
     *
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function sanitize(array $context): array
    {
        $sanitized = [];

        foreach ($context as $key => $value) {
            if ($this->isSensitiveKey($key)) {
                $sanitized[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitize($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Sanitizes SQL parameter bindings.
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function sanitizeSqlParams(array $params): array
    {
        return $this->sanitize($params);
    }

    /**
     * Determines whether a key should be redacted.
     *
     * @param string $key
     * @return bool
     */
    private function isSensitiveKey(string $key): bool
    {
        return in_array(strtolower($key), $this->sensitiveKeys, true);
    }
}
