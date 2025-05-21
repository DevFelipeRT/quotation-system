<?php

declare(strict_types=1);

namespace App\Kernel\Presentation;

use App\Domain\Item\Application\UseCases\CreateUseCase;
use App\Domain\Item\Application\UseCases\DeleteUseCase;
use App\Domain\Item\Application\UseCases\ListUseCase;
use App\Domain\Item\Application\UseCases\UpdateUseCase;
use App\Domain\Item\Presentation\Http\Controllers\Controller as ItemController;
use App\Domain\Item\Presentation\Http\Controllers\CreateController;
use App\Domain\Item\Presentation\Http\Controllers\DeleteController;
use App\Domain\Item\Presentation\Http\Controllers\UpdateController;
use App\Infrastructure\Http\UrlResolverInterface;
use App\Infrastructure\Logging\Application\LogEntryAssemblerInterface;
use App\Infrastructure\Logging\Infrastructure\Contracts\LoggerInterface;
use App\Infrastructure\Rendering\Infrastructure\ViewRendererInterface;
use App\Infrastructure\Session\Domain\Contracts\SessionHandlerInterface;
use App\Presentation\Http\Controllers\HomeController;
use Config\ConfigProvider;

/**
 * ControllerKernel
 *
 * Responsible for instantiating all HTTP controllers required by the application.
 * This kernel isolates controller creation from routing and configuration logic.
 */
final class ControllerKernel
{
    private ConfigProvider $config;
    private SessionHandlerInterface $session;
    private ViewRendererInterface $renderer;
    private UrlResolverInterface $urlResolver;
    private LoggerInterface $logger;
    private LogEntryAssemblerInterface $logEntryAssembler;
    private ListUseCase $listUseCase;
    private CreateUseCase $createUseCase;
    private UpdateUseCase $updateUseCase;
    private DeleteUseCase $deleteUseCase;

    /**
     * @param ConfigProvider $config
     * @param SessionHandlerInterface $session
     * @param ViewRendererInterface $renderer
     * @param UrlResolverInterface $urlResolver
     * @param LoggerInterface $logger
     * @param LogEntryAssemblerInterface $logEntryAssembler
     * @param ListUseCase $listUseCase
     * @param CreateUseCase $createUseCase
     * @param UpdateUseCase $updateUseCase
     * @param DeleteUseCase $deleteUseCase
     */
    public function __construct(
        ConfigProvider $config,
        SessionHandlerInterface $session,
        ViewRendererInterface $renderer,
        UrlResolverInterface $urlResolver,
        LoggerInterface $logger,
        LogEntryAssemblerInterface $logEntryAssembler,
        ListUseCase $listUseCase,
        CreateUseCase $createUseCase,
        UpdateUseCase $updateUseCase,
        DeleteUseCase $deleteUseCase
    ) {
        $this->config             = $config;
        $this->session            = $session;
        $this->renderer           = $renderer;
        $this->urlResolver        = $urlResolver;
        $this->logger             = $logger;
        $this->logEntryAssembler  = $logEntryAssembler;
        $this->listUseCase        = $listUseCase;
        $this->createUseCase      = $createUseCase;
        $this->updateUseCase      = $updateUseCase;
        $this->deleteUseCase      = $deleteUseCase;
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
