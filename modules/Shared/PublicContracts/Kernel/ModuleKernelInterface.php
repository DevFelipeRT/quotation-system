<?php

declare(strict_types=1);

namespace Application\Contract\Kernel;

/**
 * Defines a common contract for all module kernels.
 *
 * This marker interface ensures that all module-specific kernels (e.g.,
 * RenderingKernelInterface, CrmKernelInterface) can be recognized and
 * handled polymorphically by the main application kernel or service container.
 *
 * It serves as a common ancestor for module bootstrapping and integration,
 * providing a consistent type for kernel discovery and management.
 */
interface ModuleKernelInterface
{
    // This interface is intentionally left empty as it primarily serves as a marker.
    // In the future, common methods required by all modules, such as boot()
    // or getServiceProvider(), could be added here.
}
