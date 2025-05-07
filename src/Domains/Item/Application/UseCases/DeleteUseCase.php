<?php

declare(strict_types=1);

namespace App\Application\UseCases\Item;

use App\Domain\Repositories\ItemRepositoryInterface;
use InvalidArgumentException;

/**
 * Class DeleteUseCase
 *
 * Application-layer use case responsible for deleting an item by its ID.
 *
 * Responsibilities:
 * - Validate the identifier;
 * - Delegate deletion to the repository.
 */
final class DeleteUseCase
{
    private ItemRepositoryInterface $itemRepository;

    /**
     * DeleteUseCase constructor.
     *
     * @param ItemRepositoryInterface $itemRepository Contract for item persistence.
     */
    public function __construct(ItemRepositoryInterface $itemRepository)
    {
        $this->itemRepository = $itemRepository;
    }

    /**
     * Executes the deletion process for a specific item.
     *
     * @param int $id The identifier of the item to delete.
     *
     * @return void
     * @throws InvalidArgumentException If the ID is invalid.
     */
    public function execute(int $id): void
    {
        if ($id <= 0) {
            throw new InvalidArgumentException('ID inválido para exclusão.');
        }

        $this->itemRepository->delete($id);
    }
}
