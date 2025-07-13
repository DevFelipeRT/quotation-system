<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\RenderingEngine\Engine;

use Rendering\Infrastructure\Contract\RenderingEngine\TemplateEngineInterface;
use RuntimeException;
use Throwable;

/**
 * A low-level PHP template execution engine.
 *
 * Its sole responsibility is to execute a given PHP template file in an isolated
 * scope, inject variables, and capture the output buffer. It is completely
 * agnostic of the domain (Pages, Views, etc.).
 */
final class PhpTemplateEngine implements TemplateEngineInterface
{
    /**
     * Executes a PHP template file in an isolated scope and returns the rendered output.
     * 
     * This method handles the complete template execution lifecycle:
     * 1. Validates template file existence and readability
     * 2. Extracts variables into template scope using extract()
     * 3. Includes and executes the template file
     * 4. Captures output using output buffering with proper error handling
     * 
     * {@inheritdoc}
     */
    public function execute(string $templatePath, array $data): string
    {
        $this->assertTemplateExists($templatePath);
        $output = $this->executeTemplateWithErrorHandling($templatePath, $data);
        return $output;
    }

    /**
     * Ensures that the template file exists and is readable before attempting to render.
     */
    private function assertTemplateExists(string $templatePath): void
    {
        if (!is_file($templatePath)) {
            throw new RuntimeException(sprintf('Template file not found at path: %s', $templatePath));
        }
        
        if (!is_readable($templatePath)) {
            throw new RuntimeException(sprintf('Template file is not readable: %s', $templatePath));
        }
    }

    /**
     * Executes the template with proper error handling and context information.
     *
     * @param string $templatePath The absolute path to the template file.
     * @param array<string, mixed> $data The variables to be extracted into the template.
     * @return string The rendered output.
     * @throws RuntimeException Re-throw the original exception with enhanced context.
     */
    private function executeTemplateWithErrorHandling(string $templatePath, array $data): string
    {
        try {
            $renderTemplate = $this->createTemplateExecutionCallable($templatePath, $data);
            return $this->captureOutput($renderTemplate);
        } catch (Throwable $exception) {
            // Provide more detailed error context for debugging
            $templateName = basename($templatePath);
            $errorMessage = sprintf(
                'Error rendering template "%s": %s (in %s:%d)', 
                $templateName, 
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine()
            );
            
            throw new RuntimeException($errorMessage, 0, $exception);
        }
    }

    /**
     * Creates a callable that, when executed, renders the template.
     *
     * This isolates the template inclusion logic into a dedicated, testable unit.
     * Uses EXTR_SKIP to prevent data variables from overwriting existing variables
     * like $templatePath and $data in the local scope.
     *
     * @return callable A function that renders the specified template.
     */
    private function createTemplateExecutionCallable(string $templatePath, array $data): callable
    {
        return function () use ($templatePath, $data): void {
            extract($data, EXTR_SKIP);
            include $templatePath;
        };
    }

    /**
     * Captures the output of a given operation using output buffering.
     *
     * This method is generic and can capture the output of any callable.
     * It ensures proper cleanup of output buffers even when exceptions occur.
     *
     * @param callable $operation The operation to execute and capture.
     * @return string The captured output.
     * @throws Throwable Re-throws any exception that occurs during the operation.
     */
    private function captureOutput(callable $operation): string
    {
        ob_start();
        try {
            $operation();
        } catch (Throwable $exception) {
            // In case of an error, ensure the buffer is cleaned before re-throwing.
            ob_end_clean();
            throw $exception;
        }

        $output = ob_get_clean();
        $this->assertOutputCaptured($output);
        return $output;
    }

    /**
     * Asserts that the output captured from the template is valid.
     *
     * Validates that ob_get_clean() returned a valid string result.
     * Although ob_get_clean() typically returns string|false, in the context of this method
     * it should always return string since we control the buffer lifecycle.
     * This validation catches edge cases where output buffering might fail.
     *
     * @param string|false $output The captured output from ob_get_clean().
     * @throws RuntimeException If the output capture failed.
     */
    private function assertOutputCaptured(string|false $output): void
    {
        if ($output === false) {
            throw new RuntimeException('Failed to capture template output buffer.');
        }
    }
    
}
