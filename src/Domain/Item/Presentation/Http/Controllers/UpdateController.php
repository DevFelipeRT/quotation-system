<?php

declare(strict_types=1);

namespace App\Domain\Item\Presentation\Http\Controllers;

use App\Domain\Item\Application\UseCases\UpdateUseCase;
use App\Infrastructure\Logging\LoggerInterface;
use Exception;
use InvalidArgumentException;

/**
 * Class UpdateController
 *
 * Handles HTTP POST requests to update an existing item.
 * Validates input data, delegates to the use case, and logs results.
 */
final class UpdateController extends Controller
{
    private UpdateUseCase $useCase;

    /**
     * UpdateController constructor.
     *
     * @param UpdateUseCase $useCase Use case for updating items.
     * @param LoggerInterface $logger Structured logger injected from infrastructure.
     */
    public function __construct(UpdateUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    /**
     * Handles the update process for a specific item.
     *
     * @return void
     */
    public function __invoke(): void
    {
        try {
            $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

            if ($id <= 0) {
                throw new InvalidArgumentException('ID inválido para atualização.');
            }

            $this->useCase->execute(
                id: $id,
                name: $_POST['name'] ?? '',
                price: isset($_POST['price']) ? (float) $_POST['price'] : 0.0,
                categoryId: isset($_POST['category_id']) ? (int) $_POST['category_id'] : 0,
                description: $_POST['description'] ?? ''
            );

            $this->redirect('/itemsManager');
        } catch (Exception $e) {
            throw new Exception("Erro ao atualizar o item: " . $e->getMessage());
        }
    }
}
