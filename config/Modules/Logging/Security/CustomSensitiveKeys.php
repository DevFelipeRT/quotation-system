<?php

declare(strict_types=1);

namespace Config\Modules\Logging\Security;

/**
 * CustomSensitiveKeys
 *
 * Centralizes additional sensitive keys for log sanitization,
 * to be merged with domain and infrastructure defaults.
 *
 * This class is intended for configuration purposes only.
 */
final class CustomSensitiveKeys
{
    /**
     * Returns the hardcoded list of additional sensitive keys for your application.
     *
     * @return string[]
     */
    public static function list(): array
    {
        return [
            'auth_token',
            'refresh_token',
            'jwt',
            'pin',
            'creditcard',
            'ccv',
            'accesskey',
            'apikey',
            'secretkey',
            'biometria',
            'passport',
            'telefone',
            // Add more keys here as needed for your security policy
        ];
    }

    private function __construct()
    {
    }
}
