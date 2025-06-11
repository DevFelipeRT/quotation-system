<?php

declare(strict_types=1);

namespace Rendering\Domain\Contract;

/**
 * ViewInterface
 *
 * Contract for immutable view representations delivered to the rendering layer.
 * Designed for Value Objects that encapsulate all required data for rendering,
 * regardless of the presentation format (HTML, JSON, XML, etc).
 *
 * Implementations must guarantee immutability and value-based comparison.
 *
 * @author
 */
interface ViewInterface
{
    /**
     * Returns the template or resource file name for the view.
     *
     * @return string
     */
    public function fileName(): string;

    /**
     * Returns the encapsulated view data as an object.
     *
     * @return object
     */
    public function data(): object;

    /**
     * Exports the view as an array for serialization or debugging.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
