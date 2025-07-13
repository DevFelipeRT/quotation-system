<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\TemplateProcessing\Compiling\Directive;

use Rendering\Infrastructure\Contract\TemplateProcessing\Compiling\CompilerInterface;

/**
 * Provides a base implementation for compilers that process template directives.
 *
 * This abstract class encapsulates the common logic for iterating through
 * parameterized and parameterless directives, allowing child classes to simply
 * define the directives they handle and their specific replacement logic.
 */
abstract class AbstractDirectiveCompiler implements CompilerInterface
{
    /**
     * An associative array of directives that are followed by a parenthesized expression.
     * The key is the directive name (e.g., 'foreach'), and the value is the regex
     * pattern to find its start (e.g., '/@foreach\s*\(/').
     * @var array<string, string>
     */
    protected const PARAMETERIZED_DIRECTIVES = [];

    /**
     * An associative array of parameterless directives and their direct PHP replacements.
     * The key is the regex pattern (e.g., '/@endforeach\b/'), and the value
     * is the replacement string (e.g., '<?php endforeach; ?>').
     * @var array<string, string>
     */
    protected const PARAMETERLESS_DIRECTIVES = [];

    /**
     * {@inheritdoc}
     */
    public function compile(string $content): string
    {
        $content = $this->compileParameterizedDirectives($content);
        $content = $this->compileParameterlessDirectives($content);

        return $content;
    }

    /**
     * Compiles directives that require parsing a balanced parenthesized expression.
     * 
     * @param string $content The content to process.
     * @return string The content with parameterized directives replaced.
     */
    protected function compileParameterizedDirectives(string $content): string
    {
        foreach (static::PARAMETERIZED_DIRECTIVES as $name => $pattern) {
            $content = $this->compileDirectiveWithCallback(
                $pattern,
                $content,
                fn (string $expression) => $this->buildParameterizedReplacement($name, $expression)
            );
        }
        return $content;
    }

    /**
     * Compiles simple directives that have a fixed replacement.
     * 
     * @param string $content The content to process.
     * @return string The content with parameterless directives replaced.
     */
    protected function compileParameterlessDirectives(string $content): string
    {
        $patterns = array_keys(static::PARAMETERLESS_DIRECTIVES);
        $replacements = array_values(static::PARAMETERLESS_DIRECTIVES);
        
        return preg_replace($patterns, $replacements, $content) ?? $content;
    }

    /**
     * A generic method for compiling directives that use balanced parentheses.
     *
     * @param string $pattern The regex pattern to find the directive start.
     * @param string $content The content to process.
     * @param callable $replacementBuilder A callback that receives the expression and returns the replacement string.
     * @return string The transformed content.
     */
    protected function compileDirectiveWithCallback(string $pattern, string $content, callable $replacementBuilder): string
    {
        $offset = 0;
        while (preg_match($pattern, $content, $matches, PREG_OFFSET_CAPTURE, $offset)) {
            $matchStart = (int) $matches[0][1];
            $parenStart = $matchStart + strlen($matches[0][0]) - 1;

            $expression = $this->extractBalancedExpression($content, $parenStart);
            if ($expression === null) {
                $offset = $parenStart + 1;
                continue;
            }

            $replacement = $replacementBuilder($expression);
            $originalLength = strlen($matches[0][0]) + strlen($expression) + 1;

            $content = substr_replace($content, $replacement, $matchStart, $originalLength);
            $offset = $matchStart + strlen($replacement);
        }

        return $content;
    }

    /**
     * Extracts a balanced expression from within parentheses, starting from a given offset.
     *
     * This method correctly handles nested parentheses by tracking the opening and
     * closing pairs, returning the content between the first opening parenthesis
     * and its corresponding closing one.
     *
     * @param string $string The full content string to search within.
     * @param int $startOffset The position of the opening parenthesis '('.
     * @return string|null The extracted expression, or null if no matching closing parenthesis is found.
     */
    protected function extractBalancedExpression(string $string, int $startOffset): ?string
    {
        $level = 1;
        $length = strlen($string);
        for ($i = $startOffset + 1; $i < $length; $i++) {
            if ($string[$i] === ')') {
                $level--;
                if ($level === 0) {
                    return substr($string, $startOffset + 1, $i - $startOffset - 1);
                }
            } elseif ($string[$i] === '(') {
                $level++;
            }
        }
        return null; // Unmatched parenthesis.
    }

    /**
     * Builds the default PHP replacement for a given parameterized directive.
     *
     * Child classes can override this method to provide custom logic for
     * specific directives (e.g., @forelse).
     * 
     * @param string $name The name of the directive (e.g., 'foreach').
     * @param string $expression The expression within the parentheses of the directive.
     */
    protected function buildParameterizedReplacement(string $name, string $expression): string
    {
        return "<?php {$name}({$expression}): ?>";
    }
}
