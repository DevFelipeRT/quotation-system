<?php

namespace Config\App;

use Config\Env\EnvLoader;

/**
 * Class AppConfig
 *
 * Provides immutable access to core application configuration, loaded from environment variables.
 *
 * @package Config\App
 */
class AppConfig
{
    private const DEFAULT_APP_NAME = 'Quotation System';

    /** @var string */
    private string $applicationName;

    /** @var string */
    private string $environment;

    /** @var bool */
    private bool $debug;

    /** @var string */
    private string $cookieDomain;

    /** @var string */
    private string $locale;

    /** @var string */
    private string $timezone;

    /** @var string */
    private string $sessionPrefix;

    /**
     * AppConfig constructor.
     *
     * @param EnvLoader $env Environment variable loader.
     */
    public function __construct(EnvLoader $env)
    {
        $this->applicationName = self::DEFAULT_APP_NAME;
        $this->environment     = $env->getRequired('APP_ENV');
        $this->debug           = strtolower($env->getRequired('APP_DEBUG')) === 'true';
        $this->cookieDomain    = $env->getRequired('COOKIE_DOMAIN');
        $this->locale          = $env->getRequired('APP_LOCALE');
        $this->timezone        = $env->getRequired('APP_TIMEZONE');
        $this->sessionPrefix   = $env->getRequired('SESSION_PREFIX');
    }

    /** @return string */
    public function getName(): string
    {
        return $this->applicationName;
    }

    /** @return string */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /** @return bool */
    public function isDevelopment(): bool
    {
        return $this->environment === 'development';
    }

    /** @return bool */
    public function isDebugMode(): bool
    {
        return $this->debug;
    }

    /** @return string */
    public function getCookieDomain(): string
    {
        return $this->cookieDomain;
    }

    /** @return string */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /** @return string */
    public function getTimezone(): string
    {
        return $this->timezone;
    }

    /** @return string */
    public function getSessionPrefix(): string
    {
        return $this->sessionPrefix;
    }
}
