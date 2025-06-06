<?php

namespace App\Infrastructure\Rendering\Domain\Contracts;

/**
 * ViewInterface
 *
 * Represents a view model to be rendered by the presentation layer.
 * Implementations may include HTML views, JSON responses, or other formats.
 *
 * Each view exposes its associated data and may optionally specify a template identifier.
 */
interface ViewInterface
{
    /**
     * Returns the view's data context (variables available to the renderer).
     *
     * @return array<string, mixed>
     */
    public function data(): array;
}
