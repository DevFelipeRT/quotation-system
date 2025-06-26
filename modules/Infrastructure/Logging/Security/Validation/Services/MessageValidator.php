<?php

declare(strict_types=1);

namespace Logging\Security\Validation\Services;

use Logging\Security\Validation\Tools\StringValidationTrait;
use PublicContracts\Logging\Config\ValidationConfigInterface;
use Logging\Domain\Exception\InvalidLogMessageException;

/**
 * MessageValidator
 *
 * Validates and normalizes log message strings.
 * Enforces non-emptiness, forbidden character policy, maximum length,
 * and correct terminal punctuation. Throws a domain-specific exception on failure.
 */
final class MessageValidator
{
    use StringValidationTrait;

    private int $maxLength;
    private string $forbiddenCharsRegex;
    private string $terminalPunctuationRegex;

    /**
     * @param ValidationConfigInterface $config Configuration provider for message validation.
     */
    public function __construct(ValidationConfigInterface $config)
    {
        $this->maxLength               = $config->logMessageMaxLength();
        $this->forbiddenCharsRegex     = $config->stringForbiddenCharsRegex();
        $this->terminalPunctuationRegex = $config->logMessageTerminalPunctuationRegex();
    }

    /**
     * Validates a log message string.
     *
     * Enforces trimming, non-emptiness, forbidden characters, and maximum length.
     * Normalizes the message by capitalizing the first letter and appending terminal
     * punctuation if missing, according to configuration.
     *
     * @param string   $message   The log message to validate.
     * @param int|null $maxLength Maximum allowed message length (overrides default).
     *
     * @return string             The validated and normalized log message.
     *
     * @throws InvalidLogMessageException If the message is invalid, too long, or has forbidden formatting.
     */
    public function validate(string $message, ?int $maxLength = null): string
    {
        $max = $maxLength ?? $this->maxLength;
        $msg = $this->cleanString($message);

        if ($this->isEmpty($msg)) {
            throw InvalidLogMessageException::empty();
        }
        if ($this->hasForbiddenChars($msg, $this->forbiddenCharsRegex)) {
            throw InvalidLogMessageException::invalidCharacters();
        }
        if ($this->exceedsMaxLength($msg, $max)) {
            throw InvalidLogMessageException::tooLong();
        }

        // Capitalize first letter (Unicode safe)
        $msg = mb_strtoupper(mb_substr($msg, 0, 1)) . mb_substr($msg, 1);

        // Append terminal punctuation if missing
        if (!preg_match($this->terminalPunctuationRegex, $msg)) {
            $msg .= '.';
        }

        return $msg;
    }
}
