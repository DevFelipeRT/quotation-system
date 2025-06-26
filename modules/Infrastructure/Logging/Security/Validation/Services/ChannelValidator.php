<?php

declare(strict_types=1);

namespace Logging\Security\Validation\Services;

use Logging\Security\Validation\Tools\StringValidationTrait;
use PublicContracts\Logging\Config\ValidationConfigInterface;
use Logging\Domain\Exception\InvalidLogChannelException;

/**
 * ChannelValidator
 *
 * Responsible for validating log channel names according to domain policy.
 * Enforces non-emptiness, forbidden character rules, and normalization to lowercase.
 * Throws a domain-specific exception in case of failure.
 */
final class ChannelValidator
{
    use StringValidationTrait;

    private readonly int $maxLength;
    private readonly string $forbiddenCharsRegex;

    /**
     * @param ValidationConfigInterface $config Configuration provider for channel validation.
     */
    public function __construct(ValidationConfigInterface $config)
    {
        $this->maxLength = $config->channelMaxLength();
        $this->forbiddenCharsRegex = $config->stringForbiddenCharsRegex();
    }

    /**
     * Validates a log channel name.
     *
     * Enforces trimming, non-emptiness, and the forbidden character policy.
     * Normalizes the channel name to lowercase on success.
     *
     * @param string $channel Log channel name to validate.
     * @return string         The validated and normalized channel name.
     *
     * @throws InvalidLogChannelException If the channel is empty, exceeds maximum length or contains forbidden characters.
     */
    public function validate(string $channel): string
    {
        $clean = $this->cleanString($channel);

        if ($this->isEmpty($clean)) {
            throw InvalidLogChannelException::empty();
        }
        if (mb_strlen($clean) > $this->maxLength) {
            throw InvalidLogChannelException::tooLong($this->maxLength);
        }
        if ($this->hasForbiddenChars($clean, $this->forbiddenCharsRegex)) {
            throw InvalidLogChannelException::invalidCharacters();
        }

        return mb_strtolower($clean);
    }
}
