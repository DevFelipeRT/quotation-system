<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\Contract\Building\Partial;

use Rendering\Domain\Contract\PartialViewInterface;
use Rendering\Domain\Partial\ValueObject\Footer;

/**
 * Defines the contract for a Footer component builder.
 *
 * This interface provides a fluent API to encapsulate the construction
 * logic of a Footer object, including its copyright notice, JavaScript assets,
 * and nested partial sub-components.
 */
interface FooterBuilderInterface
{
    /**
     * Sets the copyright notice for the footer.
     *
     * @param string $owner The name of the copyright holder.
     * @param string $message The message to append after the owner.
     * @return $this
     */
    public function setCopyright(string $owner, string $message = 'All rights reserved.'): self;

    /**
     * Adds a JavaScript file path to the footer's asset collection.
     *
     * @param string $path
     * @return $this
     */
    public function addJs(string $path): self;

    /**
     * Adds a named partial sub-component to the footer.
     *
     * @param string $key The identifier for the partial (used with @partial).
     * @param PartialViewInterface $partial The partial view object to add.
     * @return $this
     */
    public function addPartial(string $key, PartialViewInterface $partial): self;

    /**
     * Assembles and returns the final, immutable Footer object.
     *
     * @return Footer
     */
    public function build(): Footer;
}
