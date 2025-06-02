<?php

declare(strict_types=1);

namespace Rendering\Infrastructure;

use Rendering\Application\HtmlView;
use Rendering\Domain\Contracts\ViewInterface;
use App\Shared\UrlResolver\AppUrlResolver;
use InvalidArgumentException;
use RuntimeException;

/**
 * HtmlViewRenderer
 *
 * Renders HtmlView objects using PHP templates.
 * Injects shared presentation context variables into the template scope,
 * including base URL and other layout-level metadata.
 */
final class HtmlViewRenderer implements ViewRendererInterface
{
    /**
     * Base path for template files (absolute).
     *
     * @var string
     */
    private string $templatePath;

    /**
     * Resolver for generating the absolute base URL.
     *
     * @var AppUrlResolver
     */
    private AppUrlResolver $urlResolver;

    /**
     * Constructs a renderer using a base templates directory and a URL resolver.
     *
     * @param string $templatePath Absolute directory path to templates.
     * @param AppUrlResolver $urlResolver Responsible for computing base URL.
     */
    public function __construct(string $templatePath, AppUrlResolver $urlResolver)
    {
        $this->templatePath = rtrim($templatePath, '/');
        $this->urlResolver = $urlResolver;
    }

    /**
     * Renders a view into an HTML string using buffered PHP templates.
     *
     * @param ViewInterface $view A view implementing HtmlView.
     * @return string Rendered output
     *
     * @throws InvalidArgumentException If the view is not an HtmlView.
     * @throws RuntimeException If the template file is not found.
     */
    public function render(ViewInterface $view): string
    {
        if (!$view instanceof HtmlView) {
            throw new InvalidArgumentException('HtmlViewRenderer supports only HtmlView instances.');
        }

        $templateFile = "{$this->templatePath}/{$view->template()}";

        if (!is_file($templateFile)) {
            throw new RuntimeException("Template file not found: {$templateFile}");
        }

        // Inject shared context like baseUrl before rendering
        $data = array_merge(
            ['baseUrl' => $this->urlResolver->baseUrl()],
            $view->data()
        );

        return $this->renderTemplate($templateFile, $data);
    }

    /**
     * Executes the template in an isolated scope with safely extracted data.
     *
     * @param string $templateFile Absolute path to template file.
     * @param array<string, mixed> $data Associative array of template variables.
     * @return string Rendered HTML content.
     */
    private function renderTemplate(string $templateFile, array $data): string
    {
        ob_start();

        (static function (string $__file__, array $__data__): void {
            extract($__data__, EXTR_SKIP);
            include $__file__;
        })($templateFile, $data);

        return ob_get_clean();
    }
}
