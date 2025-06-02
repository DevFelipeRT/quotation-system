<?php

declare(strict_types=1);

namespace App\Shared\Container\Infrastructure\Scope;

use App\Shared\Container\Domain\Contracts\ContainerScopeInterface;

/**
 * Class TransientScope
 *
 * Implements a transient scope: returns a new instance for every resolution.
 */
class TransientScope implements ContainerScopeInterface
{
    /**
     * {@inheritdoc}
     */
    public function resolve(string $id, callable $factory): mixed
    {
        return $factory();
    }

    /**
     * {@inheritdoc}
     */
    public function clear(string $id): void
    {
        // Nothing to clear in transient scope
    }

    /**
     * {@inheritdoc}
     */
    public function clearAll(): void
    {
        // Nothing to clear for transient scope
    }

}
