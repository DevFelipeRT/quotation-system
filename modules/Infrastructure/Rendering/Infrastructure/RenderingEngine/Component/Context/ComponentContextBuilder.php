<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\RenderingEngine\Component\Context;

use Rendering\Domain\Contract\RenderableInterface;
use Rendering\Infrastructure\Contract\RenderingEngine\Component\Context\ContextBuilderInterface;
use Rendering\Infrastructure\Contract\RenderingEngine\Component\Context\RenderContextInterface;
use RuntimeException;

/**
 * An orchestrator that builds a rendering context by dispatching the task
 * to a specialized builder.
 *
 * This class implements the ContextBuilderInterface, allowing it to be used
 * polymorphically alongside specialist builders. It uses a dispatch map for O(1)
 * complexity, providing high performance by avoiding iteration.
 */
final class ComponentContextBuilder implements ContextBuilderInterface
{
    /**
     * @param array<class-string<RenderableInterface>, ContextBuilderInterface> $builders
     */
    public function __construct(private readonly array $builders)
    {
    }

    /**
     * {@inheritdoc}
     *
     * This implementation finds the correct specialist builder from its dispatch map
     * and delegates the build task to it.
     */
    public function build(RenderableInterface $renderable): RenderContextInterface
    {
        $builder = $this->findBuilderFor($renderable);
        return $builder->build($renderable);
    }

    /**
     * Finds the appropriate context builder for the given renderable object from the dispatch map.
     *
     * It first attempts a direct match on the object's concrete class, then checks
     * for implemented interfaces for broader matching.
     */
    private function findBuilderFor(RenderableInterface $renderable): ContextBuilderInterface
    {
        $renderableClass = get_class($renderable);

        // Attempt a direct match with the concrete class name first for performance.
        if (isset($this->builders[$renderableClass])) {
            return $this->builders[$renderableClass];
        }

        // If no direct match, check against registered interfaces.
        foreach (class_implements($renderableClass) as $interface) {
            if (isset($this->builders[$interface])) {
                return $this->builders[$interface];
            }
        }

        throw new RuntimeException(
            sprintf('No context builder registered for renderable class or interface "%s".', $renderableClass)
        );
    }
}
