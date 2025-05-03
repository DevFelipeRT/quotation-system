<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Entities\Item;

/**
 * Interface ItemRepositoryInterface
 *
 * Represents the contract for a repository responsible for data access
 * operations related to Item entities.
 *
 * This abstraction isolates the domain from infrastructure concerns
 * and allows flexibility for different storage implementations.
 */
interface ItemRepositoryInterface
{
    /**
     * Fetches all items from the data source.
     *
     * @return Item[] List of fully instantiated Item objects.
     */
    public function findAll(): array;

    /**
     * Persists a new item in the data source.
     *
     * @param Item $item The item to be stored.
     */
    public function save(Item $item): void;

    /**
     * Updates an existing item based on its identifier.
     *
     * @param Item $item The item with updated values.
     */
    public function update(Item $item): void;

    /**
     * Deletes an item based on its identifier.
     *
     * @param int $id The unique identifier of the item to delete.
     */
    public function delete(int $id): void;

    /**
     * Finds a single item by its unique identifier.
     *
     * @param int $id
     * @return Item|null Returns the item if found, or null otherwise.
     */
    public function findById(int $id): ?Item;
}
