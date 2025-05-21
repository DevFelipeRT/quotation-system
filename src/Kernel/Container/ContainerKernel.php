<?php

declare(strict_types=1);

namespace App\Kernel\Container;

use App\Shared\Container\AppContainer;
use App\Shared\Container\AppContainerInterface;
use App\Infrastructure\Logging\Infrastructure\Contracts\LoggerInterface;
use App\Infrastructure\Logging\Infrastructure\Contracts\PsrLoggerInterface;

/**
 * ContainerKernel
 *
 * Initializes the application container with required bindings.
 * Relies on external logging services to be passed in, ensuring separation of concerns.
 */
final class ContainerKernel
{
    public static function create(
        LoggerInterface $logger,
        PsrLoggerInterface $psrLogger
    ): AppContainerInterface {
        $container = new AppContainer();

        // Bootstrap essential bindings (including logging contracts)
        ServiceBindings::bootstrap($container, $logger, $psrLogger);

        return $container;
    }
}
