<?php

declare(strict_types=1);

namespace Rendering\Infrastructure;

use PublicContracts\Rendering\RenderingConfigInterface;
use PublicContracts\Rendering\RenderingFacadeInterface;
use PublicContracts\Rendering\RenderingKernelInterface;
use Rendering\Application\Contract\PageBuildingServiceInterface;
use Rendering\Application\Contract\PageRenderingServiceInterface;
use Rendering\Application\RenderingFacade;
use Rendering\Domain\Contract\SecurityServiceInterface;
use Rendering\Domain\Shared\ValueObject\Directory;
use Rendering\Infrastructure\Building\Page\AssetPathResolver;
use Rendering\Infrastructure\Building\Page\PageBuilder;
use Rendering\Infrastructure\Building\Page\PageBuildingService;
use Rendering\Infrastructure\Building\Partials\FooterBuilder;
use Rendering\Infrastructure\Building\Partials\HeaderBuilder;
use Rendering\Infrastructure\Building\Partials\NavigationBuilder;
use Rendering\Infrastructure\Building\Partials\PartialFactory;
use Rendering\Infrastructure\Contract\TemplateProcessing\TemplateProcessingServiceInterface;
use Rendering\Infrastructure\RenderingEngine\PageRenderingService;
use Rendering\Infrastructure\RenderingEngine\TemplateRenderer;
use Rendering\Infrastructure\TemplateProcessing\TemplateCache;
use Rendering\Infrastructure\TemplateProcessing\TemplateCompiler;
use Rendering\Infrastructure\TemplateProcessing\TemplatePathResolver;
use Rendering\Infrastructure\TemplateProcessing\TemplateProcessingService;
use Rendering\Security\DirectorySecurityService;

/**
 * A self-contained bootstrap for the Rendering module.
 *
 * This class acts as the Composition Root for the entire rendering system.
 * It is responsible for instantiating and wiring all components (services,
 * renderers, factories, etc.) with their dependencies. It operates without an
 * external dependency injection container, making it a portable and explicit factory
 * for the module's services.
 */
final class RenderingKernel implements RenderingKernelInterface
{
    /**
     * The validated path to the directory containing view files.
     * @var Directory
     */
    private readonly Directory $viewsDirectory;
    
    /**
     * The validated path to the cache directory for compiled templates.
     * @var Directory
     */
    private readonly Directory $cacheDirectory;

    /**
     * The validated path to the directory containing static assets (CSS, JS, images).
     * @var Directory
     */
    private readonly Directory $assetsDirectory;

    /**
     * The service for validating directory paths.
     * @var SecurityServiceInterface
     */
    private readonly SecurityServiceInterface $securityService;

    /**
     * The high-level service for processing and compiling templates.
     * @var TemplateProcessingServiceInterface
     */
    private readonly TemplateProcessingServiceInterface $templateProcessingService;

    /**
     * The high-level service for building a complete Page object.
     * @var PageBuildingServiceInterface
     */
    private readonly PageBuildingServiceInterface $pageBuildingService;

    /**
     * The high-level service for rendering a complete Page object into HTML.
     * @var PageRenderingServiceInterface
     */
    private readonly PageRenderingServiceInterface $pageRenderingService;

    /**
     * The public-facing facade for the entire rendering module.
     * @var RenderingFacadeInterface
     */
    private RenderingFacadeInterface $renderer;

    /**
     * The name of the copyright holder.
     * @var string
     */
    private readonly string $copyrightOwner;

    /**
     * The copyright message text.
     * @var string
     */
    private readonly string $copyrightMessage;

    /**
     * Initializes the kernel and boots all subsystems in the correct order.
     *
     * @param RenderingConfigInterface $config A data object containing all necessary settings.
     */
    public function __construct(RenderingConfigInterface $config)
    {
        $this->bootSecurity();
        $this->initiateConfig($config);
        $this->bootComponents();
    }

    /**
     * Returns the ready-to-use rendering facade, the main public entry point for the module.
     *
     * @return RenderingFacadeInterface
     */
    public function renderer(): RenderingFacadeInterface
    {
        return $this->renderer;
    }
    
    /**
     * Instantiates the security service, a root dependency.
     */
    private function bootSecurity(): void
    {
        $this->securityService = new DirectorySecurityService();
    }

    /**
     * Loads configuration from the config object and instantiates core value objects.
     */
    private function initiateConfig(RenderingConfigInterface $config): void
    {
        $this->viewsDirectory = new Directory(
            $config->viewsDirectory(),
            $this->securityService
        );
        $this->cacheDirectory = new Directory(
            $config->cacheDirectory(),
            $this->securityService
        );
        $this->assetsDirectory = new Directory(
            $config->assetsDirectory(),
            $this->securityService
        );
        $this->copyrightOwner = $config->copyrightOwner();
        $this->copyrightMessage = $config->copyrightMessage();
    }

    /**
     * Acts as the main bootstrap coordinator for all service subsystems.
     */
    private function bootComponents(): void
    {
        $this->bootTemplateProcessing();
        $this->bootPageBuilding();
        $this->bootRenderingEngine();
        $this->createRenderingFacade();
    }

    /**
     * Instantiates and wires the entire template processing subsystem.
     */
    private function bootTemplateProcessing(): void
    {
        $pathResolver = new TemplatePathResolver($this->viewsDirectory);
        $templateCompiler = new TemplateCompiler($pathResolver);
        $templateCache = new TemplateCache($this->cacheDirectory); 
        
        $this->templateProcessingService = new TemplateProcessingService(
            $pathResolver,
            $templateCache,
            $templateCompiler
        );
    }

    /**
     * Instantiates and wires the entire page building subsystem.
     */
    private function bootPageBuilding(): void
    {
        $pageBuilder = new PageBuilder();
        $headerBuilder = new HeaderBuilder();
        $navigationBuilder = new NavigationBuilder();
        $footerBuilder = new FooterBuilder(
            $this->copyrightOwner,
            $this->copyrightMessage
        );
        $partialFactory = new PartialFactory();
        $assetPathResolver = new AssetPathResolver(
            $this->assetsDirectory
        );
        
        $this->pageBuildingService = new PageBuildingService(
            $pageBuilder,
            $headerBuilder,
            $footerBuilder,
            $navigationBuilder,
            $partialFactory,
            $assetPathResolver
        );
    }

    /**
     * Instantiates and wires the rendering engine subsystem.
     */
    private function bootRenderingEngine(): void
    {
        $templateRenderer = new TemplateRenderer(
            $this->templateProcessingService
        );
        
        $this->pageRenderingService = new PageRenderingService(
            $templateRenderer
        );
    }

    /**
     * Instantiates the final public-facing facade with its service dependencies.
     */
    private function createRenderingFacade(): void
    {
        $this->renderer = new RenderingFacade(
            $this->pageBuildingService,
            $this->pageRenderingService
        );
    }
}