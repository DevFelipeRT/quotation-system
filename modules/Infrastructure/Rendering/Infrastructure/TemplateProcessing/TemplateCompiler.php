<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\TemplateProcessing;

use Rendering\Infrastructure\Contract\TemplateProcessing\TemplateCompilerInterface;
use Rendering\Infrastructure\Contract\TemplateProcessing\TemplatePathResolverInterface;

/**
 * Compiles template source code into executable PHP code.
 *
 * This class is the core engine of the template processing system. It takes the
 * raw string content of a template and transforms custom directives (like @extends,
 * @section, @yield, and @include) into native PHP. The compiler itself is
 * designed to be stateless between public compile() calls.
 */
final class TemplateCompiler implements TemplateCompilerInterface
{
    /**
     * The path to the layout file this template extends, relative to the views path.
     * @var string|null
     */
    private ?string $layout = null;

    /**
     * An array holding the compiled content of defined sections.
     * @var array<string, string>
     */
    private array $sections = [];

    /**
     * The service responsible for resolving template names into absolute paths.
     * @var TemplatePathResolverInterface
     */
    private readonly TemplatePathResolverInterface $pathResolver;

    /**
     * @param TemplatePathResolverInterface $pathResolver The service for resolving template paths.
     */
    public function __construct(TemplatePathResolverInterface $pathResolver)
    {
        $this->pathResolver = $pathResolver;
    }

    /**
     * Compiles the string content of a template file into executable PHP code.
     *
     * @param string $content The raw string content of the template file to be compiled.
     * @return string The compiled PHP code as a string, ready to be cached and executed.
     */
    public function compile(string $content): string
    {
        $this->resetState();

        // Step 1: Parse for inheritance (@extends) and capture all sections (@section).
        $content = $this->parseLayoutAndSections($content);

        // Step 2: If a layout was defined, its content becomes the new master content.
        if ($this->layout !== null) {
            $layoutPath = $this->pathResolver->resolve($this->layout);
            $content = file_get_contents($layoutPath);
        }

        // Step 3: Compile the final content by injecting sections and including partials.
        return $this->compileFinalContent($content);
    }

    /**
     * Resets the internal, transient state of the compiler for a new compilation task.
     */
    private function resetState(): void
    {
        $this->layout = null;
        $this->sections = [];
    }

    /**
     * Coordinates the initial parsing of layout and section directives.
     */
    private function parseLayoutAndSections(string $content): string
    {
        $content = $this->parseExtends($content);
        return $this->parseSections($content);
    }

    /**
     * Finds an @extends directive, registers the layout path for later processing, and removes the directive.
     */
    private function parseExtends(string $content): string
    {
        $pattern = '/@extends\s*\(\s*\'([^\']+)\'\s*\)/';
        if (preg_match($pattern, $content, $matches)) {
            $this->layout = $matches[1];
            return str_replace($matches[0], '', $content);
        }
        return $content;
    }

    /**
     * Finds all @section blocks, captures their content into an internal array, and removes them from the string.
     */
    private function parseSections(string $content): string
    {
        $pattern = '/@section\s*\(\s*\'([^\']+)\'\s*\)(.*?)@endsection/s';
        return preg_replace_callback($pattern, function ($matches) {
            $this->sections[$matches[1]] = $matches[2];
            return ''; // The section definition is removed as its content will be yielded.
        }, $content);
    }

    /**
     * Compiles all remaining directives on the final content string.
     */
    private function compileFinalContent(string $content): string
    {
        $content = $this->compileYields($content);
        $content = $this->compilePartials($content);
        return $this->compileIncludes($content);
    }

    /**
     * Replaces @yield('section-name') directives with the corresponding captured section content.
     */
    private function compileYields(string $content): string
    {
        $pattern = '/@yield\s*\(\s*\'([^\']+)\'\s*\)/';
        return preg_replace_callback($pattern, function ($matches) {
            return isset($this->sections[$matches[1]]) ? $this->sections[$matches[1]] : '';
        }, $content);
    }

    /**
     * Replaces @partial('identifier') directives with the PHP code to call the rendering helper.
     */
    private function compilePartials(string $content): string
    {
        $pattern = '/@partial\s*\(\s*\'([^\']+)\'\s*\)/';
        $replacement = '<?= $view->renderPartial(\'$1\') ?>';
        return preg_replace($pattern, $replacement, $content);
    }

    /**
     * Replaces @include('path.phtml', [data]) directives with the PHP code to call the rendering helper.
     */
    private function compileIncludes(string $content): string
    {
        $pattern = "/@include\s*\(\s*'([^']*)'(?:\s*,\s*(.*))?\s*\)/s";

        return preg_replace_callback($pattern, function ($matches) {
            // Group 1 is always the path inside the quotes.
            $templatePath = $matches[1];
            
            // Group 2 is the optional data array string.
            $data = isset($matches[2]) ? $matches[2] : '[]';

            // Re-add quotes around the path and assemble the final, safe PHP call.
            return "<?= \$view->include('{$templatePath}', {$data}) ?>";
        }, $content);
    }
}