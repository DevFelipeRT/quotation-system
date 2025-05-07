<?php

declare(strict_types=1);

namespace App\Domain\Entities;

/**
 * Class Item
 *
 * Domain entity representing an item (e.g., a product or service).
 * Encapsulates all required attributes, while preserving immutability
 * principles and domain constraints.
 */
class Item
{
    private ?int $id = null;
    private string $name;
    private float $price;
    private int $categoryId;
    private string $description;

    /**
     * Item constructor.
     *
     * @param string $name
     * @param float $price
     * @param int $categoryId
     * @param string $description
     */
    public function __construct(
        string $name,
        float $price,
        int $categoryId,
        string $description
    ) {
        if ($price < 0) {
            throw new \InvalidArgumentException('Item price must be non-negative.');
        }

        $this->name = $name;
        $this->price = $price;
        $this->categoryId = $categoryId;
        $this->description = $description;
    }

    /**
     * Gets the item ID.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Assigns the item ID. This method should be called only once after persistence.
     *
     * @param int $id
     */
    public function defineId(int $id): void
    {
        if ($this->id !== null) {
            throw new \LogicException('Item ID is immutable once defined.');
        }

        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Renames the item (if required by a business rule).
     *
     * @param string $newName
     */
    public function rename(string $newName): void
    {
        $this->name = $newName;
    }

    /**
     * Reprices the item.
     *
     * @param float $newPrice
     */
    public function reprice(float $newPrice): void
    {
        if ($newPrice < 0) {
            throw new \InvalidArgumentException('Item price must be non-negative.');
        }

        $this->price = $newPrice;
    }
}
