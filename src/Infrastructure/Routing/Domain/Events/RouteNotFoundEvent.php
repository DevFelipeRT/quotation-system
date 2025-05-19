<?php

declare(strict_types=1);

namespace App\Infrastructure\Routing\Domain\Events;

use App\Infrastructure\Routing\Domain\Events\Contracts\RoutingEventInterface;
use App\Infrastructure\Routing\Presentation\Http\Contracts\RouteRequestInterface;

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
     * @var RouteRequestInterface
     */
    private RouteRequestInterface $request;

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
     * @param RouteRequestInterface $request The unmatched request.
     * @param string|null $message Optional reason/context.
     * @param \DateTimeImmutable|null $occurredAt Event timestamp (optional, defaults to now).
     */
    public function __construct(
        RouteRequestInterface $request,
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
    public function request(): RouteRequestInterface
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
