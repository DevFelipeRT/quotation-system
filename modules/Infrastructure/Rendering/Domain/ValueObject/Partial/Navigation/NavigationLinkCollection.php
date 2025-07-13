<?php

declare(strict_types=1);

namespace Rendering\Domain\ValueObject\Partial\Navigation;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Rendering\Domain\Contract\Partial\Navigation\NavigationLinkInterface;
use Rendering\Domain\Trait\Validation\NavigationLinkValidationTrait;

/**
 * Represents an immutable, type-safe collection of navigation links.
 *
 * This Value Object ensures that all elements are valid instances of NavigationLinkInterface.
 * It implements IteratorAggregate and Countable to allow for easy iteration and counting.
 */
final class NavigationLinkCollection implements IteratorAggregate, Countable
{
    use NavigationLinkValidationTrait;

    /**
     * @var NavigationLinkInterface[] The collection of navigation links.
     */
    private readonly array $links;

    /**
     * Constructs a new NavigationLinkCollection instance.
     *
     * @param NavigationLinkInterface[] $links An array of navigation links.
     * @throws InvalidArgumentException if the links array contains invalid items.
     */
    public function __construct(array $links)
    {
        $this->validateLinkArray($links);
        $this->links = array_values($links); // Ensure keys are re-indexed
    }

    /**
     * Returns the entire collection of links as an array.
     *
     * @return NavigationLinkInterface[]
     */
    public function all(): array
    {
        return $this->links;
    }

    /**
     * Returns the number of links in the collection.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->links);
    }

    /**
     * Allows the collection to be iterated over using foreach.
     *
     * @return ArrayIterator<int, NavigationLinkInterface>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->links);
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
