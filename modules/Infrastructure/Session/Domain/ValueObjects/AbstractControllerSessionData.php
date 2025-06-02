<?php

declare(strict_types=1);

namespace Session\Domain\ValueObjects;

use Session\Domain\Contracts\SessionDataInterface;

/**
 * Abstract base class for controller-specific session data.
 *
 * Encapsulates controller state or request-scoped data
 * and enforces contextual metadata and serialization rules.
 * Immutable by design.
 */
abstract class AbstractControllerSessionData extends AbstractSessionData
{
    public function __construct(SessionContext $context)
    {
        parent::__construct($context);
    }

    /**
     * Returns an associative array of controller-specific data.
     *
     * Concrete implementations must provide only primitive or serializable types.
     *
     * @return array<string, mixed>
     */
    abstract protected function controllerPayload(): array;

    /**
     * Returns the controller identifier associated with this session data.
     * This should correspond to a unique controller name or route.
     *
     * @return string
     */
    abstract public function controllerKey(): string;

    /**
     * Returns true if the session data is authenticated.
     *
     * You may override if controller-specific logic is required.
     */
    public function isAuthenticated(): bool
    {
        return $this->context->isAuthenticated();
    }

    /**
     * Returns the locale associated with the session context.
     */
    public function getLocale(): string
    {
        return $this->context->getLocale();
    }

    /**
     * Serializes controller data for session storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_merge(
            [
                'controller'    => $this->controllerKey(),
                'locale'        => $this->getLocale(),
                'authenticated' => $this->isAuthenticated(),
            ],
            $this->controllerPayload()
        );
    }
}
