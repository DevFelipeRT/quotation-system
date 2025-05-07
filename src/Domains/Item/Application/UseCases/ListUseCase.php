<?php

declare(strict_types=1);

namespace App\Application\UseCases\Item;

use App\Domain\Entities\Item;
use App\Domain\Repositories\ItemRepositoryInterface;

/**
 * Class ListUseCase
 *
 * Use case responsible for retrieving a list of all items.
 * Encapsulates application-specific logic for item listing,
 * independent from presentation and infrastructure concerns.
 */
class ListUseCase
{
    private ItemRepositoryInterface $itemRepository;

    /**
     * Constructs the use case with the required item repository dependency.
     *
     * @param ItemRepositoryInterface $itemRepository
     */
    public function __construct(ItemRepositoryInterface $itemRepository)
    {
        $this->itemRepository = $itemRepository;
    }

    /**
     * Executes the use case to retrieve all items.
     *
     * @return Item[] Array of domain entities.
     */
    public function execute(): array
    {
        return $this->itemRepository->findAll();
    }
}
