<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Item;

use App\Application\UseCases\Item\DeleteUseCase;
use App\Logging\Domain\LogEntry;
use App\Logging\Domain\LogLevelEnum;
use App\Logging\LoggerInterface;
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
    private LoggerInterface $logger;

    /**
     * DeleteController constructor.
     *
     * @param DeleteUseCase $useCase Application-layer use case for item deletion.
     * @param LoggerInterface $logger Structured logger for operational audit.
     */
    public function __construct(
        DeleteUseCase $useCase,
        LoggerInterface $logger
    ) {
        $this->useCase = $useCase;
        $this->logger = $logger;
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

            $this->logger->log(new LogEntry(
                level: LogLevelEnum::INFO,
                message: 'Item excluído com sucesso.',
                context: ['id' => $id],
                channel: 'presentation.item'
            ));

            header('Location: /itemsManager');
            exit;

        } catch (Exception $e) {
            $this->logger->log(new LogEntry(
                level: LogLevelEnum::ERROR,
                message: 'Erro ao excluir item.',
                context: ['erro' => $e->getMessage()],
                channel: 'presentation.item'
            ));

            http_response_code(400);
            echo 'Erro ao excluir o item.';
        }
    }
}
