<?php

declare(strict_types=1);

namespace App\Domain\Item\Application\UseCases;

use App\Domain\Item\Domain\Entities\Item;
use App\Domain\Item\Infrastructure\Persistence\ItemRepositoryInterface;
use InvalidArgumentException;

/**
 * Class UpdateUseCase
 *
 * Application-layer use case responsible for updating an existing item.
 *
 * Responsibilities:
 * - Validate the input data;
 * - Instantiate the updated Item entity;
 * - Delegate persistence to the item repository.
 */
final class UpdateUseCase
{
    private ItemRepositoryInterface $itemRepository;

    /**
     * UpdateUseCase constructor.
     *
     * @param ItemRepositoryInterface $itemRepository Repository responsible for item persistence.
     */
    public function __construct(ItemRepositoryInterface $itemRepository)
    {
        $this->itemRepository = $itemRepository;
    }

    /**
     * Executes the update process for a specific item.
     *
     * @param int $id ID of the item to update.
     * @param string $name Updated name of the item.
     * @param float $price Updated unit price.
     * @param int $categoryId Updated category ID.
     * @param string $description Updated optional description.
     *
     * @return void
     *
     * @throws InvalidArgumentException If validation fails.
     */
    public function execute(
        int $id,
        string $name,
        float $price,
        int $categoryId,
        string $description = ''
    ): void {
        if ($id <= 0) {
            throw new InvalidArgumentException('ID inválido para atualização.');
        }

        $name = trim($name);
        $description = trim($description);

        if ($name === '') {
            throw new InvalidArgumentException('O nome do item é obrigatório.');
        }

        if ($price < 0) {
            throw new InvalidArgumentException('O preço do item deve ser positivo.');
        }

        if ($categoryId <= 0) {
            throw new InvalidArgumentException('Categoria inválida.');
        }

        $item = new Item($name, $price, $categoryId, $description);
        $item->defineId($id);

        $this->itemRepository->update($item);
    }
}
