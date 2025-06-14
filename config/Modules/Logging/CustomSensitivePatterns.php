<?php

declare(strict_types=1);

namespace Config\Modules\Logging;

/**
 * CustomSensitivePatterns
 *
 * Centralizes additional regex patterns for sensitive data detection in log sanitization.
 * Should be merged with the sanitizer's default patterns.
 *
 * Intended exclusively for configuration purposes.
 */
final class CustomSensitivePatterns
{
    /**
     * Returns the hardcoded list of additional sensitive data patterns (regex).
     *
     * @return string[]
     */
    public static function list(): array
    {
        return [
            // Example: Brazilian cellphone (11 digits, may include country code)
            '/\b(?:\+?55)?\s?\d{2}\s?\d{4,5}-?\d{4}\b/',

            // Example: simple credit card patterns not in defaults
            '/\b4[0-9]{12}(?:[0-9]{3})?\b/',    // Visa
            '/\b5[1-5][0-9]{14}\b/',           // MasterCard
            '/\b3[47][0-9]{13}\b/',            // American Express

            // Example: RG (Brazilian ID, numbers with optional "X" at end)
            '/\b\d{1,2}\.?\d{3}\.?\d{3}-?[\dXx]\b/',

            // Example: custom app token prefix
            '/app_[a-z0-9]{32}/i',

            // Add more as your threat model or application evolves
        ];
    }
}
