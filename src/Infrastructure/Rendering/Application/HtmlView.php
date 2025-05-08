<?php

namespace App\Infrastructure\Rendering\Application;

/**
 * HtmlView
 *
 * Encapsulates a PHP template-based view, providing the template filename
 * and the data to be injected during rendering. Intended to be consumed
 * by a ViewRenderer such as HtmlViewRenderer.
 */
final class HtmlView implements ViewInterface
{
    /**
     * Template filename to render (e.g. 'dashboard.php').
     *
     * @var string
     */
    private string $template;

    /**
     * Associative array of data to be extracted into the template.
     *
     * @var array<string, mixed>
     */
    private array $data;

    /**
     * @param string $template Template filename to be rendered.
     * @param array<string, mixed> $data View variables to inject into the template.
     */
    public function __construct(string $template, array $data = [])
    {
        $this->template = $template;
        $this->data     = $data;
    }

    /**
     * Returns the filename of the template to be rendered.
     *
     * @return string
     */
    public function template(): string
    {
        return $this->template;
    }

    /**
     * Returns the associative array of view variables.
     *
     * @return array<string, mixed>
     */
    public function data(): array
    {
        return $this->data;
    }
}
