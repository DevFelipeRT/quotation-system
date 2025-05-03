<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Item;

use App\Application\UseCases\Item\ListUseCase;
use App\Interfaces\Infrastructure\LoggerInterface;
use App\Presentation\Http\Controllers\AbstractHttpController;
use App\Presentation\Http\Renderers\HtmlViewRenderer;
use App\Presentation\Http\Views\HtmlView;
use Exception;

/**
 * Class Controller
 *
 * Handles rendering of the item management interface.
 * Loads data via the application use case and renders the corresponding HTML view.
 */
final class Controller extends AbstractHttpController
{
    private ListUseCase $listItemsUseCase;
    private HtmlViewRenderer $viewRenderer;

    /**
     * Controller constructor.
     *
     * @param ListUseCase $listItemsUseCase Application use case for fetching items.
     * @param HtmlViewRenderer $viewRenderer View renderer for HTML responses.
     * @param LoggerInterface $logger Structured logger for diagnostics.
     */
    public function __construct(
        ListUseCase $listItemsUseCase,
        HtmlViewRenderer $viewRenderer,
        LoggerInterface $logger
    ) {
        parent::__construct($logger);
        $this->listItemsUseCase = $listItemsUseCase;
        $this->viewRenderer = $viewRenderer;
    }

    /**
     * Renders the item management view.
     *
     * @return void
     */
    public function index(): void
    {
        try {
            $items = $this->listItemsUseCase->execute();

            $view = new HtmlView(
                template: 'items_manager.php',
                data: [
                    'headerTitle' => 'Gerenciar Itens',
                    'fileName'   => 'items_manager',
                    'items'       => $items,
                ]
            );

            echo $this->viewRenderer->render($view);

            $this->logInfo(
                message: 'Interface de gerenciamento de itens exibida com sucesso.',
                channel: 'presentation.item'
            );
        } catch (Exception $e) {
            $this->logError(
                message: 'Falha ao exibir a interface de gerenciamento de itens.',
                context: ['erro' => $e->getMessage()],
                channel: 'presentation.item'
            );

            $this->respondWithError('Erro interno ao carregar a interface de itens.', 500);
        }
    }
}
