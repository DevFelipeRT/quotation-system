<?php

namespace Config\App;

/**
 * Defines default immutable configuration values for the application.
 *
 * Used by AppConfig as fallback when environment variables are missing or invalid.
 */
final class DefaultAppConfig
{
    public const APPLICATION_NAME = 'Quotation System';
    public const VERSION = 'undefined';
    public const ENVIRONMENT = 'production';
    public const DEBUG = false;
    public const COOKIE_DOMAIN = 'localhost';
    public const LOCALE = 'pt_BR';
    public const TIMEZONE = 'UTC';
    public const SESSION_PREFIX = 'qtsession_';

    /**
     * Prevent instantiation.
     */
    private function __construct()
    {
        // static-only class
    }
}
