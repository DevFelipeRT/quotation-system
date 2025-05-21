<?php

declare(strict_types=1);

namespace App\Domain\Item\Presentation\Http\Controllers;

use App\Domain\Item\Application\UseCases\DeleteUseCase;
use App\Infrastructure\Logging\Domain\LogEntry;
use App\Infrastructure\Logging\Domain\LogLevelEnum;
use App\Infrastructure\Logging\LoggerInterface;
use Exception;
use InvalidArgumentException;

/**
 * Class DeleteController
 *
 * Handles the deletion of an item via HTTP POST request.
 * Validates the input ID, delegates to the use case, and logs the result.
 */
final class DeleteController extends Controller
{
    private DeleteUseCase $useCase;

    /**
     * DeleteController constructor.
     *
     * @param DeleteUseCase $useCase Application-layer use case for item deletion.
     * @param LoggerInterface $logger Structured logger for operational audit.
     */
    public function __construct(
        DeleteUseCase $useCase,
    ) {
        $this->useCase = $useCase;
    }

    /**
     * Handles the item deletion process triggered by form submission.
     *
     * @return void
     */
    public function __invoke(): void
    {
        try {
            $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
            if ($id <= 0) {
                throw new InvalidArgumentException('ID inválido para exclusão.');
            }
            $this->useCase->execute($id);
            header('Location: /itemsManager');
            exit;

        } catch (Exception $e) {
            throw new Exception("Erro ao excluir item: " . $e->getMessage());
        }
    }
}
