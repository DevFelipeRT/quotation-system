<?php

declare(strict_types=1);

namespace App\Domain\Item\Presentation\Http\Controllers;

use App\Domain\Item\Application\UseCases\CreateUseCase;
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
    public function __construct(CreateUseCase $useCase)
    {
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
            $this->useCase->execute(
                $_POST['name'] ?? '',
                isset($_POST['price']) ? (float) $_POST['price'] : 0.0,
                isset($_POST['category_id']) ? (int) $_POST['category_id'] : 0,
                $_POST['description'] ?? ''
            );
            $this->redirect('/itemsManager');
        } catch (Exception $e) {
            throw new Exception("Error creating item: " . $e->getMessage());
        }
    }
}
