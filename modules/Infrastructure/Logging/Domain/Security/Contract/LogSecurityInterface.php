<?php

declare(strict_types=1);

namespace Logging\Domain\Security\Contract;

/**
 * Facade interface for all domain security operations within the logging domain.
 *
 * This interface centralizes and exposes all validation and sanitization routines
 * required for the safe handling of log-related input data. By extending both
 * {@see ValidatorInterface} and {@see SanitizerInterface}, it guarantees that
 * all Value Objects and services depending on security operations have access to
 * a unified, consistent API for enforcing domain security policies.
 *
 * Responsibilities:
 * - Exposes all methods required for input validation and sanitization, as defined
 *   by the underlying interfaces.
 * - Ensures that all Value Objects and components in the logging domain invoke
 *   security routines through this contract, promoting uniformity and reliability.
 * - Facilitates maintenance, extension, and testing by decoupling security logic
 *   from domain models.
 *
 * Usage:
 * All log-related Value Objects MUST depend exclusively on this interface for
 * validating and sanitizing input, ensuring robust protection against unsafe
 * or malformed data throughout the domain.
 *
 * @see ValidatorInterface
 * @see SanitizerInterface
 */
interface LogSecurityInterface extends ValidatorInterface, SanitizerInterface
{
}
