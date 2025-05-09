<?php

declare(strict_types=1);

namespace App\Domains\Item\Presentation\Http\Controllers;

use App\Domains\Item\Application\UseCases\CreateUseCase;
use App\Infrastructure\Logging\LoggerInterface;
use Exception;

/**
 * Class CreateController
 *
 * Handles the creation of a new item via HTTP POST.
 * Delegates to the application-layer use case and utilizes inherited utilities
 * for structured logging and response handling.
 */
final class CreateController extends Controller
{
    private CreateUseCase $useCase;

    /**
     * CreateController constructor.
     *
     * @param CreateUseCase $useCase Application-layer use case for item creation.
     */
    public function __construct(CreateUseCase $useCase, LoggerInterface $logger)
    {
        parent::__construct($logger);
        $this->useCase = $useCase;
    }

    /**
     * Handles the item creation process via POST.
     *
     * @return void
     */
    public function __invoke(): void
    {
        try {
            $item = $this->useCase->execute(
                $_POST['name'] ?? '',
                isset($_POST['price']) ? (float) $_POST['price'] : 0.0,
                isset($_POST['category_id']) ? (int) $_POST['category_id'] : 0,
                $_POST['description'] ?? ''
            );

            $this->logInfo(
                message: 'Item criado com sucesso.',
                context: ['id' => $item->getId(), 'nome' => $item->getName()],
                channel: 'presentation.item'
            );

            $this->redirect('/itemsManager');
        } catch (Exception $e) {
            $this->logError(
                message: 'Erro ao criar item.',
                context: ['erro' => $e->getMessage()],
                channel: 'presentation.item'
            );

            $this->respondWithError('Erro ao processar os dados do item.', 400);
        }
    }
}
