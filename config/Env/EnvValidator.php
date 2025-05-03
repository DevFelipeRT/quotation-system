<?php

namespace Config\Env;

use RuntimeException;

/**
 * EnvValidator
 *
 * Verifies that all required environment variables are defined before the application
 * proceeds with dependency initialization. Fails early and explicitly if misconfigured.
 */
class EnvValidator
{
    /**
     * Required environment variable keys.
     *
     * @var array<string>
     */
    private const REQUIRED_KEYS = [
        'APP_ENV',
        'APP_DEBUG',
        'DB_HOST',
        'DB_PORT',
        'DB_USER',
        'DB_PASS',
        'DB_NAME',
    ];

    /**
     * Validates that all required environment variables are present and non-empty.
     *
     * @param EnvLoader $env
     * @throws RuntimeException If any required variable is missing or empty.
     */
    public static function validate(EnvLoader $env): void
    {
        foreach (self::REQUIRED_KEYS as $key) {
            $value = $env->getRequired($key);

            if (trim($value) === '') {
                throw new RuntimeException("Environment variable '{$key}' is defined but empty.");
            }
        }
    }
}
