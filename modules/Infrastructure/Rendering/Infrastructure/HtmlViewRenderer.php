<?php

declare(strict_types=1);

namespace Rendering\Infrastructure;

use Rendering\Application\Contract\ViewRendererInterface;
use Rendering\Domain\Contract\ViewInterface;
use RuntimeException;

/**
 * HtmlViewRenderer
 *
 * Responsible for rendering HTML views and partials from file-based templates.
 * Supports extracting data and including both full views and partial fragments.
 */
final class HtmlViewRenderer implements ViewRendererInterface
{
    /**
     * @var string
     */
    private string $partialsDirectory;

    /**
     * @var string
     */
    private string $viewsDirectory;

    /**
     * Initializes the renderer with directories for views and partials.
     *
     * @param string $partialsDirectory
     * @param string $viewsDirectory
     */
    public function __construct(string $partialsDirectory, string $viewsDirectory)
    {
        $this->partialsDirectory = rtrim($partialsDirectory, '/');
        $this->viewsDirectory = rtrim($viewsDirectory, '/');
    }

    /**
     * Renders a full view using the provided ViewInterface.
     *
     * @param ViewInterface $view
     * @return string
     */
    public function render(ViewInterface $view): string
    {
        $viewFilePath = $this->buildFilePath($view->fileName());
        $data = $view->data()->toArray();

        return $this->renderView($viewFilePath, $data);
    }

    /**
     * Renders a partial template fragment by file name.
     *
     * @param string $filename Relative filename within the partials directory.
     * @param array<string, mixed> $data Optional data to extract for the partial.
     * @return string
     */
    public function renderPartial(string $filename, array $data = []): string
    {
        $partialFilePath = $this->buildPartialFilePath($filename);

        return $this->renderView($partialFilePath, $data);
    }

    /**
     * Resolves the absolute path to a full view template.
     *
     * @param string $fileName
     * @return string
     */
    private function buildFilePath(string $fileName): string
    {
        $filePath = "{$this->viewsDirectory}/{$fileName}";
        if (!is_file($filePath)) {
            throw new RuntimeException("View file not found: {$filePath}");
        }
        return $filePath;
    }

    /**
     * Resolves the absolute path to a partial template.
     *
     * @param string $fileName
     * @return string
     */
    private function buildPartialFilePath(string $fileName): string
    {
        $filePath = "{$this->partialsDirectory}/{$fileName}";
        if (!is_file($filePath)) {
            throw new RuntimeException("Partial file not found: {$filePath}");
        }
        return $filePath;
    }

    /**
     * Performs template rendering with extracted data.
     *
     * @param string $filePath
     * @param array<string, mixed> $data
     * @return string
     */
    private function renderView(string $filePath, array $data): string
    {
        ob_start();

        (static function (string $__file__, array $__data__): void {
            extract($__data__, EXTR_SKIP);
            include $__file__;
        })($filePath, $data);

        return ob_get_clean();
    }
}
