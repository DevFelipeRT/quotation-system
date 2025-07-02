<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\Building\Partials;

use Rendering\Infrastructure\Contract\Building\Partial\FooterBuilderInterface;
use Rendering\Domain\Contract\PartialViewInterface;
use Rendering\Domain\Partial\ValueObject\Footer;

/**
 * Implements the Builder pattern to assemble a complete Footer object.
 */
final class FooterBuilder implements FooterBuilderInterface
{
    private string $copyrightNotice = '';
    private string $copyrightOwner = '';
    private string $copyrightMessage = 'All rights reserved.';
    private array $jsLinks = [];
    private array $partials = [];

    public function __construct(?string $copyrightOwner, ?string $copyrightMessage) {
        if (!empty($copyrightOwner) || !empty($copyrightMessage)) {
            $this->copyrightOwner = $copyrightOwner;
            $this->copyrightMessage = $copyrightMessage;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setCopyright(string $owner, string $message = 'All rights reserved.'): self
    {
        $this->copyrightOwner = $owner;
        $this->copyrightMessage = $message;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addJs(string $path): self
    {
        if (!in_array($path, $this->jsLinks, true)) {
            $this->jsLinks[] = $path;
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addPartial(string $key, PartialViewInterface $partial): self
    {
        $this->partials[$key] = $partial;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function build(): Footer
    {
        $this->buildCopyrightNotice();
        return new Footer(
            $this->copyrightNotice,
            $this->jsLinks,
            $this->partials
        );
    }

    private function buildCopyrightNotice(): void
    {
        $this->copyrightNotice = '© ' . date('Y') . " {$this->copyrightOwner}. {$this->copyrightMessage}";
    }
}
