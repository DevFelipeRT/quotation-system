<?php

namespace Config\Env;

use InvalidArgumentException;
use RuntimeException;

/**
 * EnvLoader
 *
 * Secure, stateless reader for .env files. This loader reads individual environment
 * variables on demand, without persisting sensitive data in memory.
 *
 * It is intended for backend systems that require strong isolation between configuration
 * and runtime behavior.
 */
class EnvLoader
{
    /**
     * Absolute path to the .env file.
     *
     * @var string
     */
    private string $envFilePath;

    /**
     * Constructs a secure environment loader.
     *
     * @param string $envFilePath Full path to the .env file.
     * @throws InvalidArgumentException If file is invalid or unreadable.
     */
    public function __construct(string $envFilePath)
    {
        if (!str_ends_with($envFilePath, '.env')) {
            throw new InvalidArgumentException('Only files ending in .env are supported.');
        }

        if (!is_readable($envFilePath)) {
            throw new InvalidArgumentException("Environment file not readable: {$envFilePath}");
        }

        $this->envFilePath = $envFilePath;
    }

    /**
     * Reads a required environment variable from the .env file.
     *
     * @param string $key Variable name (e.g. DB_USER)
     * @return string
     * @throws RuntimeException If the key is missing or empty.
     */
    public function getRequired(string $key): string
    {
        $value = $this->readKey($key);

        if ($value === null || trim($value) === '') {
            throw new RuntimeException("Missing required environment variable: {$key}");
        }

        return $value;
    }

    /**
     * Reads an environment variable from the .env file,
     * or returns the provided default if missing or empty.
     *
     * @param string $key Variable name (e.g. DB_PORT)
     * @param string|null $default Fallback value if the key is not found or empty
     * @return string|null
     */
    public function get(string $key, ?string $default = null): ?string
    {
        $value = $this->readKey($key);

        return ($value === null || trim($value) === '') ? $default : $value;
    }

    /**
     * Reads a specific key from the .env file (without parsing all keys).
     *
     * @param string $key
     * @return string|null
     */
    private function readKey(string $key): ?string
    {
        $lines = file($this->envFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            [$envKey, $envValue] = array_pad(explode('=', $line, 2), 2, null);

            if (trim($envKey) === $key) {
                return trim($envValue ?? '');
            }
        }

        return null;
    }

    /**
     * Prevents accidental exposure of the internal state via debugging tools.
     *
     * @return array<string, string>
     */
    public function __debugInfo(): array
    {
        return ['EnvLoader' => '[secured]'];
    }
}
