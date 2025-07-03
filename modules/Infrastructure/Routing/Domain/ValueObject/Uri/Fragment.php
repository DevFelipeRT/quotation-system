<?php

declare(strict_types=1);

namespace Routing\Domain\ValueObject\Uri;

/**
 * Represents the fragment component of a URI as an immutable value object.
 *
 * This object encapsulates the fragment identifier (after "#"),
 * ensuring it is properly encoded.
 */
final class Fragment
{
    private readonly string $value;

    /**
     * @param string $value The raw fragment string, without the leading "#".
     */
    public function __construct(string $value)
    {
        // The fragment can contain reserved characters, which should be percent-encoded.
        $this->value = preg_replace_callback(
            '/(?:[^a-zA-Z0-9_\-.~!$&\'()*+,;=:@\/?%]+|%(?![A-Fa-f0-9]{2}))/',
            fn ($matches) => rawurlencode($matches[0]),
            $value
        );
    }

    /**
     * Returns the properly encoded fragment string.
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Magic method to allow casting the object to a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Checks if two Fragment objects are equal.
     *
     * @param self $other
     * @return bool
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}