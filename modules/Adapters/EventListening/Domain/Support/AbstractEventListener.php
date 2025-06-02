<?php

declare(strict_types=1);

namespace App\Adapters\EventListening\Domain\Support;

use App\Adapters\EventListening\Domain\Contracts\EventListenerInterface;

/**
 * Base class for strongly-typed event listeners with type safety and failure isolation.
 *
 * Subclasses must specify the handled event type and provide a typed handler method.
 *
 * @template T of object
 */
abstract class AbstractEventListener implements EventListenerInterface
{
    /**
     * Dispatch entry point. Filters by supported type before handling.
     *
     * @param object $event
     * @return void
     */
    final public function __invoke(object $event): void
    {
        $expected = $this->eventType();

        if (!$event instanceof $expected) {
            return;
        }

        try {
            /** @var T $event */
            $this->handle($event);
        } catch (\Throwable $e) {
            $this->onFailure($event, $e);
        }
    }

    /**
     * Returns the expected event class name.
     *
     * @return class-string<T>
     */
    abstract protected function eventType(): string;

    /**
     * Handles the typed event.
     *
     * @param T $event
     * @return void
     */
    abstract protected function handle(object $event): void;

    /**
     * Optional error handler for when an exception is thrown during handling.
     *
     * @param object $event
     * @param \Throwable $exception
     * @return void
     */
    protected function onFailure(object $event, \Throwable $exception): void
    {
        // Default: no action. Can be overridden for logging, alerting, etc.
    }
}
