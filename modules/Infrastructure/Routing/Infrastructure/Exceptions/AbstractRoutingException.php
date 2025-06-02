<?php 

declare(strict_types=1);

namespace Routing\Infrastructure\Exceptions;

/**
 * Base exception for all routing-related errors.
 * Supports automatic prepending of a context-specific prefix to all messages.
 */
abstract class AbstractRoutingException extends \RuntimeException
{
    /**
     * @var array<string, mixed>
     */
    protected array $context = [];

    /**
     * Constructs a new RoutingException.
     *
     * @param string|null          $message  Explanation of the error.
     * @param array<string, mixed> $context  Optional diagnostic context (e.g. allowed methods, request info).
     * @param int                  $code     Optional internal error code.
     * @param \Throwable|null      $previous Optional prior exception.
     */
    public function __construct(
        ?string $message = null, 
        array $context = [], 
        int $code = 0, 
        ?\Throwable $previous = null
    ) {
        $fullMessage = $this->prefix() . ($message ?? $this->defaultMessage());
        $this->context = $context;
        parent::__construct($fullMessage, $code, $previous);
    }

    /**
     * Returns the prefix to prepend to the error message.
     * Each subclass should override as needed.
     *
     * @return string
     */
    protected function prefix(): string
    {
        return '[Routing] ';
    }

    /**
     * Returns the default message for the exception type.
     * Each subclass should override as needed.
     *
     * @return string
     */
    protected function defaultMessage(): string
    {
        return 'An error occurred in the routing system.';
    }

    /**
     * Returns structured context describing the error.
     *
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return $this->context;
    }
}
