<?php

declare(strict_types=1);

namespace Container\Infrastructure\Autowiring;

use Container\Domain\Contracts\ContainerInterface;

/**
 * Interface ResolverInterface
 *
 * Contract for all container resolution strategies (e.g., autowiring, factory-based, etc).
 * All resolvers must implement this interface to enable flexible dependency resolution.
 */
interface ResolverInterface
{
    /**
     * Attempts to resolve an instance for the given identifier.
     * Most commonly used for autowiring or reflection-based resolution.
     *
     * @param string $id
     * @param ContainerInterface $container
     * @param array $resolutionStack Used to detect circular dependencies
     * @return mixed
     */
    public function resolve(string $id, ContainerInterface $container, array $resolutionStack = []): mixed;
}
