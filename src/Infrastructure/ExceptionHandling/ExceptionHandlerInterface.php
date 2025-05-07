<?php

declare(strict_types=1);

namespace App\Exceptions;

use Throwable;

/**
 * Defines a contract for classes responsible for handling uncaught exceptions
 * within the application's execution flow.
 *
 * Implementations may include logging, error response rendering, or system-level
 * notifications, depending on the context in which the exception occurred.
 */
interface ExceptionHandlerInterface
{
    /**
     * Handles an uncaught throwable.
     *
     * @param Throwable $exception The exception or error that was thrown.
     * @return void
     */
    public function handle(Throwable $exception): void;
}
