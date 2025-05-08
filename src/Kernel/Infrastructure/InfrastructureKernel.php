<?php

declare(strict_types=1);

namespace App\Kernel\Infrastructure;

use Throwable;

/**
 * InfrastructureKernel
 *
 * Aggregates and exposes infrastructure-level services required at runtime.
 *
 * This kernel centralizes access to reusable components across the application,
 * including session handling, routing, view rendering, and structured logging.
 * Logging services are injected via LoggingKernel.
 *
 * Responsibilities:
 * - Start and expose user session handler
 * - Resolve application URLs and paths
 * - Render HTML views for HTTP responses
 * - Log infrastructure errors using structured entries
 */
final class InfrastructureKernel
{
    private readonly LoggerInterface $logger;
    private readonly LogEntryAssemblerInterface $logEntryAssembler;
    private readonly UrlResolverInterface $urlResolver;
    private readonly ViewRendererInterface $viewRenderer;
    private readonly ?SessionHandlerInterface $sessionHandler;

    /**
     * Initializes infrastructure services based on configuration and logging kernel.
     *
     * @param ConfigContainer $config   Configuration container with paths and environment.
     * @param LoggingKernel   $logging  Logging kernel (provides logger and assembler).
     */
    public function __construct(ConfigContainer $config, LoggingKernel $logging)
    {
        $this->logger = $logging->getLogger();
        $this->logEntryAssembler = $logging->getLogEntryAssembler();
        $this->urlResolver = $this->createUrlResolver($config);
        $this->viewRenderer = $this->createViewRenderer($config, $this->urlResolver);
        $this->sessionHandler = $this->createSessionHandler();
    }

    /**
     * Returns the active logger for structured persistence.
     *
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Returns the log entry assembler that converts messages to structured logs.
     *
     * @return LogEntryAssemblerInterface
     */
    public function getLogEntryAssembler(): LogEntryAssemblerInterface
    {
        return $this->logEntryAssembler;
    }

    /**
     * Provides a resolver for application routes and asset URLs.
     *
     * @return UrlResolverInterface
     */
    public function getUrlResolver(): UrlResolverInterface
    {
        return $this->urlResolver;
    }

    /**
     * Returns the view renderer for server-side HTML generation.
     *
     * @return ViewRendererInterface
     */
    public function getViewRenderer(): ViewRendererInterface
    {
        return $this->viewRenderer;
    }

    /**
     * Returns the active session handler or null if session failed to start.
     *
     * @return SessionHandlerInterface|null
     */
    public function getSessionHandler(): ?SessionHandlerInterface
    {
        return $this->sessionHandler;
    }

    /**
     * Instantiates the application's URL resolver.
     *
     * @param ConfigContainer $config
     * @return UrlResolverInterface
     */
    private function createUrlResolver(ConfigContainer $config): UrlResolverInterface
    {
        return new AppUrlResolver(
            $config->getPathsConfig()->getAppDirectory()
        );
    }

    /**
     * Instantiates the HTML view renderer.
     *
     * @param ConfigContainer $config
     * @param UrlResolverInterface $resolver
     * @return ViewRendererInterface
     */
    private function createViewRenderer(ConfigContainer $config, UrlResolverInterface $resolver): ViewRendererInterface
    {
        return new HtmlViewRenderer(
            $config->getPathsConfig()->getViewsDirPath(),
            $resolver
        );
    }

    /**
     * Attempts to start and return the session handler.
     *
     * @return SessionHandlerInterface|null
     */
    private function createSessionHandler(): ?SessionHandlerInterface
    {
        $session = new SessionHandler();

        if (!$this->startSessionSafely($session)) {
            $this->logSessionStartFailure();
            return null;
        }

        return $session;
    }

    /**
     * Attempts to start the session, safely handling failure.
     *
     * @param SessionHandler $session
     * @return bool
     */
    private function startSessionSafely(SessionHandler $session): bool
    {
        try {
            $session->start();
            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Logs a failure when session startup is unsuccessful.
     *
     * @return void
     */
    private function logSessionStartFailure(): void
    {
        $message = LoggableMessage::error('Failed to start session')
            ->withChannel('kernel');

        $entry = $this->logEntryAssembler->assembleFromMessage($message);
        $this->logger->log($entry);
    }
}
