<?php

namespace Config\App;

use Config\Env\EnvLoader;

/**
 * AppConfig
 *
 * Provides immutable access to application-level configuration derived from
 * environment variables. Includes metadata such as environment type, debug mode,
 * cookie domain, and application identity.
 */
class AppConfig
{
    /**
     * Human-readable application name.
     */
    private string $applicationName;

    /**
     * Current execution environment (e.g., 'development', 'production').
     */
    private string $environment;

    /**
     * Whether debug mode is enabled.
     */
    private bool $debug;

    /**
     * Cookie domain (for session/cookie scoping).
     */
    private string $cookieDomain;

    /**
     * Application locale (for i18n, formatting, etc.).
     */
    private string $locale;

    /**
     * Timezone identifier (e.g., 'UTC', 'America/Sao_Paulo').
     */
    private string $timezone;

    /**
     * Session prefix to isolate session variables by system instance.
     */
    private string $sessionPrefix;

    /**
     * Initializes application metadata configuration.
     *
     * @param EnvLoader $env Environment loader for secure access.
     */
    public function __construct(EnvLoader $env)
    {
        $this->applicationName = 'Quotation System'; // Hardcoded metadata
        $this->environment     = $env->getRequired('APP_ENV');
        $this->debug           = $env->getRequired('APP_DEBUG') === 'true';
        $this->cookieDomain    = $env->getRequired('COOKIE_DOMAIN');
        $this->locale          = $env->getRequired('APP_LOCALE');
        $this->timezone        = $env->getRequired('APP_TIMEZONE');
        $this->sessionPrefix   = $env->getRequired('SESSION_PREFIX');
    }

    public function name(): string
    {
        return $this->applicationName;
    }

    public function env(): string
    {
        return $this->environment;
    }

    public function isDevelopment(): bool
    {
        return $this->environment === 'development';
    }

    public function isDebug(): bool
    {
        return $this->debug;
    }

    public function cookieDomain(): string
    {
        return $this->cookieDomain;
    }

    public function locale(): string
    {
        return $this->locale;
    }

    public function timezone(): string
    {
        return $this->timezone;
    }

    public function sessionPrefix(): string
    {
        return $this->sessionPrefix;
    }
}
