<?php

declare(strict_types=1);

namespace Logging\Infrastructure\Exception;

use Logging\Domain\ValueObject\Contract\LoggableInputInterface;
use Exception;
use Throwable;


/**
 * Thrown when the LogEntryAssembler fails to assemble a LogEntry
 * from a LoggableInputInterface, encapsulating the original cause.
 */
final class LogEntryAssemblyException extends Exception
{
    private ?LoggableInputInterface $input;

    public function __construct(
        string $message,
        ?LoggableInputInterface $input = null,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->input = $input;
    }

    /**
     * Factory for assembly error with chained cause.
     *
     * @param LoggableInputInterface $input
     * @param Throwable $previous
     * @return self
     */
    public static function fromPrevious(LoggableInputInterface $input, Throwable $previous): self
    {
        $msg = sprintf(
            'Failed to assemble LogEntry from input (level: %s, message: %s): %s',
            $input->getLevel() ?? '[null]',
            $input->getMessage() ?? '[null]',
            $previous->getMessage()
        );
        return new self($msg, $input, 0, $previous);
    }

    /**
     * Returns the loggable input that caused the error, if available.
     */
    public function getInput(): ?LoggableInputInterface
    {
        return $this->input;
    }
}
