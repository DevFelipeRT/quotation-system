<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\RenderingEngine\Component\Context\Builder;

use Rendering\Infrastructure\Contract\RenderingEngine\Component\Context\ContextBuilderInterface;
use InvalidArgumentException;
use Rendering\Domain\Contract\RenderableInterface;
use Rendering\Domain\Contract\Page\PageInterface;
use Rendering\Domain\Contract\View\ViewInterface;
use Rendering\Domain\ValueObject\Shared\PartialsCollection;
use Rendering\Infrastructure\Contract\RenderingEngine\Component\Context\RenderContextInterface;
use Rendering\Infrastructure\RenderingEngine\Component\Context\RenderContext;

/**
 * A specialized context builder for objects that implement PageInterface.
 */
final class PageContextBuilder implements ContextBuilderInterface
{
    /**
     * {@inheritdoc}
     */
    public function build(RenderableInterface $renderable): RenderContextInterface
    {
        if (!$renderable instanceof PageInterface) {
            throw new InvalidArgumentException('PageContextBuilder only supports instances of PageInterface.');
        }

        $data = $this->buildPageData($renderable);
        $apiContext = $this->createPageApiContext($renderable);

        return new RenderContext($data, $apiContext);
    }

    /**
     * Aggregates data from the Page and its associated View.
     */
    private function buildPageData(PageInterface $page): array
    {
        $view = $page->view();

        $pageData = $page->data()?->all() ?? [];
        $viewData = $view->data()?->all() ?? [];
        
        $data = array_merge($pageData, $viewData);
        $data['page'] = $page;

        return $data;
    }

    /**
     * Creates a special API context for a page that aggregates partials from all sources.
     * This allows `$view->partial('name')` to work correctly within the page layout,
     * finding partials defined in the Page, Header, Footer, or View.
     */
    private function createPageApiContext(PageInterface $page): PageInterface
    {
        $pagePartials = $page->partials()?->all() ?? [];
        $viewPartials = $page->view()->partials()?->all() ?? [];
        $headerPartials = $page->header()?->partials()?->all() ?? [];
        $footerPartials = $page->footer()?->partials()?->all() ?? [];

        $allPartials = array_merge($pagePartials, $headerPartials, $footerPartials, $viewPartials);
        
        if (empty($allPartials)) {
            return $page;
        }

        $mergedPartialsCollection = new PartialsCollection($allPartials);

        // Return an anonymous class that acts as a proxy, overriding only the partials collection.
        return new class($page, $mergedPartialsCollection) implements PageInterface {
            public function __construct(private PageInterface $page, private PartialsCollection $partials) {}
            public function partials(): ?PartialsCollection { return $this->partials; }
            public function fileName(): string { return $this->page->fileName(); }
            public function data(): ?\Rendering\Domain\Contract\RenderableDataInterface { return $this->page->data(); }
            public function title(): string { return $this->page->title(); }
            public function header(): ?\Rendering\Domain\ValueObject\Partial\Header { return $this->page->header(); }
            public function view(): ViewInterface { return $this->page->view(); }
            public function footer(): ?\Rendering\Domain\ValueObject\Partial\Footer { return $this->page->footer(); }
            public function assets(): \Rendering\Domain\Contract\Page\AssetsInterface { return $this->page->assets(); }
        };
    }
}
