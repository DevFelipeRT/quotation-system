<?php

declare(strict_types=1);

namespace ClassDiscovery\Domain;

use ClassDiscovery\Domain\ValueObjects\FullyQualifiedClassName;

final class FqcnCollection
{
    /** @var FullyQualifiedClassName[] */
    private array $items;

    /**
     * @param FullyQualifiedClassName[] $items
     * @throws \InvalidArgumentException If any item is not a FullyQualifiedClassName.
     */
    public function __construct(array $items = [])
    {
        $this->items = $this->validateItems($items);
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
     * @return FullyQualifiedClassName[]
     */
    public function toArray(): array
    {
        return $this->items;
    }

    private function validateItems(array $items): array
    {
        foreach ($items as $item) {
            if (!$item instanceof FullyQualifiedClassName) {
                throw new \InvalidArgumentException('All items must be instances of FullyQualifiedClassName.');
            }
        }
        return array_values($items);
    }
}
