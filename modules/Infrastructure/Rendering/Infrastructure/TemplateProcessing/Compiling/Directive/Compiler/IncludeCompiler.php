<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\TemplateProcessing\Compiling\Directive\Compiler;

use Rendering\Infrastructure\TemplateProcessing\Compiling\Directive\AbstractDirectiveCompiler;

/**
 * A compiler pass that transforms @include directives into PHP render calls.
 *
 * This compiler extends the AbstractDirectiveCompiler and provides a custom
 * implementation for the buildParameterizedReplacement method to handle the
 * specific logic of the @include directive.
 */
final class IncludeCompiler extends AbstractDirectiveCompiler
{
    /**
     * {@inheritdoc}
     */
    protected const PARAMETERIZED_DIRECTIVES = [
        'include' => '/@include\s*\(/',
    ];

    /**
     * {@inheritdoc}
     *
     * Provides the specific replacement logic for the @include directive,
     * parsing the template path and the optional data array.
     */
    protected function buildParameterizedReplacement(string $name, string $expression): string
    {
        $parts = $this->splitExpressionByComma($expression);
        $template = trim($parts[0]);
        $data = isset($parts[1]) ? trim($parts[1]) : '[]';

        return "<?= \$view->include({$template}, {$data}) ?>";
    }

    /**
     * Splits a directive's expression by the first top-level comma.
     *
     * This method safely splits an expression into two parts (e.g., for @include),
     * correctly ignoring commas that are inside nested parentheses or arrays.
     *
     * @param string $expression The expression string to split.
     * @return string[] An array containing one or two parts of the expression.
     */
    private function splitExpressionByComma(string $expression): array
    {
        $level = 0;
        $length = strlen($expression);
        for ($i = 0; $i < $length; $i++) {
            switch ($expression[$i]) {
                case '(':
                case '[':
                    $level++;
                    break;
                case ')':
                case ']':
                    $level--;
                    break;
                case ',':
                    if ($level === 0) {
                        return [substr($expression, 0, $i), substr($expression, $i + 1)];
                    }
                    break;
            }
        }
        return [$expression];
    }
}
