<?php

declare(strict_types=1);

namespace Rendering\Domain\ValueObject\Shared;

use ArrayIterator;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use Rendering\Domain\Contract\Partial\PartialViewInterface;
use Rendering\Domain\Trait\Validation\PartialsValidationTrait;

/**
 * Represents an immutable, type-safe collection of partial view components.
 *
 * This Value Object ensures that all elements are valid instances of PartialViewInterface
 * and are indexed by a non-empty string identifier. It implements IteratorAggregate
 * and Countable to allow for easy iteration and counting of partials.
 */
final class PartialsCollection implements IteratorAggregate, Countable
{
    use PartialsValidationTrait;
    
    /**
     * @var array<string, PartialViewInterface> The collection of partials.
     */
    private readonly array $partials;

    /**
     * Constructs a new PartialsCollection instance.
     *
     * @param array<string, PartialViewInterface> $partials An associative array of partials.
     * @throws InvalidArgumentException if the partials array is invalid.
     */
    public function __construct(array $partials)
    {
        $this->validatePartials($partials);
        $this->partials = $partials;
    }

    /**
     * Retrieves a partial by its unique identifier.
     *
     * @param string $identifier The identifier of the partial to retrieve.
     * @return PartialViewInterface
     * @throws InvalidArgumentException if no partial with the given identifier exists.
     */
    public function get(string $identifier): PartialViewInterface
    {
        if (!$this->has($identifier)) {
            throw new InvalidArgumentException("Partial with identifier '{$identifier}' not found in the collection.");
        }
        return $this->partials[$identifier];
    }

    /**
     * Checks if a partial with the given identifier exists in the collection.
     *
     * @param string $identifier The identifier to check.
     * @return bool True if the partial exists, false otherwise.
     */
    public function has(string $identifier): bool
    {
        return isset($this->partials[$identifier]);
    }

    /**
     * Returns the entire collection of partials as an associative array.
     *
     * @return array<string, PartialViewInterface>
     */
    public function all(): array
    {
        return $this->partials;
    }

    /**
     * Returns the number of partials in the collection.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->partials);
    }

    /**
     * Allows the collection to be iterated over using foreach.
     *
     * @return ArrayIterator<string, PartialViewInterface>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->partials);
    }

    /**
     * Converts the collection to an associative array.
     *
     * @return array<string, PartialViewInterface>
     */
    public function toArray(): array
    {
        return $this->all();
    }
}
