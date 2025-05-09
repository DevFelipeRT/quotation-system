<?php

declare(strict_types=1);

namespace App\Domains\Item\Presentation\Http\Controllers;

use App\Domains\Item\Application\UseCases\ListUseCase;
use App\Infrastructure\Logging\Domain\LogEntry;
use App\Infrastructure\Logging\Domain\LogLevelEnum;
use App\Infrastructure\Logging\LoggerInterface;
use App\Infrastructure\Rendering\Application\HtmlView;
use App\Infrastructure\Rendering\Infrastructure\HtmlViewRenderer;
use App\Presentation\Http\Controllers\AbstractController;
use Exception;

/**
 * Class Controller
 *
 * Handles rendering of the item management interface.
 * Loads data via the application use case and renders the corresponding HTML view.
 */
final class Controller extends AbstractController
{
    private ListUseCase $listItemsUseCase;
    private HtmlViewRenderer $viewRenderer;
    private LoggerInterface $logger;

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
        $this->logger = $logger;
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
                    'fileName'    => 'items_manager',
                    'items'       => $items,
                ]
            );

            echo $this->viewRenderer->render($view);

            $this->logger->log(new LogEntry(
                level: LogLevelEnum::INFO,
                message: 'Interface de gerenciamento de itens exibida com sucesso.',
                context: [],
                channel: 'presentation.item'
            ));
        } catch (Exception $e) {
            $this->logger->log(new LogEntry(
                level: LogLevelEnum::ERROR,
                message: 'Falha ao exibir a interface de gerenciamento de itens.',
                context: ['erro' => $e->getMessage()],
                channel: 'presentation.item'
            ));

            $this->respondWithError('Erro interno ao carregar a interface de itens.', 500);
        }
    }
}
