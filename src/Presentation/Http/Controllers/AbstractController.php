<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Messaging\LogMessage;
use App\Infrastructure\Logging\LogAssembler;
use App\Interfaces\Infrastructure\SessionInterface;
use App\Interfaces\Infrastructure\LoggerInterface;
use App\Interfaces\Infrastructure\UrlResolverInterface;
use App\Interfaces\Presentation\ControllerInterface;
use App\Interfaces\Presentation\ViewInterface;
use App\Interfaces\Presentation\ViewRendererInterface;
use App\Interfaces\Presentation\Routing\RouteRequestInterface;
use Config\Container\ConfigContainer;
use Throwable;

/**
 * AbstractController
 *
 * Base class for all HTTP controllers. Provides access to view rendering,
 * session, logging, and configuration. Also handles global error fallback
 * and output standardization.
 */
abstract class AbstractController implements ControllerInterface
{
    private ConfigContainer $config;
    private SessionInterface $session;
    private ViewRendererInterface $viewRenderer;
    protected UrlResolverInterface $urlResolver;
    private LoggerInterface $logger;
    private LogAssembler $logAssembler;

    /**
     * Base controller dependencies are injected via constructor.
     *
     * @param ConfigContainer         $config       Global configuration container.
     * @param SessionInterface        $session      Session handler interface.
     * @param ViewRendererInterface   $viewRenderer View renderer service.
     * @param LoggerInterface         $logger       Application logger interface.
     * @param LogAssembler            $logAssembler Assembler for log entries.
     */
    public function __construct(
        ConfigContainer $config,
        SessionInterface $session,
        ViewRendererInterface $viewRenderer,
        UrlResolverInterface $urlResolver,
        LoggerInterface $logger,
        LogAssembler $logAssembler
    ) {
        $this->config       = $config;
        $this->session      = $session;
        $this->viewRenderer = $viewRenderer;
        $this->urlResolver = $urlResolver;
        $this->logger       = $logger;
        $this->logAssembler = $logAssembler;
    }

    /**
     * Entry point for controller execution.
     * Wraps the user-defined logic and fallback error handling.
     *
     * @param RouteRequestInterface $request
     * @return string
     */
    final public function handle(RouteRequestInterface $request): string
    {
        try {
            return $this->execute($request);
        } catch (Throwable $exception) {
            $this->reportError($exception, [
                'method' => $request->method()->value(),
                'path'   => $request->path()->value(),
                'host'   => $request->host(),
                'scheme' => $request->scheme(),
            ]);

            return $this->renderGenericErrorPage();
        }
    }

    /**
     * Subclasses must implement this to handle request-specific logic.
     */
    abstract protected function execute(RouteRequestInterface $request): string;

    /**
     * Provides access to the full application configuration.
     */
    protected function config(): ConfigContainer
    {
        return $this->config;
    }

    /**
     * Provides access to the session abstraction.
     */
    protected function getSession(): SessionInterface
    {
        return $this->session;
    }

    /**
     * Renders the specified view using the injected renderer.
     */
    protected function render(ViewInterface $view): string
    {
        return $this->viewRenderer->render($view);
    }

    /**
     * Issues a redirect and halts execution.
     */
    protected function redirect(string $url): void
    {
        header('Location: ' . $url, true, 302);
        exit();
    }

    /**
     * Outputs JSON and terminates execution.
     */
    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit();
    }

    /**
     * Reports a structured exception log to the logger interface.
     */
    private function reportError(Throwable $exception, array $request): void
    {
        $message = LogMessage::error(
            'Erro inesperado ao processar requisição.',
            [
                'exception' => $exception->getMessage(),
                'trace'     => $exception->getTraceAsString(),
                'request'   => $request,
            ]
        );

        $entry = $this->logAssembler->fromLogMessage($message);
        $this->logger->log($entry);
    }

    /**
     * Renders a fallback error view.
     */
    protected function renderGenericErrorPage(): string
    {
        return $this->render(
            new \App\Presentation\Http\Views\HtmlView('error.php', [
                'message' => 'Ocorreu um erro inesperado. Tente novamente mais tarde.',
            ])
        );
    }
}
