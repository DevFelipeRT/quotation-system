<?php

namespace App\Kernel;

use App\Infrastructure\Http\AppUrlResolver;
use App\Infrastructure\Http\UrlResolverInterface;
use App\Infrastructure\Session\SessionHandler;
use App\Infrastructure\Session\SessionHandlerInterface;
use App\Logging\Application\LogEntryAssembler;
use App\Logging\Application\LogEntryAssemblerInterface;
use App\Logging\Infrastructure\FileLogger;
use App\Logging\LoggerInterface;
use App\Messaging\Application\Types\LoggableMessage;
use App\Presentation\Http\Renderers\HtmlViewRenderer;
use App\Presentation\Http\Renderers\ViewRendererInterface;
use Config\Container\ConfigContainer;
use Throwable;

/**
 * InfrastructureKernel
 *
 * Core orchestrator of infrastructure-level services required during
 * the runtime of the application. This includes:
 *
 * - Structured logging and log entry assembly
 * - Session handling for stateful requests
 * - URL resolution for routing and asset access
 * - View rendering for HTML responses
 *
 * All services are configured via the ConfigContainer and exposed through
 * interface-based accessors to maximize testability and encapsulation.
 */
final class InfrastructureKernel
{
    private LoggerInterface $logger;
    private LogEntryAssemblerInterface $logEntryAssembler;
    private ?SessionHandlerInterface $sessionHandler = null;
    private UrlResolverInterface $urlResolver;
    private ViewRendererInterface $viewRenderer;

    /**
     * Constructs and wires all core infrastructure services.
     *
     * @param ConfigContainer $config Fully initialized configuration container.
     */
    public function __construct(ConfigContainer $config)
    {
        $this->logger = $this->createLogger($config);
        $this->logEntryAssembler = $this->createLogEntryAssembler();
        $this->urlResolver = $this->createUrlResolver($config);
        $this->viewRenderer = $this->createViewRenderer($config, $this->urlResolver);
        $this->sessionHandler = $this->createSessionHandler();
    }

    private function createLogger(ConfigContainer $config): LoggerInterface
    {
        return new FileLogger($config->getPathsConfig()->getLogsDirPath());
    }

    private function createLogEntryAssembler(): LogEntryAssemblerInterface
    {
        return new LogEntryAssembler();
    }

    private function createUrlResolver(ConfigContainer $config): UrlResolverInterface
    {
        return new AppUrlResolver($config->getPathsConfig()->getAppDirectory());
    }

    private function createViewRenderer(ConfigContainer $config, UrlResolverInterface $resolver): ViewRendererInterface
    {
        return new HtmlViewRenderer(
            $config->getPathsConfig()->getViewsDirPath(),
            $resolver
        );
    }

    private function createSessionHandler(): ?SessionHandlerInterface
    {
        $session = new SessionHandler();

        if (!$this->startSessionSafely($session)) {
            $this->logSessionStartFailure();
            return null;
        }

        return $session;
    }

    private function startSessionSafely(SessionHandler $session): bool
    {
        try {
            $session->start();
            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function logSessionStartFailure(): void
    {
        $message = LoggableMessage::error('Failed to start session')
            ->withChannel('kernel');

        $entry = $this->logEntryAssembler->assembleFromMessage($message);
        $this->logger->log($entry);
    }


    /**
     * Returns the logger service used for structured log persistence.
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Returns the session handler instance, or null if initialization failed.
     */
    public function getSessionHandler(): ?SessionHandlerInterface
    {
        return $this->sessionHandler;
    }

    /**
     * Provides access to the service that builds log entries from messages.
     */
    public function getLogEntryAssembler(): LogEntryAssemblerInterface
    {
        return $this->logEntryAssembler;
    }

    /**
     * Returns the application's URL resolver for routes and assets.
     */
    public function getUrlResolver(): UrlResolverInterface
    {
        return $this->urlResolver;
    }

    /**
     * Provides the HTML view renderer for server-side rendered responses.
     */
    public function getViewRenderer(): ViewRendererInterface
    {
        return $this->viewRenderer;
    }

    // /**
    //  * Provides runtime diagnostics for infrastructure services.
    //  *
    //  * @return array{session_active: bool, view_root: string|null, logger_class: class-string}
    //  */
    // public function diagnostics(): array
    // {
    //     return [
    //         'session_active' => $this->sessionHandler?->isActive() ?? false,
    //         'view_root'      => method_exists($this->viewRenderer, 'getViewRootPath')
    //             ? $this->viewRenderer->getViewRootPath()
    //             : null,
    //         'logger_class'   => get_class($this->logger),
    //     ];
    // }
}