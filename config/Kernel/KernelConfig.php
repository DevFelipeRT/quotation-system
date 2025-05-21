<?php

declare(strict_types=1);

namespace Config\Kernel;

/**
 * KernelConfig
 *
 * Centralizes all bootstrap and resilience policies for the application kernel.
 * - Defines which modules are essential/critical.
 * - Allows future extensions: initialization order, fallback, custom error handling.
 *
 * @package Config\Kernel
 */
final class KernelConfig
{
    /**
     * @var string[] List of critical (essential) kernel module names. 
     * If any fails, system bootstrap fails.
     */
    private array $essentialModules = [
        'logger',
        'eventDispatcher',
        'database',
        'session',
    ];

    /**
     * @var string[] List of non-essential (degradable) modules.
     * If any fails, system continues with reduced functionality.
     */
    private array $nonEssentialModules = [
        'analytics',
        'email',
        // Extend as needed.
    ];

    // Optional: Map module to init order/priority
    private array $initializationOrder = [
        'logger',
        'eventDispatcher',
        'database',
        'session',
        'analytics',
        'email',
    ];

    public function getEssentialModules(): array
    {
        return $this->essentialModules;
    }

    public function getNonEssentialModules(): array
    {
        return $this->nonEssentialModules;
    }

    /**
     * Returns the initialization order for all modules.
     * @return string[]
     */
    public function getInitializationOrder(): array
    {
        return $this->initializationOrder;
    }

    /**
     * Allows dynamic extension of configuration, e.g. per-environment.
     */
    public function addNonEssentialModule(string $module): void
    {
        if (!in_array($module, $this->nonEssentialModules, true)) {
            $this->nonEssentialModules[] = $module;
        }
    }

    public function addEssentialModule(string $module): void
    {
        if (!in_array($module, $this->essentialModules, true)) {
            $this->essentialModules[] = $module;
        }
    }
}
