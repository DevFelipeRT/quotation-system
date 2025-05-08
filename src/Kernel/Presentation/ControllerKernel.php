<?php

declare(strict_types=1);

namespace App\Kernel\Presentation;

/**
 * ControllerKernel
 *
 * Responsible for instantiating all HTTP controllers required by the application.
 * This kernel isolates controller creation from routing and configuration logic.
 */
final class ControllerKernel
{
    private ConfigContainer $config;
    private SessionHandler $session;
    private HtmlViewRenderer $renderer;
    private AppUrlResolver $urlResolver;
    private FileLogger $logger;
    private LogEntryAssembler $logEntryAssembler;
    private ListUseCase $listUseCase;
    private CreateUseCase $createUseCase;
    private UpdateUseCase $updateUseCase;
    private DeleteUseCase $deleteUseCase;

    /**
     * @param ConfigContainer $config
     * @param SessionHandler $session
     * @param HtmlViewRenderer $renderer
     * @param AppUrlResolver $urlResolver
     * @param FileLogger $logger
     * @param LogEntryAssembler $logEntryAssembler
     * @param ListUseCase $listUseCase
     * @param CreateUseCase $createUseCase
     * @param UpdateUseCase $updateUseCase
     * @param DeleteUseCase $deleteUseCase
     */
    public function __construct(
        ConfigContainer $config,
        SessionHandler $session,
        HtmlViewRenderer $renderer,
        AppUrlResolver $urlResolver,
        FileLogger $logger,
        LogEntryAssembler $logEntryAssembler,
        ListUseCase $listUseCase,
        CreateUseCase $createUseCase,
        UpdateUseCase $updateUseCase,
        DeleteUseCase $deleteUseCase
    ) {
        $this->config        = $config;
        $this->session       = $session;
        $this->renderer      = $renderer;
        $this->urlResolver   = $urlResolver;
        $this->logger        = $logger;
        $this->logEntryAssembler  = $logEntryAssembler;
        $this->listUseCase   = $listUseCase;
        $this->createUseCase = $createUseCase;
        $this->updateUseCase = $updateUseCase;
        $this->deleteUseCase = $deleteUseCase;
    }

    /**
     * Builds and returns a map of controller FQCNs to their instantiated objects.
     *
     * @return array<class-string, object>
     */
    public function map(): array
    {
        return [
            HomeController::class => new HomeController(
                $this->config,
                $this->session,
                $this->renderer,
                $this->urlResolver,
                $this->logger,
                $this->logEntryAssembler
            ),

            ItemController::class => new ItemController(
                $this->listUseCase,
                $this->renderer,
                $this->logger
            ),

            CreateController::class => new CreateController(
                $this->createUseCase,
                $this->logger
            ),

            UpdateController::class => new UpdateController(
                $this->updateUseCase,
                $this->logger
            ),

            DeleteController::class => new DeleteController(
                $this->deleteUseCase,
                $this->logger
            ),
        ];
    }
}
