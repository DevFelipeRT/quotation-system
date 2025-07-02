<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\Contract\Building\Partial;

use Rendering\Domain\Partial\ValueObject\PartialView;

/**
 * Defines a factory specialized in creating all VOs that implement
 * the PartialViewInterface.
 *
 * This centralizes the creation logic for a family of related objects,
 * decoupling the client from their concrete instantiation.
 */
interface PartialFactoryInterface
{
    /**
     * Creates a generic PartialView object for any reusable template.
     */
    public function createPartialView(string $templateFile, array $data = [], array $partials = []): PartialView;
}