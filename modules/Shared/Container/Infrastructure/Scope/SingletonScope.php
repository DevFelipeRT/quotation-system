<?php

declare(strict_types=1);

namespace Container\Infrastructure\Scope;

use Container\Domain\Contracts\ContainerScopeInterface;

/**
 * Class SingletonScope
 *
 * Implements a singleton scope: always returns the same instance for a given binding.
 */
class SingletonScope implements ContainerScopeInterface
{
    /**
     * @var array<string, mixed>
     */
    protected array $instances = [];

    /**
     * {@inheritdoc}
     */
    public function resolve(string $id, callable $factory): mixed
    {
        if (!array_key_exists($id, $this->instances)) {
            $this->instances[$id] = $factory();
        }
        return $this->instances[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function clear(string $id): void
    {
        unset($this->instances[$id]);
    }

    /**
     * Clears all stored singleton instances.
     */
    public function clearAll(): void
    {
        $this->instances = [];
    }
}
