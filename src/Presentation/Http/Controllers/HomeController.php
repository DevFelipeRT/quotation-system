<?php

namespace App\Presentation\Http\Controllers;

use App\Interfaces\Presentation\Routing\RouteRequestInterface;
use App\Presentation\Http\Views\HtmlView;

/**
 * HomeController
 *
 * Handles the HTTP request for the application's home page.
 * Responsible for rendering the dashboard view with context-specific variables.
 */
final class HomeController extends AbstractController
{
    /**
     * Executes the controller logic for the home route.
     * Prepares user context and view variables, then renders the dashboard.
     *
     * @param RouteRequestInterface $request Structured HTTP request.
     * @return string Rendered HTML response.
     */
    protected function execute(RouteRequestInterface $request): string
    {
        $view = new HtmlView('dashboard.php', [
            'appName'     => $this->config()->app()->name(),
            'headerTitle' => 'Dashboard',
            'baseUrl'     => $this->urlResolver->baseUrl(),
            'fileName'   => 'dashboard.php',
            'usuario'     => $this->getUserData(),
        ]);

        return $this->render($view);
    }

    /**
     * Retrieves simulated user data for the dashboard.
     *
     * @return array{name: string, email: string}
     */
    private function getUserData(): array
    {
        return [
            'name'  => 'João da Silva',
            'email' => 'joao@example.com',
        ];
    }
}
