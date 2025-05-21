<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Application\Messaging\Application\Types\LoggableMessage;
use App\Infrastructure\Rendering\Application\HtmlView;
use App\Infrastructure\Rendering\Domain\Contracts\ViewInterface;
use App\Infrastructure\Rendering\Infrastructure\ViewRendererInterface;
use App\Infrastructure\Routing\Presentation\Http\Contracts\RouteRequestInterface;
use App\Infrastructure\Session\Domain\Contracts\SessionHandlerInterface;
use App\Shared\UrlResolver\UrlResolverInterface;
use Throwable;

/**
 * AbstractController
 *
 * Defines the foundational behavior for all HTTP controllers in the application.
 * It encapsulates shared concerns such as configuration access, session handling,
 * view rendering, error reporting, and JSON/redirect output management.
 *
 * Responsibilities:
 * - Define a unified entry point (`handle`) for controller execution
 * - Delegate request-specific logic to concrete implementations via `execute`
 * - Provide helper methods for rendering views, returning JSON, and issuing redirects
 * - Log unexpected exceptions using the application's structured logging strategy
 *
 * All dependencies are injected through the constructor to promote immutability,
 * testability, and adherence to clean architectural boundaries.
 */
abstract class AbstractController implements ControllerInterface
{
    private SessionHandlerInterface $session;
    private ViewRendererInterface $viewRenderer;
    protected UrlResolverInterface $urlResolver;

    public function __construct(
        SessionHandlerInterface $session,
        ViewRendererInterface $viewRenderer,
        UrlResolverInterface $urlResolver,
    ) {
        $this->session = $session;
        $this->viewRenderer = $viewRenderer;
        $this->urlResolver = $urlResolver;
    }

    /**
     * Entry point for handling incoming HTTP requests.
     * Wraps execution with structured error logging and fallback rendering.
     */
    final public function handle(RouteRequestInterface $request): string
    {
        try {
            return $this->execute($request);
        } catch (Throwable $exception) {
            $this->logException($exception, $request);
            return $this->renderErrorView();
        }
    }

    /**
     * Concrete controllers must implement this to handle the request.
     */
    abstract protected function execute(RouteRequestInterface $request): string;

    /**
     * Logs unexpected exceptions with contextual request data.
     */
    private function logException(Throwable $exception, RouteRequestInterface $request): void
    {
        $message = LoggableMessage::error(
            'Unhandled exception in HTTP controller.',
            [
                'exception' => $exception->getMessage(),
                'trace'     => $exception->getTraceAsString(),
                'request'   => [
                    'method' => $request->method()->value(),
                    'path'   => $request->path()->value(),
                    'host'   => $request->host(),
                    'scheme' => $request->scheme(),
                ],
            ]
        )->withChannel('controller');

        // $entry = $this->logAssembler->assembleFromMessage($message);
        // $this->logger->log($entry);
    }

    /**
     * Renders a generic error page when an unhandled exception occurs.
     */
    protected function renderErrorView(): string
    {
        return $this->viewRenderer->render(
            new HtmlView('error.php', [
                'message' => 'Ocorreu um erro inesperado. Tente novamente mais tarde.',
            ])
        );
    }

    /**
     * Returns a rendered view.
     */
    protected function render(ViewInterface $view): string
    {
        return $this->viewRenderer->render($view);
    }

    /**
     * Returns a JSON response and halts further execution.
     */
    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
        exit();
    }

    /**
     * Performs an HTTP redirect and halts execution.
     */
    protected function redirect(string $url): void
    {
        header('Location: ' . $url, true, 302);
        exit();
    }

    /**
     * Returns the current session handler.
     */
    protected function getSession(): SessionHandlerInterface
    {
        return $this->session;
    }
}
