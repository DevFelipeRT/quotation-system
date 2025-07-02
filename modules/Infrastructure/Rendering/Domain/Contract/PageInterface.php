<?php

declare(strict_types=1);

namespace Rendering\Domain\Contract;

use Rendering\Domain\Partial\ValueObject\Footer;
use Rendering\Domain\Partial\ValueObject\Header;
use Rendering\Domain\Partial\ValueObject\Navigation\Navigation;

/**
 * Defines the contract for a composite Page object.
 *
 * A Page represents the entire renderable document, aggregating all its
 * structural parts. By extending PartialProviderInterface, it also acts

 * as the primary context for providing injectable partial view components.
 */
interface PageInterface extends PartialProviderInterface
{
    /**
     * Returns the header component of the page.
     */
    public function header(): Header;

    /**
     * Returns the primary view content of the page.
     */
    public function view(): ViewInterface;

    /**
     * Returns the footer component of the page.
     */
    public function footer(): Footer;
}
