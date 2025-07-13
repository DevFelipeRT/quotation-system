<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\Building\Factory;

use Rendering\Domain\ValueObject\Shared\Directory;
use Rendering\Infrastructure\Building\Page\AssetPathResolver;
use Rendering\Infrastructure\Building\Page\PageBuilder;
use Rendering\Infrastructure\Building\Partial\FooterBuilder;
use Rendering\Infrastructure\Building\Partial\HeaderBuilder;
use Rendering\Infrastructure\Building\Partial\NavigationBuilder;
use Rendering\Infrastructure\Building\Partial\PartialBuilder;
use Rendering\Infrastructure\Building\View\ViewBuilder;

/**
 * Factory responsible for creating all specialized builders with their dependencies.
 * 
 * This factory encapsulates the instantiation logic for all page component builders,
 * reducing complexity in the BuildingServiceFactory.
 */
final class BuilderFactory
{
    /**
     * Creates all specialized builders with their shared dependencies.
     *
     * @param RenderableDataFactory $renderableDataFactory The shared renderable data factory.
     * @param PartialFactory $partialFactory The shared partial factory.
     * @param AssetsFactory $assetsFactory The assets factory.
     * @param NavigationLinkFactory $linksFactory The navigation links factory.
     * @param Directory $assetsDirectory The validated directory containing static assets.
     * @param string $copyrightOwner The name of the copyright holder.
     * @param string $copyrightMessage The copyright message text.
     * @return array<string, object> Array with all specialized builders.
     */
    public static function createAll(
        RenderableDataFactory $renderableDataFactory,
        PartialFactory $partialFactory,
        AssetsFactory $assetsFactory,
        NavigationLinkFactory $linksFactory,
        Directory $assetsDirectory,
        string $copyrightOwner,
        string $copyrightMessage
    ): array {
        return [
            'pageBuilder' => new PageBuilder(
                $partialFactory, 
                $renderableDataFactory, 
                $assetsFactory
            ),
            'viewBuilder' => new ViewBuilder(
                $partialFactory, 
                $renderableDataFactory
            ),
            'headerBuilder' => new HeaderBuilder(
                $partialFactory, 
                $renderableDataFactory
            ),
            'navigationBuilder' => new NavigationBuilder(
                $partialFactory, 
                $renderableDataFactory,
                $linksFactory
            ),
            'footerBuilder' => new FooterBuilder(
                $partialFactory,
                $renderableDataFactory,
                $copyrightOwner,
                $copyrightMessage
            ),
            'partialBuilder' => new PartialBuilder(
                $partialFactory, 
                $renderableDataFactory
            ),
            'assetPathResolver' => new AssetPathResolver($assetsDirectory)
        ];
    }
}
