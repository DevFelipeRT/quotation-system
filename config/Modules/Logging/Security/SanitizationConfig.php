<?php

declare(strict_types=1);

namespace Config\Modules\Logging\Security;

use PublicContracts\Logging\Config\SanitizationConfigInterface;

/**
 * SanitizationConfig
 *
 * Encapsulates all sanitization settings for the logging module.
 *
 * - Loads custom sensitive keys from CustomSensitiveKeys.
 * - Loads custom sensitive patterns from CustomSensitivePatterns.
 * - Uses DefaultSanitizationValues enum for recursion depth and mask token.
 */
final class SanitizationConfig implements SanitizationConfigInterface
{
    /**
     * @var string[] List of keys whose values must be masked or removed.
     */
    private array $sensitiveKeys;

    /**
     * @var string[] List of regex patterns identifying sensitive values.
     */
    private array $sensitivePatterns;

    /**
     * @var int Recursion depth limit for nested sanitization.
     */
    private int $maxDepth;

    /**
     * @var string Token used to mask sensitive values.
     */
    private string $maskToken;
    
    /**
     * Regular expression pattern for forbidden characters or strings in the mask token.
     *
     * @var string
     */
    private string $maskTokenForbiddenPattern;

    /**
     * Constructs the config by loading all values from enums and custom sources.
     */
    public function __construct()
    {
        $this->sensitiveKeys             = CustomSensitiveKeys::list();
        $this->sensitivePatterns         = CustomSensitivePatterns::list();
        $this->maxDepth                  = DefaultSanitizationValues::MAX_DEPTH->getValue();
        $this->maskToken                 = DefaultSanitizationValues::MASK_TOKEN->getValue();
        $this->maskTokenForbiddenPattern = DefaultSanitizationValues::MASK_TOKEN_FORBIDDEN_PATTERN->getValue();
    }

    /**
     * Returns the list of keys to be sanitized.
     *
     * @return string[]
     */
    public function sensitiveKeys(): array
    {
        return $this->sensitiveKeys;
    }

    /**
     * Returns the list of regex patterns used to identify sensitive values.
     *
     * @return string[]
     */
    public function sensitivePatterns(): array
    {
        return $this->sensitivePatterns;
    }

    /**
     * Returns the recursion depth limit for nested structures.
     *
     * @return int
     */
    public function maxDepth(): int
    {
        return $this->maxDepth;
    }

    /**
     * Returns the token used to mask sensitive data.
     *
     * @return string
     */
    public function maskToken(): string
    {
        return $this->maskToken;
    }

    /**
     * Returns the regular expression pattern for forbidden characters or strings in the mask token.
     *
     * @return string
     */
    public function maskTokenForbiddenPattern(): string
    {
        return $this->maskTokenForbiddenPattern;
    }

    /**
     * Exports this configuration as an associative array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'sensitive_keys'     => $this->sensitiveKeys,
            'sensitive_patterns' => $this->sensitivePatterns,
            'max_depth'          => $this->maxDepth,
            'mask_token'         => $this->maskToken,
        ];
    }
}
