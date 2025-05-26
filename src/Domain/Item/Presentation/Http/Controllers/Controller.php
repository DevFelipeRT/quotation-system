<?php

declare(strict_types=1);

namespace App\Domain\Item\Presentation\Http\Controllers;

use App\Domain\Item\Application\UseCases\ListUseCase;
use App\Infrastructure\Logging\LoggerInterface;
use App\Infrastructure\Rendering\Application\HtmlView;
use App\Infrastructure\Rendering\Infrastructure\HtmlViewRenderer;
use App\Infrastructure\Routing\Presentation\Http\Contracts\RouteRequestInterface;
use App\Presentation\Http\Controllers\AbstractController;
use Exception;
use Throwable;

/**
 * Class Controller
 *
 * Handles rendering of the item management interface.
 * Loads data via the application use case and renders the corresponding HTML view.
 */
class Controller extends AbstractController
{
    private ListUseCase $listItemsUseCase;

    /**
     * Controller constructor.
     *
     * @param ListUseCase $listItemsUseCase Application use case for fetching items.
     * @param HtmlViewRenderer $viewRenderer View renderer for HTML responses.
     * @param LoggerInterface $logger Structured logger for diagnostics.
     */
    public function __construct(
        ListUseCase $listItemsUseCase,
    ) {
        parent::__construct();
        $this->listItemsUseCase = $listItemsUseCase;
    }

    /**
     * Executes the logic for the route.
     *
     * @param RouteRequestInterface $request
     * @return string
     */
    protected function execute(RouteRequestInterface $request): string
    {
        try {
            $view = $this->buildView();
            return $this->render($view);
        } catch (Throwable $e) {
            throw new Exception("Error executing controller: " . $e->getMessage());
        }
    }
    
    /**
     * Builds the view for the item management interface.
     *
     * @return HtmlView
     */
    private function buildView(): HtmlView
    {
        try {
            $items = $this->getViewData();
    
            return new HtmlView(
                template: 'items_manager.php',
                data: [
                    'headerTitle' => 'Gerenciar Itens',
                    'fileName'    => 'items_manager',
                    'items'       => $items,
                ]
            );
        } catch (Throwable $e) {
            throw new Exception("Error building view: " . $e->getMessage());
        }
    }

    /**
     * Fetches the view data for rendering.
     *
     * @return void
     */
    private function getViewData(): array
    {
        try {
            $items = $this->listItemsUseCase->execute();
        } catch (Throwable $e) {
            throw new Exception("Error fetching items: " . $e->getMessage());
            $items = [];
        }
        return $items;
    }
}
