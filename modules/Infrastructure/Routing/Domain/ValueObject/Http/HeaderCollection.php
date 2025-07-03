<?php

declare(strict_types=1);

namespace Routing\Domain\ValueObject\Http;

use IteratorAggregate;
use Countable;
use Traversable;
use ArrayIterator;
use Routing\Domain\ValueObject\Http\Header;

/**
 * Represents an immutable, case-insensitive collection of HTTP headers.
 *
 * As a First-Class Collection, this object manages Header value objects.
 * It uses normalized keys internally to ensure that header access is
 * case-insensitive, as required by HTTP specifications.
 */
final class HeaderCollection implements IteratorAggregate, Countable
{
    /**
     * Headers stored with a normalized, Title-Case key.
     * @var array<string, Header>
     */
    private readonly array $headers;

    /**
     * @param Header ...$headers A list of headers to initialize the collection.
     */
    public function __construct(Header ...$headers)
    {
        $normalizedHeaders = [];
        foreach ($headers as $header) {
            $normalizedHeaders[$header->getNormalizedName()] = $header;
        }
        $this->headers = $normalizedHeaders;
    }

    /**
     * Returns a new collection instance with the specified header added or replaced.
     *
     * @param Header $header The header to add or replace.
     * @return self
     */
    public function withHeader(Header $header): self
    {
        $newHeaders = $this->headers;
        $newHeaders[$header->getNormalizedName()] = $header;

        // Reconstruct from the internal Header objects to maintain the correct class instance
        return new self(...array_values($newHeaders));
    }

    /**
     * Retrieves a header by its case-insensitive name.
     *
     * @param string $name The name of the header to retrieve.
     * @return Header|null The Header object or null if not found.
     */
    public function get(string $name): ?Header
    {
        $normalizedName = str_replace(' ', '-', ucwords(strtolower(str_replace('-', ' ', $name))));

        return $this->headers[$normalizedName] ?? null;
    }

    /**
     * Checks if a header exists by its case-insensitive name.
     *
     * @param string $name The name of the header to check.
     * @return bool
     */
    public function has(string $name): bool
    {
        return $this->get($name) !== null;
    }

    /**
     * Allows the collection to be used in a `foreach` loop.
     *
     * @return Traversable<string, Header>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->headers);
    }

    /**
     * Allows the collection to be counted using the count() function.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->headers);
    }
}