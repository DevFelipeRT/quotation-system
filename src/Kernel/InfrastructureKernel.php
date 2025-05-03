<?php

namespace App\Kernel;

use App\Infrastructure\Http\AppUrlResolver;
use App\Infrastructure\Logging\FileLogger;
use App\Infrastructure\Logging\LogAssembler;
use App\Infrastructure\Session\SessionHandler;
use App\Presentation\Http\Renderers\HtmlViewRenderer;
use Config\Container\ConfigContainer;

/**
 * InfrastructureKernel
 *
 * Centralized bootstrap for low-level services that do not contain domain logic,
 * including session handling, logging, URL resolution, and view rendering.
 * This kernel exists purely to isolate infrastructure setup from business logic.
 */
final class InfrastructureKernel
{
    private readonly SessionHandler $sessionHandler;
    private readonly FileLogger $logger;
    private readonly LogAssembler $logAssembler;
    private readonly AppUrlResolver $urlResolver;
    private readonly HtmlViewRenderer $viewRenderer;

    /**
     * Initializes infrastructure services from configuration context.
     *
     * @param ConfigContainer $config Application configuration container
     */
    public function __construct(ConfigContainer $config)
    {
        $this->sessionHandler = new SessionHandler();
        $this->sessionHandler->start();

        $this->logger       = new FileLogger($config->paths()->logsDir());
        $this->logAssembler = new LogAssembler();
        $this->urlResolver  = new AppUrlResolver($config->paths()->appDirectory());
        $this->viewRenderer = new HtmlViewRenderer(
            $config->paths()->viewsDir(),
            $this->urlResolver
        );
    }

    /**
     * Returns the logger instance for structured application logs.
     */
    public function logger(): FileLogger
    {
        return $this->logger;
    }

    /**
     * Returns the session handler responsible for managing PHP sessions.
     */
    public function session(): SessionHandler
    {
        return $this->sessionHandler;
    }

    /**
     * Returns the service that assembles structured logs from domain events.
     */
    public function logAssembler(): LogAssembler
    {
        return $this->logAssembler;
    }

    /**
     * Returns the service responsible for generating application-relative URLs.
     */
    public function urlResolver(): AppUrlResolver
    {
        return $this->urlResolver;
    }

    /**
     * Returns the view renderer for rendering HTML-based interfaces.
     */
    public function viewRenderer(): HtmlViewRenderer
    {
        return $this->viewRenderer;
    }
}
