<?php

declare(strict_types=1);

namespace App\Infrastructure\Routing\Presentation\Http\Routing;

use App\Infrastructure\Routing\Application\Services\RoutePath;
use App\Infrastructure\Routing\Presentation\Http\Routing\Contracts\RouteRequestInterface;

/**
 * Class RouteRequest
 *
 * Default implementation of RouteRequestInterface.
 */
final class RouteRequest implements RouteRequestInterface
{
    private readonly HttpMethod $method;
    private readonly RoutePath $path;
    private readonly string $host;
    private readonly string $scheme;

    /**
     * @param HttpMethod $method
     * @param RoutePath $path
     * @param string $host
     * @param string $scheme
     */
    public function __construct(
        HttpMethod $method,
        RoutePath $path,
        string $host,
        string $scheme
    ) {
        $this->method = $method;
        $this->path = $path;
        $this->host = $host;
        $this->scheme = $scheme;
    }

    public function method(): HttpMethod
    {
        return $this->method;
    }

    public function path(): RoutePath
    {
        return $this->path;
    }

    public function host(): string
    {
        return $this->host;
    }

    public function scheme(): string
    {
        return $this->scheme;
    }

    public function equals(RouteRequestInterface $other): bool
    {
        return $this->method->equals($other->method())
            && $this->path->equals($other->path())
            && $this->host === $other->host()
            && $this->scheme === $other->scheme();
    }
}
