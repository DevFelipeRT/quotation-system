<?php

declare(strict_types=1);

namespace Container\Exceptions;

/**
 * Class ContainerException
 *
 * Thrown for general container errors, such as resolution failures, misconfigurations, or internal errors.
 */
class ContainerException extends \Exception
{
    /**
     * Constructs a new ContainerException instance.
     *
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(string $message, int $code = 500, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
