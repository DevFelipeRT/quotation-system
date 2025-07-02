<?php

declare(strict_types=1);

namespace Rendering\Domain\Contract;

/**
 * Defines the contract for a service that handles directory path security and validation.
 */
interface SecurityServiceInterface
{
    /**
     * Validates a given directory path.
     *
     * This method should check if the path points to an existing directory and
     * is located within an expected, secure base location to prevent directory
     * traversal attacks.
     *
     * @param string $path The absolute path to the directory to validate.
     * @return void
     * @throws InvalidDirectoryException if the path is deemed invalid or unsafe.
     */
    public function validateDirectoryPath(string $path): void;
}