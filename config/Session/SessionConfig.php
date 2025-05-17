<?php

declare(strict_types=1);

namespace Config\Session;

/**
 * SessionConfig
 *
 * Exposes strongly-typed configuration for session handling.
 * Supports explicit configuration of the session driver and default locale.
 * For future drivers or options, add strongly-typed properties and accessors.
 */
final class SessionConfig
{
    /**
     * The session driver key to be used for session handling.
     * Only 'native' is currently implemented.
     *
     * @var string
     */
    private string $defaultDriver = 'native';

    /**
     * The default locale to be used for new sessions.
     * Must follow the pattern xx_XX (e.g. 'pt_BR').
     *
     * @var string
     */
    private string $defaultLocale = 'pt_BR';

    /**
     * Returns the default session driver key.
     *
     * @return string
     */
    public function defaultDriver(): string
    {
        return $this->defaultDriver;
    }

    /**
     * Returns the default locale to be used for new sessions.
     *
     * @return string
     */
    public function defaultLocale(): string
    {
        return $this->defaultLocale;
    }

    // Add further driver-specific properties and accessors as needed.
}
