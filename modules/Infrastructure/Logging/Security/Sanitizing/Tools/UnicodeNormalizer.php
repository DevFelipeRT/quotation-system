<?php

declare(strict_types=1);

namespace Logging\Security\Sanitizing\Tools;

/**
 * UnicodeNormalizer
 *
 * Domain service responsible for normalizing strings to Unicode FORM_KC.
 * Ensures consistent handling of Unicode data across the application,
 * with graceful fallback if the intl extension is not available.
 */
final class UnicodeNormalizer
{
    /**
     * Normalizes the given string to Unicode FORM_KC.
     * Falls back to the original string if the Normalizer class is not available.
     *
     * @param string $value
     * @return string
     */
    public function normalize(string $value): string
    {
        if (class_exists('Normalizer')) {
            return \Normalizer::normalize($value, \Normalizer::FORM_KC) ?: $value;
        }
        return $value;
    }
}
