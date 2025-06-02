<?php

declare(strict_types=1);

namespace Routing\Domain\Events;

use Routing\Domain\Events\Contracts\RoutingEventInterface;
use Routing\Presentation\Http\Contracts\ServerRequestInterface;

/**
 * RouteNotFoundEvent
 *
 * Dispatched when an incoming request does not match any registered route.
 * Carries the unmatched request and contextual information for listeners
 * (logging, analytics, custom 404 handlers, security, etc).
 */
final class RouteNotFoundEvent implements RoutingEventInterface
{
    /**
     * @var ServerRequestInterface
     */
    private ServerRequestInterface $request;

    /**
     * @var \DateTimeImmutable
     */
    private \DateTimeImmutable $occurredAt;

    /**
     * Optional message or code indicating the not found context.
     * For extensibility (not required for minimal implementation).
     *
     * @var string|null
     */
    private ?string $message;

    /**
     * RouteNotFoundEvent constructor.
     *
     * @param ServerRequestInterface $request The unmatched request.
     * @param string|null $message Optional reason/context.
     * @param \DateTimeImmutable|null $occurredAt Event timestamp (optional, defaults to now).
     */
    public function __construct(
        ServerRequestInterface $request,
        ?string $message = null,
        ?\DateTimeImmutable $occurredAt = null
    ) {
        $this->request = $request;
        $this->message = $message;
        $this->occurredAt = $occurredAt ?? new \DateTimeImmutable();
    }

    /**
     * Returns the unmatched request.
     */
    public function request(): ServerRequestInterface
    {
        return $this->request;
    }

    /**
     * Returns the optional not found message or code.
     */
    public function message(): ?string
    {
        return $this->message;
    }

    /**
     * Returns when this event occurred.
     */
    public function occurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
