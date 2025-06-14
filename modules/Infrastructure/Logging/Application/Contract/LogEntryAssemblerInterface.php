<?php

declare(strict_types=1);

namespace Logging\Application\Contract;

use Logging\Domain\ValueObject\Contract\LogEntryInterface;
use PublicContracts\Logging\LoggableInputInterface;

/**
 * Defines the contract for assembling LogEntry objects from loggable inputs.
 *
 * This interface ensures that any input satisfying LoggableInputInterface
 * can be converted into a structured LogEntry for logging purposes.
 */
interface LogEntryAssemblerInterface
{
    /**
     * Assembles a LogEntry instance from a given loggable input.
     *
     * @param LoggableInputInterface $input
     * @return LogEntryInterface
     */
    public function assembleFromInput(LoggableInputInterface $input): LogEntryInterface;
}
