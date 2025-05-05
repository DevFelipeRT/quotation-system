<?php

namespace App\Messaging\Domain;

/**
 * Contract for loggable messages that can specify a log channel.
 *
 * Extends the base MessageInterface with an optional channel accessor,
 * enabling message routing, filtering, or grouping in logging systems.
 */
interface LoggableMessageInterface extends MessageInterface
{
    /**
     * Returns the associated log channel (e.g., 'auth', 'billing', etc.), if defined.
     *
     * @return string|null
     */
    public function getChannel(): ?string;
}
