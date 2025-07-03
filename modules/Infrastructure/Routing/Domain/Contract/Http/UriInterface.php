<?php

declare(strict_types=1);

namespace Routing\Domain\Contract\Http;

/**
 * Describes a Uniform Resource Identifier (URI).
 *
 * This contract provides a standardized way to access the various parts of a
 * URI. Its design is compatible with the principles defined in PSR-7.
 */
interface UriInterface
{
    /**
     * Retrieves the scheme component of the URI.
     *
     * @return string The URI scheme (e.g., "http", "https").
     */
    public function getScheme(): string;

    /**
     * Retrieves the authority component of the URI.
     *
     * @return string The URI authority, in "[user-info@]host[:port]" format.
     */
    public function getAuthority(): string;

    /**
     * Retrieves the user information component of the URI.
     *
     * @return string The URI user information, in "user[:password]" format.
     */
    public function getUserInfo(): string;

    /**
     * Retrieves the host component of the URI.
     *
     * @return string The URI host.
     */
    public function getHost(): string;

    /**
     * Retrieves the port component of the URI.
     *
     * @return int|null The URI port or null if no port is specified.
     */
    public function getPort(): ?int;

    /**
     * Retrieves the path component of the URI.
     *
     * @return string The URI path.
     */
    public function getPath(): string;

    /**
     * Retrieves the query string of the URI.
     *
     * @return string The URI query string (e.g., "key=value&another=true").
     */
    public function getQuery(): string;

    /**
     * Retrieves the fragment component of the URI.
     *
     * @return string The URI fragment (the part after the "#").
     */
    public function getFragment(): string;

    /**
     * Returns the string representation of the entire URI.
     *
     * @return string
     */
    public function __toString(): string;
}