<?php

declare(strict_types=1);

namespace PublicContracts\Container;

/**
 * Interface ServiceProviderInterface
 *
 * Contract for service providers that register one or more bindings in the container.
 * Promotes modular and reusable container configuration.
 */
interface ServiceProviderInterface
{
    /**
     * Registers bindings and services within the provided container instance.
     *
     * @param ContainerInterface $container
     * @return void
     */
    public function register(ContainerInterface $container): void;
}
