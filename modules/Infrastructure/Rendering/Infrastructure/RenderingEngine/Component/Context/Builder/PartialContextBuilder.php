<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\RenderingEngine\Component\Context\Builder;

use Rendering\Infrastructure\Contract\RenderingEngine\Component\Context\ContextBuilderInterface;
use ReflectionClass;
use InvalidArgumentException;
use Rendering\Domain\Contract\RenderableInterface;
use Rendering\Domain\Contract\Partial\PartialViewInterface;
use Rendering\Domain\ValueObject\Partial\Navigation\Navigation;
use Rendering\Infrastructure\Contract\RenderingEngine\Component\Context\RenderContextInterface;
use Rendering\Infrastructure\RenderingEngine\Component\Context\RenderContext;

/**
 * A specialized context builder for objects that implement PartialViewInterface.
 */
final class PartialContextBuilder implements ContextBuilderInterface
{
    /**
     * {@inheritdoc}
     */
    public function build(RenderableInterface $renderable): RenderContextInterface
    {
        if (!$renderable instanceof PartialViewInterface) {
            throw new InvalidArgumentException('PartialContextBuilder only supports instances of PartialViewInterface.');
        }

        $data = $this->buildPartialData($renderable);

        // For a standalone Partial, the API context is the Partial itself.
        return new RenderContext($data, $renderable);
    }

    /**
     * Builds the data array for the partial, including special enrichments.
     */
    private function buildPartialData(PartialViewInterface $partial): array
    {
        $data = $partial->data()?->toArray() ?? [];
        $data['partial'] = $partial;

        $this->enrichDataWithPublicMethods($partial, $data);

        return $data;
    }

    /**
     * Enriches a partial's data by exposing public methods as template variables.
     */
    private function enrichDataWithPublicMethods(PartialViewInterface $partial, array &$data): void
    {
        if ($partial instanceof Navigation) {
            $data['links'] = $partial->links();
        }

        $this->exposePublicMethodsAsVariables($partial, $data);
    }

    /**
     * Uses reflection to expose a partial's public, parameter-less methods as variables.
     */
    private function exposePublicMethodsAsVariables(PartialViewInterface $partial, array &$data): void
    {
        $reflection = new ReflectionClass($partial);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        
        $methodsToSkip = [
            '__construct', '__destruct', '__clone', '__toString', 
            'fileName', 'data', 'partials', 'getData', 'getPartials'
        ];

        foreach ($methods as $method) {
            $isOwnMethod = $method->getDeclaringClass()->getName() === get_class($partial);
            $isNotSkipped = !in_array($method->getName(), $methodsToSkip, true);
            $hasNoRequiredParams = $method->getNumberOfRequiredParameters() === 0;

            if ($isOwnMethod && $isNotSkipped && $hasNoRequiredParams) {
                try {
                    $result = $method->invoke($partial);
                    if ($result !== null && !isset($data[$method->getName()])) {
                        $data[$method->getName()] = $result;
                    }
                } catch (\Throwable) {
                    // Ignore methods that might throw exceptions during invocation.
                }
            }
        }
    }
}
