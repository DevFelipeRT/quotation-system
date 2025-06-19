<?php

declare(strict_types=1);

namespace Logging\Infrastructure;

use Logging\Application\Contract\LogEntryAssemblerInterface;
use Logging\Domain\Exception\InvalidLogLevelException;
use Logging\Domain\Security\Contract\LogSecurityInterface;
use Logging\Domain\ValueObject\Contract\LogEntryInterface;
use Logging\Domain\ValueObject\Contract\LoggableInputInterface;
use Logging\Domain\ValueObject\LogEntry;
use Logging\Domain\ValueObject\LogLevel;
use Logging\Domain\ValueObject\LogMessage;
use Logging\Domain\ValueObject\LogContext;
use Logging\Domain\ValueObject\LogChannel;
use Logging\Infrastructure\Exception\LogEntryAssemblyException;
use PublicContracts\Logging\Config\AssemblerConfigInterface;

/**
 * LogEntryAssembler
 *
 * Assembles immutable LogEntry objects from generic loggable input contracts,
 * supporting fallback default values for input parameters, except message and timestamp.
 * Allows injection of custom log level definitions for LogLevel construction.
 */
final class LogEntryAssembler implements LogEntryAssemblerInterface
{
    private readonly LogSecurityInterface $security;

    /** @var string|null */
    private readonly ?string $defaultLevel;

    /** @var array<string, string>|null */
    private readonly ?array $defaultContext;

    /** @var string|null */
    private readonly ?string $defaultChannel;

    /** @var string[]|null */
    private readonly ?array $customLogLevels;

    /** @var string|null */
    private readonly ?string $maskToken;

    /**
     * @param LogSecurityInterface $security
     * @param AssemblerConfigInterface $config
     */
    public function __construct(
        LogSecurityInterface $security,
        AssemblerConfigInterface $config
    ) {
        $this->security        = $security;
        $this->defaultLevel    = $config->defaultLevel();
        $this->defaultContext  = $config->defaultContext();
        $this->defaultChannel  = $config->defaultChannel();
        $this->customLogLevels = $config->customLogLevels();
        $this->maskToken       = $config->maskToken();
    }

    /**
     * Converts a generic loggable input into a fully validated LogEntry.
     *
     * @param LoggableInputInterface $input
     * @return LogEntryInterface
     *
     * @throws LogEntryAssemblyException If any part of the assembly fails.
     */
    public function assembleFromInput(LoggableInputInterface $input): LogEntryInterface
    {
        try {
            $level     = $this->buildLogLevel($input);
            $message   = $this->buildLogMessage($input);
            $context   = $this->buildLogContext($input);
            $channel   = $this->buildLogChannel($input);
            $timestamp = $input->getTimestamp();

            return new LogEntry(
                level: $level,
                message: $message,
                context: $context,
                channel: $channel,
                timestamp: $timestamp
            );
        } catch (\Throwable $e) {
            throw LogEntryAssemblyException::fromPrevious($input, $e);
        }
    }

    /**
     * Instantiates the LogLevel value object, using fallback if necessary.
     *
     * @param LoggableInputInterface $input
     * @return LogLevel
     * @throws InvalidLogLevelException
     */
    private function buildLogLevel(LoggableInputInterface $input): LogLevel
    {
        $level = $input->getLevel();

        try {
            return new LogLevel(
                (string) $level,
                $this->security,
                $this->customLogLevels
            );
        } catch (InvalidLogLevelException $e) {
            if ($this->defaultLevel !== null && $level !== $this->defaultLevel) {
                return new LogLevel(
                    (string) $this->defaultLevel,
                    $this->security,
                    $this->customLogLevels
                );
            }
            throw $e;
        }
    }

    /**
     * Instantiates the LogMessage value object using a message sanitized in the context of the input channel.
     *
     * The message is sanitized according to both its own content and the associated channel, ensuring
     * that sensitive information is not exposed even if channel and message values are related.
     * No fallback is applied: a valid message must be present in the input.
     *
     * @param LoggableInputInterface $input
     * @return LogMessage
     */
    private function buildLogMessage(LoggableInputInterface $input): LogMessage
    {
        $message = $this->sanitizeMassageByChannel($input);
        return new LogMessage(
            $message,
            $this->security
        );
    }

    /**
     * Instantiates the LogContext value object, using fallback if necessary.
     */
    private function buildLogContext(LoggableInputInterface $input): LogContext
    {
        $context = $input->getContext();
        if ($context === null && $this->defaultContext !== null) {
            $context = $this->defaultContext;
        }
        return new LogContext(
            (array) $context,
            $this->security
        );
    }

    /**
     * Instantiates the LogChannel value object, using fallback if necessary.
     */
    private function buildLogChannel(LoggableInputInterface $input): LogChannel
    {
        $channel = $input->getChannel();
        if ($channel === null && $this->defaultChannel !== null) {
            $channel = $this->defaultChannel;
        }
        return new LogChannel(
            (string) $channel,
            $this->security
        );
    }

    /**
     * Sanitizes the log message in the context of its channel using the security policy.
     *
     * This method ensures that the message is sanitized based on the associated channel,
     * preventing exposure of sensitive information when the channel indicates a confidential context.
     *
     * @param LoggableInputInterface $input
     * @return string Sanitized message safe for logging.
     */
    private function sanitizeMassageByChannel(LoggableInputInterface $input): string
    {
        $channel = $input->getChannel() ?? '';
        $message = $input->getMessage();
        $sanitizedArray = $this->security->sanitize([$channel => $message], $this->maskToken);
        return array_values($sanitizedArray)[0];
    }

}
