<?php

declare(strict_types=1);

namespace Persistence\Domain\ValueObject;

use InvalidArgumentException;
use JsonSerializable;
use Persistence\Domain\Support\CredentialsSecurity;

/**
 * Encapsulates a sensitive database secret (e.g., password).
 *
 * This class prevents accidental exposure of secret values
 * via debug tools, string casting, serialization, or logging.
 *
 * Secrets must be accessed explicitly through the reveal() method.
 *
 * @immutable
 */
final class DatabaseSecret implements JsonSerializable
{
    use CredentialsSecurity;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        private readonly string $value
    ) {
        if (trim($value) === '') {
            throw new InvalidArgumentException('Database secret cannot be empty.');
        }
    }

    /**
     * Explicitly reveals the internal secret value.
     * Use with caution and never log the result.
     *
     * @return string
     */
    public function reveal(): string
    {
        return $this->value;
    }

    /**
     * Provides a masked preview for debugging tools.
     *
     * @return array<string, mixed>
     */
    private function getSafeDebugInfo(): array
    {
        return ['value' => '[hidden]'];
    }
}
