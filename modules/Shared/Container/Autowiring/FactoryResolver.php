<?php

declare(strict_types=1);

namespace Container\Autowiring;

use Container\Contracts\ResolverInterface;
use PublicContracts\Container\ContainerInterface;

/**
 * Class FactoryResolver
 *
 * Resolves a service instance using a provided factory callable.
 * Designed to be used internally by the container.
 */
class FactoryResolver implements ResolverInterface
{
    /**
     * Resolves an instance using the provided factory and the container.
     *
     * @param callable $factory
     * @param ContainerInterface $container
     * @return mixed
     */
    public function resolveFactory(callable $factory, ContainerInterface $container): mixed
    {
        return $factory($container);
    }

    /**
     * Not intended for direct id-based resolution; included to fulfill interface contract.
     * If invoked, throws a LogicException.
     *
     * @param string $id
     * @param ContainerInterface $container
     * @param array $resolutionStack
     * @return mixed
     */
    public function resolve(string $id, ContainerInterface $container, array $resolutionStack = []): mixed
    {
        throw new \LogicException('Use resolveFactory() with an explicit factory.');
    }
}
