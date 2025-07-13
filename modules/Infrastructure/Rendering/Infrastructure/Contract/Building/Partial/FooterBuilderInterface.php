<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\Contract\Building\Partial;

/**
 * Defines the contract for a Footer component builder.
 *
 * This interface extends the PartialBuilderInterface to provide specific
 * methods for building a Footer object, which is a type of Partial.
 */
interface FooterBuilderInterface extends PartialBuilderInterface
{
    /**
     * Sets the copyright notice for the footer.
     *
     * @param string $owner The name of the copyright holder.
     * @param string $message The message to append after the owner.
     * @return $this
     */
    public function setCopyright(string $owner, string $message = 'All rights reserved.'): self;
}
