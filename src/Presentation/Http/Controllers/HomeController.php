<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

/**
 * HomeController
 *
 * Handles the request for the application's root (dashboard) page.
 * Constructs and renders a view with application context and user data.
 */
final class HomeController extends AbstractController
{
    /**
     * Executes the logic for the home route.
     *
     * @param RouteRequestInterface $request
     * @return string
     */
    protected function execute(RouteRequestInterface $request): string
    {
        $view = $this->buildDashboardView();
        return $this->render($view);
    }

    /**
     * Constructs the dashboard view with context data.
     *
     * @return HtmlView
     */
    private function buildDashboardView(): HtmlView
    {
        $appConfig = $this->getConfig()->getAppConfig();
        $baseUrl = $this->urlResolver->baseUrl();
        $user = $this->getUserData();

        return new HtmlView('dashboard.php', [
            'appName'     => $appConfig->getName(),
            'headerTitle' => 'Dashboard',
            'baseUrl'     => $baseUrl,
            'fileName'    => 'dashboard.php',
            'usuario'     => $user,
        ]);
    }


    /**
     * Returns mock user data for view context.
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
