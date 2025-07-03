<?php

declare(strict_types=1);

namespace Routing\Domain\Exception;

/**
 * Marker interface for all exceptions thrown by the Routing module.
 *
 * By implementing this interface, custom exceptions like RouteNotFoundException
 * can be caught with a single `catch (ExceptionInterface $e)` block,
 * enabling robust error handling for clients of this module. It extends the
 * base \Throwable interface to ensure all implementations are catchable.
 */
interface ExceptionInterface extends \Throwable
{
    // This interface is intentionally empty.
}