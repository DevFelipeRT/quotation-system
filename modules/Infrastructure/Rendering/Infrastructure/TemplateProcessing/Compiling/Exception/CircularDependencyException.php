<?php 

declare(strict_types=1);

namespace Rendering\Infrastructure\TemplateProcessing\Compiling\Exception;

use RuntimeException;

/**
 * Exception thrown when circular dependencies are detected during template compilation.
 * Provides detailed information about the dependency cycle.
 */
class CircularDependencyException extends RuntimeException
{
    /**
     * @var array<string, array<string>> Content history that led to the circular dependency
     */
    private array $contentHistory;
    
    /**
     * @param string $message Error message
     * @param array<string, array<string>> $contentHistory History of content by section
     */
    public function __construct(string $message, array $contentHistory = [])
    {
        $this->contentHistory = $contentHistory;
        parent::__construct($message);
    }
    
    /**
     * @return array<string, array<string>> Content history by section
     */
    public function getContentHistory(): array
    {
        return $this->contentHistory;
    }
}