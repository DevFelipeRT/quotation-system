<?php

namespace Config\App;

use Config\Env\EnvLoader;
use InvalidArgumentException;

/**
 * Class AppConfig
 *
 * Provides immutable access to application-level configuration values.
 *
 * Values are sourced from environment variables loaded via EnvLoader,
 * with automatic fallback to defaults defined in DefaultAppConfig.
 *
 * This configuration object is intended to be injected into core services
 * that depend on environment-based behavior (e.g., session handling,
 * logging, database drivers, locale settings).
 */
final class AppConfig
{
    private string $applicationName;
    private string $version;
    private string $environment;
    private bool $debug;
    private string $cookieDomain;
    private string $locale;
    private string $timezone;
    private string $sessionPrefix;

    /**
     * AppConfig constructor.
     *
     * Initializes all application settings from environment variables,
     * validating and falling back to internal defaults as needed.
     *
     * @param EnvLoader $env Environment variable loader instance.
     *
     * @throws InvalidArgumentException If any environment value is malformed or invalid.
     */
    public function __construct(EnvLoader $env)
    {
        $this->applicationName = DefaultAppConfig::APPLICATION_NAME;
        $this->version         = $this->resolveVersion($env->get('APP_VERSION', DefaultAppConfig::VERSION));
        $this->environment     = $this->resolveEnvironment($env->get('APP_ENV', DefaultAppConfig::ENVIRONMENT));
        $this->debug           = $this->resolveDebug($env->get('APP_DEBUG', DefaultAppConfig::DEBUG ? 'true' : 'false'));
        $this->cookieDomain    = $this->resolveCookieDomain($env->get('COOKIE_DOMAIN', DefaultAppConfig::COOKIE_DOMAIN));
        $this->locale          = $this->resolveLocale($env->get('APP_LOCALE', DefaultAppConfig::LOCALE));
        $this->timezone        = $this->resolveTimezone($env->get('APP_TIMEZONE', DefaultAppConfig::TIMEZONE));
        $this->sessionPrefix   = $this->resolveSessionPrefix($env->get('SESSION_PREFIX', DefaultAppConfig::SESSION_PREFIX));
    }

    /**
     * Returns the human-readable name of the application.
     */
    public function getName(): string
    {
        return $this->applicationName;
    }

    /**
     * Returns the current application environment.
     *
     * Common values: 'production', 'development', 'staging', 'testing'.
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * Returns true if the application is running in development mode.
     */
    public function isDevelopment(): bool
    {
        return $this->environment === 'development';
    }

    /**
     * Returns whether debug mode is enabled (affects logging, verbosity, etc.).
     */
    public function isDebugMode(): bool
    {
        return $this->debug;
    }

    /**
     * Returns the domain used for setting application cookies.
     */
    public function getCookieDomain(): string
    {
        return $this->cookieDomain;
    }

    /**
     * Returns the default locale used across the application (e.g., 'pt_BR').
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Returns the default timezone used across the application (e.g., 'UTC').
     */
    public function getTimezone(): string
    {
        return $this->timezone;
    }

    /**
     * Returns the configured prefix used to namespace session keys.
     */
    public function getSessionPrefix(): string
    {
        return $this->sessionPrefix;
    }

    /**
     * Returns the application version (e.g., '1.0.0').
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Validates and resolves the current environment.
     *
     * @param string $value
     * @return string
     *
     * @throws InvalidArgumentException
     */
    private function resolveEnvironment(string $value): string
    {
        return match ($value) {
            'production', 'development', 'staging', 'testing' => $value,
            default => throw new InvalidArgumentException("Invalid APP_ENV: '{$value}'"),
        };
    }

    /**
     * Parses the debug flag from a string value.
     *
     * @param string $value
     * @return bool
     *
     * @throws InvalidArgumentException
     */
    private function resolveDebug(string $value): bool
    {
        return match (strtolower($value)) {
            'true' => true,
            'false' => false,
            default => throw new InvalidArgumentException("Invalid APP_DEBUG: '{$value}' (expected 'true' or 'false')"),
        };
    }

    /**
     * Validates the cookie domain format.
     *
     * @param string $value
     * @return string
     *
     * @throws InvalidArgumentException
     */
    private function resolveCookieDomain(string $value): string
    {
        $domain = trim($value);

        if ($domain === '') {
            throw new InvalidArgumentException('COOKIE_DOMAIN cannot be empty.');
        }

        return $domain;
    }

    /**
     * Validates the locale format (must be in format xx_XX).
     *
     * @param string $value
     * @return string
     *
     * @throws InvalidArgumentException
     */
    private function resolveLocale(string $value): string
    {
        $locale = trim($value);

        if (!preg_match('/^[a-z]{2}_[A-Z]{2}$/', $locale)) {
            throw new InvalidArgumentException("Invalid APP_LOCALE: '{$locale}' (expected format: xx_XX)");
        }

        return $locale;
    }

    /**
     * Validates and resolves the configured timezone.
     *
     * @param string $value
     * @return string
     *
     * @throws InvalidArgumentException
     */
    private function resolveTimezone(string $value): string
    {
        $timezone = trim($value);

        try {
            new \DateTimeZone($timezone);
        } catch (\Throwable) {
            throw new InvalidArgumentException("Invalid APP_TIMEZONE: '{$timezone}'");
        }

        return $timezone;
    }

    /**
     * Ensures the session prefix is non-empty and safe.
     *
     * @param string $value
     * @return string
     *
     * @throws InvalidArgumentException
     */
    private function resolveSessionPrefix(string $value): string
    {
        $prefix = trim($value);

        if ($prefix === '') {
            throw new InvalidArgumentException('SESSION_PREFIX cannot be empty.');
        }

        return $prefix;
    }

    /**
     * Validates and resolves the application version.
     *
     * @param string $value
     * @return string
     *
     * @throws InvalidArgumentException
     */
    private function resolveVersion(string $value): string
    {
        $version = trim($value);

        if (!preg_match('/^\d+\.\d+\.\d+$/', $version)) {
            throw new InvalidArgumentException("Invalid APP_VERSION: '{$version}' (expected format: x.x.x)");
        }

        return $version;
    }
}
