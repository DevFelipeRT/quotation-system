<?php

declare(strict_types=1);

namespace Config\Modules\Logging;

/**
 * LogSecurityConfig
 *
 * Encapsulates security-related configuration for the logging module,
 * loading custom sensitive keys and regex patterns from centralized config classes.
 *
 * All sensitive key and pattern lists are provided by hardcoded configuration objects,
 * ensuring consistent application-wide masking and detection.
 */
final class LogSecurityConfig
{
    /**
     * @var string[] Custom sensitive keys for log sanitization.
     */
    private array $sensitiveKeys;

    /**
     * @var string[] Custom sensitive regex patterns for value sanitization.
     */
    private array $sensitivePatterns;

    /**
     * @var int|null Optional recursion depth limit for sanitizer.
     */
    private ?int $maxDepth;

    /**
     * @var string|null Optional custom mask token.
     */
    private ?string $maskToken;

    /**
     * LogSecurityConfig constructor.
     *
     * All lists are loaded from CustomSensitiveKeys and CustomSensitivePatterns,
     * which are the single source of truth for this configuration.
     *
     * @param int|null $maxDepth    Maximum recursion depth for the sanitizer.
     * @param string|null $maskToken Token to be used for masking sensitive values.
     */
    public function __construct(
        ?int $maxDepth = null,
        ?string $maskToken = null
    ) {
        $this->sensitiveKeys     = CustomSensitiveKeys::list();
        $this->sensitivePatterns = CustomSensitivePatterns::list();
        $this->maxDepth          = $maxDepth;
        $this->maskToken         = $maskToken;
    }

    /**
     * Returns the custom sensitive keys for the sanitizer.
     *
     * @return string[]
     */
    public function sensitiveKeys(): array
    {
        return $this->sensitiveKeys;
    }

    /**
     * Returns the custom sensitive regex patterns for the sanitizer.
     *
     * @return string[]
     */
    public function sensitivePatterns(): array
    {
        return $this->sensitivePatterns;
    }

    /**
     * Returns the optional maximum recursion depth.
     *
     * @return int|null
     */
    public function maxDepth(): ?int
    {
        return $this->maxDepth;
    }

    /**
     * Returns the optional mask token.
     *
     * @return string|null
     */
    public function maskToken(): ?string
    {
        return $this->maskToken;
    }

    /**
     * Exports this configuration as an array suitable for LoggingKernel or sanitizer instantiation.
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
