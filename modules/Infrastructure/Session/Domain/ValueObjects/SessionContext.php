<?php

declare(strict_types=1);

namespace Session\Domain\ValueObjects;

use Session\Exceptions\InvalidSessionContextException;

/**
 * Represents contextual session metadata, such as locale and authentication state.
 *
 * Immutable and validated on construction.
 */
final class SessionContext
{
    private string $locale;
    private bool $authenticated;

    /**
     * Constructs a SessionContext instance.
     *
     * @param string $locale Expected format: xx_XX (e.g. pt_BR)
     * @param bool   $authenticated Whether the session is authenticated
     *
     * @throws InvalidSessionContextException If locale format is invalid
     */
    public function __construct(string $locale, bool $authenticated)
    {
        $this->locale = $this->validateLocale($locale);
        $this->authenticated = $authenticated;
    }

    /**
     * Returns the preferred locale of the session.
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Returns whether the session is authenticated.
     */
    public function isAuthenticated(): bool
    {
        return $this->authenticated;
    }

    /**
     * Validates and returns the session locale.
     *
     * @param string $locale
     * @return string
     *
     * @throws InvalidSessionContextException
     */
    private function validateLocale(string $locale): string
    {
        if (!preg_match('/^[a-z]{2}_[A-Z]{2}$/', $locale)) {
            throw new InvalidSessionContextException("Invalid locale format: '{$locale}' (expected format: xx_XX)");
        }

        return $locale;
    }
}
