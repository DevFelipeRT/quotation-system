<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\Building;

use Rendering\Domain\Contract\Service\BuildingServiceInterface;
use Rendering\Domain\ValueObject\Shared\Directory;
use Rendering\Infrastructure\Building\Factory\AssetsFactory;
use Rendering\Infrastructure\Building\Factory\BuilderFactory;
use Rendering\Infrastructure\Building\Factory\NavigationLinkFactory;
use Rendering\Infrastructure\Building\Factory\PartialFactory;
use Rendering\Infrastructure\Building\Factory\RenderableDataFactory;

/**
 * Factory responsible for creating and wiring the complete building service
 * with all its dependencies.
 * 
 * This factory encapsulates the instantiation logic for the entire page building
 * subsystem, reducing complexity in the main kernel.
 */
final class BuildingServiceFactory
{
    /**
     * Creates a fully configured BuildingService with all its dependencies.
     *
     * @param Directory $assetsDirectory The validated directory containing static assets.
     * @param string $copyrightOwner The name of the copyright holder.
     * @param string $copyrightMessage The copyright message text.
     * @return BuildingServiceInterface The configured building service.
     */
    public static function create(
        Directory $assetsDirectory,
        string $copyrightOwner,
        string $copyrightMessage
    ): BuildingServiceInterface {
        // Create shared factories
        $renderableDataFactory = new RenderableDataFactory();
        $partialFactory = new PartialFactory($renderableDataFactory);
        $assetsFactory = new AssetsFactory();
        $linksFactory = new NavigationLinkFactory();

        // Create all specialized builders using the BuilderFactory
        $builders = BuilderFactory::createAll(
            $renderableDataFactory,
            $partialFactory,
            $assetsFactory,
            $linksFactory,
            $assetsDirectory,
            $copyrightOwner,
            $copyrightMessage
        );
        
        return new BuildingService(
            $builders['pageBuilder'],
            $builders['viewBuilder'],
            $builders['headerBuilder'],
            $builders['footerBuilder'],
            $builders['navigationBuilder'],
            $builders['partialBuilder'],
            $builders['assetPathResolver']
        );
    }
}
