<?php

declare(strict_types=1);

namespace App\Domains\Item\Application\UseCases;

/**
 * Class CreateUseCase
 *
 * Application-layer use case responsible for orchestrating
 * the creation of a new Item entity.
 *
 * Responsibilities:
 * - Validate input data (non-null, basic invariants);
 * - Instantiate and persist a new Item using the repository;
 * - Return the created Item with its assigned identifier.
 */
final class CreateUseCase
{
    private ItemRepositoryInterface $itemRepository;

    /**
     * Constructs the use case with the required repository.
     *
     * @param ItemRepositoryInterface $itemRepository Contract for item persistence.
     */
    public function __construct(ItemRepositoryInterface $itemRepository)
    {
        $this->itemRepository = $itemRepository;
    }

    /**
     * Executes the item creation process.
     *
     * @param string $name Name of the item.
     * @param float $price Unit price of the item.
     * @param int $categoryId Associated category ID.
     * @param string $description Optional description.
     *
     * @return Item The created Item entity with defined ID.
     *
     * @throws InvalidArgumentException If input data is invalid.
     */
    public function execute(
        string $name,
        float $price,
        int $categoryId,
        string $description = ''
    ): Item {
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

        $item = new Item(
            name: $name,
            price: $price,
            categoryId: $categoryId,
            description: $description
        );

        $this->itemRepository->save($item);

        return $item;
    }
}
