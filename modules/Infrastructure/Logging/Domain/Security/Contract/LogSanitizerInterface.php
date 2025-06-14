<?php

declare(strict_types=1);

namespace Logging\Domain\Security\Contract;

/**
 * Contract for domain-level input sanitizers responsible for masking or redacting sensitive
 * information from log contexts, SQL parameter bindings, or any structure to be persisted or transmitted.
 *
 * Architectural Notes:
 * - This contract MUST be implemented in the Domain layer and referenced by all Value Objects (VOs)
 *   that represent loggable data. Its use in VOs is mandatory to enforce security at the domain level.
 * - All other layers (Application, Infrastructure, Presentation, etc.) MAY consume or compose with
 *   this contract to further enforce or reuse the domain’s masking policies, but SHOULD NOT
 *   implement their own independent logic for sensitive data detection or masking.
 * - The implementation is responsible for consistently masking, redacting, or removing
 *   passwords, secrets, tokens, personal data, or any other sensitive information according to
 *   organizational and regulatory requirements.
 *
 * Security Warning:
 * - Logging of sensitive information is a common source of security breaches and regulatory violations.
 *   The implementation of this contract MUST be audited and kept up to date with business and
 *   compliance requirements.
 */
interface LogSanitizerInterface
{
    /**
     * Sanitizes sensitive keys and values from the provided input array, 
     * returning a copy with all confidential data masked or removed.
     * 
     * This method MUST be called by all log-related Value Objects prior to storing, exposing, or transmitting data.
     *
     * @param array<string, mixed> $input
     * @return array<string, mixed> Sanitized array safe for logging or export.
     */
    public function sanitize(array $input): array;

    /**
     * Sanitizes sensitive keys and values from SQL parameter bindings, 
     * returning a copy with all confidential data masked or removed.
     *
     * This method is designed for use in contexts where SQL query parameters may contain secrets,
     * and may be called from any layer (Domain, Infra, etc.).
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed> Sanitized array safe for logging or audit.
     */
    public function sanitizeSqlParams(array $params): array;
}
