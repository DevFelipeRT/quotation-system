<?php

declare(strict_types=1);

namespace Rendering\Domain\Contract;

use Rendering\Domain\ValueObject\Shared\PartialsCollection;

/**
 * Contract for objects capable of providing partial renderable components.
 *
 * This interface establishes a contract for implementing a compositional rendering
 * architecture where entities can expose collections of partial templates or views.
 * It enables both primary pages and individual partial views to serve as contexts
 * for rendering their nested sub-components, promoting modularity and reusability
 * in template composition.
 *
 * Implementing classes should provide access to their associated partial components
 * through a structured collection, facilitating hierarchical rendering patterns
 * and supporting complex UI compositions.
 */
interface PartialProviderInterface
{
    /**
     * Retrieves the collection of partial components associated with this provider.
     *
     * Returns a collection containing all partial templates or views that should
     * be rendered as part of this provider's context. The collection may be null
     * if no partials are associated with the current provider instance.
     *
     * @return PartialsCollection|null Collection of partial components, or null if none exist
     */
    public function partials(): ?PartialsCollection;
}
