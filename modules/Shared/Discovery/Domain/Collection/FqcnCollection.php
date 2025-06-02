<?php

declare(strict_types=1);

namespace App\Shared\Discovery\Domain\Collection;

use App\Shared\Discovery\Domain\ValueObjects\FullyQualifiedClassName;
use IteratorAggregate;
use ArrayIterator;
use Traversable;

final class FqcnCollection implements IteratorAggregate
{
    /** @var FullyQualifiedClassName[] */
    private array $items;

    /**
     * @param FullyQualifiedClassName[] $items
     * @throws \InvalidArgumentException If any item is not a FullyQualifiedClassName.
     */
    public function __construct(array $items = [])
    {
        foreach ($items as $item) {
            if (!$item instanceof FullyQualifiedClassName) {
                throw new \InvalidArgumentException('All items must be instances of FullyQualifiedClassName.');
            }
        }
        $this->items = array_values($items);
    }

    /**
     * @return Traversable|FullyQualifiedClassName[]
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    /**
     * @param FullyQualifiedClassName $fqcn
     * @return self
     */
    public function withAdded(FullyQualifiedClassName $fqcn): self
    {
        $newItems = $this->items;
        $newItems[] = $fqcn;
        return new self($newItems);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @return FullyQualifiedClassName[]
     */
    public function toArray(): array
    {
        return $this->items;
    }
}
