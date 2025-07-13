<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\TemplateProcessing\Compiling;

use Rendering\Infrastructure\TemplateProcessing\Compiling\Exception\CircularDependencyException;

/**
 * Recursive template compiler with circular dependency detection.
 * 
 * Provides safe compilation of template content by detecting and preventing
 * infinite loops caused by circular references between template sections.
 */
final class RecursiveTemplateCompiler
{
    /**
     * Compiles template sections with circular dependency detection.
     *
     * @param array<string, string> $contents Template sections to compile
     * @param array<string, string> $contextSections Context sections for yield resolution
     * @param callable $compilerCallback Compiler function to apply to content
     * @return array<string, string> Compiled template sections
     * @throws CircularDependencyException When circular dependencies are detected
     */
    public function compile(
        array $contents,
        array $contextSections,
        callable $compilerCallback
    ): array {
        $contentHistory = array_fill_keys(array_keys($contents), []);
        
        return $this->compileRecursively(
            $contents,
            $contextSections,
            $compilerCallback,
            $contentHistory
        );
    }

    /**
     * Executes recursive compilation until no content changes occur.
     *
     * @param array<string, string> $contents Template sections to compile
     * @param array<string, string> $contextSections Context sections for yield resolution
     * @param callable $compilerCallback Compiler function to apply
     * @param array<string, array<string>> $contentHistory Hash history for circular detection
     * @return array<string, string> Compiled template sections
     * @throws CircularDependencyException When circular dependencies are detected
     */
    private function compileRecursively(
        array $contents,
        array $contextSections,
        callable $compilerCallback,
        array $contentHistory
    ): array {
        $result = $contents;
        $contextSections = empty($contextSections) ? $result : $contextSections;
        
        while ($this->detectContentChanges($result, $contextSections, $compilerCallback, $contentHistory)) {
            $contextSections = empty($contextSections) ? $result : $contextSections;
        }
        
        return $result;
    }

    /**
     * Processes all sections and checks for content changes.
     *
     * @param array<string, string> $result Current compilation result
     * @param array<string, string> $contextSections Context sections for compilation
     * @param callable $compilerCallback Compiler function to apply
     * @param array<string, array<string>> $contentHistory Hash history for tracking
     * @return bool True if any section content changed
     * @throws CircularDependencyException When circular dependencies are detected
     */
    private function detectContentChanges(
        array &$result,
        array $contextSections,
        callable $compilerCallback,
        array &$contentHistory
    ): bool {
        $hasChanges = false;
        
        foreach ($result as $sectionName => $content) {
            if ($this->compileSection($sectionName, $content, $contextSections, $compilerCallback, $contentHistory, $result)) {
                $hasChanges = true;
            }
        }
        
        return $hasChanges;
    }

    /**
     * Compiles a single section and updates the result if content changes.
     *
     * @param string $sectionName Section name being processed
     * @param string $content Current section content
     * @param array<string, string> $contextSections Context sections for compilation
     * @param callable $compilerCallback Compiler function to apply
     * @param array<string, array<string>> $contentHistory Hash history for tracking
     * @param array<string, string> $result Reference to compilation result
     * @return bool True if section content changed
     * @throws CircularDependencyException When circular dependencies are detected
     */
    private function compileSection(
        string $sectionName,
        string $content,
        array $contextSections,
        callable $compilerCallback,
        array &$contentHistory,
        array &$result
    ): bool {
        $this->recordContentHash($sectionName, $content, $contentHistory);
        $this->checkCircularDependency($sectionName, $contentHistory);
        
        $newContent = $compilerCallback($content, $contextSections);
        
        if ($content !== $newContent) {
            $result[$sectionName] = $newContent;
            return true;
        }
        
        return false;
    }

    /**
     * Records content hash in compilation history for tracking.
     *
     * @param string $sectionName Section name
     * @param string $content Content to track
     * @param array<string, array<string>> $contentHistory History reference
     */
    private function recordContentHash(string $sectionName, string $content, array &$contentHistory): void
    {
        $contentHash = md5($content);
        $contentHistory[$sectionName][] = $contentHash;
    }

    /**
     * Checks section for circular dependencies and throws exception if found.
     *
     * @param string $sectionName Section name
     * @param array<string, array<string>> $contentHistory Compilation history
     * @throws CircularDependencyException When circular dependencies are detected
     */
    private function checkCircularDependency(string $sectionName, array $contentHistory): void
    {
        $hashes = $contentHistory[$sectionName] ?? [];
        if ($this->detectOscillation($hashes) || $this->detectRepeatingPattern($hashes)) {
            throw new CircularDependencyException(
                "Circular dependency detected in section '{$sectionName}'",
                $contentHistory
            );
        }
    }

    /**
     * Detects simple oscillation patterns (A-B-A cycles).
     *
     * @param array<string> $hashes Content hash history
     * @return bool True if oscillation detected
     */
    private function detectOscillation(array $hashes): bool
    {
        $count = count($hashes);
        
        return $count >= 3 && $hashes[$count - 1] === $hashes[$count - 3];
    }

    /**
     * Detects repeating patterns in hash history.
     *
     * @param array<string> $hashes Content hash history
     * @return bool True if repeating pattern detected
     */
    private function detectRepeatingPattern(array $hashes): bool
    {
        $count = count($hashes);
        
        if ($count < 4) {
            return false;
        }
        
        for ($patternSize = 2; $patternSize <= min(5, floor($count / 2)); $patternSize++) {
            if ($this->isPatternRepeating($hashes, $patternSize)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Checks if pattern of specific size repeats in hash history.
     *
     * @param array<string> $hashes Content hash history
     * @param int $patternSize Pattern size to check
     * @return bool True if pattern repeats
     */
    private function isPatternRepeating(array $hashes, int $patternSize): bool
    {
        $pattern1 = array_slice($hashes, -$patternSize);
        $pattern2 = array_slice($hashes, -($patternSize * 2), $patternSize);
        
        return $pattern1 === $pattern2;
    }
}
