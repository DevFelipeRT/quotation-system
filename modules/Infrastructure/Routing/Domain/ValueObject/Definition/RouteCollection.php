<?php

declare(strict_types=1);

namespace Routing\Domain\ValueObject\Definition;

use IteratorAggregate;
use Countable;
use Traversable;
use ArrayIterator;
use Routing\Domain\ValueObject\Definition\RouteDefinition;

/**
 * Represents an immutable collection of route definitions.
 *
 * As a First-Class Collection, this value object encapsulates an array of
 * RouteDefinition objects, providing a typesafe API for managing the
 * collection. It is iterable and countable.
 */
final class RouteCollection implements IteratorAggregate, Countable
{
    /**
     * @var array<int, RouteDefinition>
     */
    private readonly array $definitions;

    /**
     * @param RouteDefinition ...$definitions A list of route definitions to initialize the collection.
     */
    public function __construct(RouteDefinition ...$definitions)
    {
        $this->definitions = $definitions;
    }

    /**
     * Returns a new collection instance with the added route definition.
     *
     * @param RouteDefinition $definition The route definition to add.
     * @return self
     */
    public function add(RouteDefinition $definition): self
    {
        $newDefinitions = $this->definitions;
        $newDefinitions[] = $definition;

        return new self(...$newDefinitions);
    }

    /**
     * Allows the collection to be used in a `foreach` loop.
     *
     * @return Traversable<int, RouteDefinition>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->definitions);
    }

    /**
     * Allows the collection to be counted using the count() function.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->definitions);
    }

    /**
     * Checks if the collection is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }
}