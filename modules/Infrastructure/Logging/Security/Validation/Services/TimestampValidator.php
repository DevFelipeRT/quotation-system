<?php

declare(strict_types=1);

namespace Logging\Security\Validation\Services;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * TimestampValidator
 *
 * Validates log timestamp values, ensuring they are instances of DateTimeImmutable.
 * Throws a InvalidArgumentException on failure.
 */
final class TimestampValidator
{
    /**
     * Validates a timestamp value.
     *
     * Ensures the value is an instance of DateTimeImmutable.
     *
     * @param mixed $date The value to validate as a timestamp.
     * @return DateTimeImmutable The validated timestamp.
     *
     * @throws InvalidArgumentException If the value is not a valid DateTimeImmutable instance.
     */
    public function validate($date): DateTimeImmutable
    {
        if (!($date instanceof DateTimeImmutable)) {
            throw new InvalidArgumentException('Invalid timestamp object.');
        }

        return $date;
    }
}
