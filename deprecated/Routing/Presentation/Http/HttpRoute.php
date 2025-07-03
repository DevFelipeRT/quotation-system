<?php

declare(strict_types=1);

namespace Routing\Presentation\Http;

use Routing\Domain\ValueObjects\ControllerAction;
use Routing\Domain\ValueObjects\HttpMethod;
use Routing\Domain\ValueObjects\RoutePath;
use Routing\Presentation\Http\Contracts\HttpRouteInterface;
use InvalidArgumentException;

/**
 * Class HttpRoute
 *
 * Default implementation of HttpRouteInterface.
 */
final class HttpRoute implements HttpRouteInterface
{
    private readonly HttpMethod $method;
    private readonly RoutePath $path;
    private readonly ControllerAction $controllerAction;
    private readonly string $name;

    /**
     * @param HttpMethod $method
     * @param RoutePath $path
     * @param ControllerAction $controllerAction
     * @param string $name
     */
    public function __construct(
        HttpMethod $method,
        RoutePath $path,
        ControllerAction $controllerAction,
        string $name
    ) {
        $this->validateName($name);

        $this->method = $method;
        $this->path = $path;
        $this->controllerAction = $controllerAction;
        $this->name = $name;
    }

    public function method(): HttpMethod
    {
        return $this->method;
    }

    public function path(): RoutePath
    {
        return $this->path;
    }

    public function controllerAction(): ControllerAction
    {
        return $this->controllerAction;
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * Validates the route name for emptiness and format.
     *
     * @param string $name
     * @return void
     * @throws InvalidArgumentException
     */
    private function validateName(string $name): void
    {
        if (trim($name) === '') {
            throw new InvalidArgumentException('Route name must not be empty.');
        }
        if (!preg_match('/^[a-zA-Z0-9_.-]+$/', $name)) {
            throw new InvalidArgumentException(
                "Invalid route name format: '{$name}'. Allowed: letters, numbers, underscore, dot, hyphen."
            );
        }
    }
}
